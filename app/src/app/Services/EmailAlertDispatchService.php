<?php

namespace App\Services;

use App\Mail\PigstepAlertMail;
use App\Models\EmailAlertDelivery;
use App\Models\FarmSetting;
use App\Models\Pig;
use App\Models\ReproductionCycle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Throwable;

class EmailAlertDispatchService
{
    public function dispatchScheduledAlerts(?Carbon $now = null): void
    {
        $now = ($now ?: now())->copy()->second(0);
        $settings = FarmSetting::current();
        $recipient = trim((string) ($settings->alert_recipient_email ?? ''));

        if ($recipient === '') {
            return;
        }

        $this->dispatchFarrowingAlerts($recipient, $now);
        $this->dispatchProtocolAlerts($recipient, $now);
        $this->dispatchOperationalReminders($recipient, $now, $settings);
    }

    protected function dispatchFarrowingAlerts(string $recipient, Carbon $now): void
    {
        foreach ([
            ['days_ahead' => 3, 'window_code' => 't3', 'label' => 'in 3 days'],
            ['days_ahead' => 0, 'window_code' => 'd0', 'label' => 'today'],
        ] as $window) {
            $targetDate = $now->copy()->startOfDay()->addDays($window['days_ahead'])->toDateString();

            $cycles = ReproductionCycle::query()
                ->with('sow')
                ->whereIn('status', [
                    ReproductionCycle::STATUS_PREGNANT,
                    ReproductionCycle::STATUS_DUE_SOON,
                ])
                ->where('pregnancy_result', ReproductionCycle::PREGNANCY_RESULT_PREGNANT)
                ->whereNotNull('expected_farrow_date')
                ->whereNull('actual_farrow_date')
                ->whereDate('expected_farrow_date', $targetDate)
                ->get();

            foreach ($cycles as $cycle) {
                $sowLabel = $this->pigLabel($cycle->sow, $cycle->sow_id);
                $expectedDate = Carbon::parse($cycle->expected_farrow_date)->toDateString();

                $subject = $window['window_code'] === 't3'
                    ? '[Pigstep] Farrowing due in 3 days — ' . $sowLabel
                    : '[Pigstep] Farrowing due today — ' . $sowLabel;

                $headline = $window['window_code'] === 't3'
                    ? 'Farrowing due in 3 days'
                    : 'Farrowing due today';

                $lines = [
                    'Sow ' . $sowLabel . ' has an expected farrowing date of ' . $this->displayDate($expectedDate) . '.',
                    'Review the breeding record and prepare the farrowing area.',
                ];

                $this->sendEmail(
                    fingerprint: 'email:farrowing:' . $window['window_code'] . ':' . $cycle->id . ':' . $expectedDate,
                    alertType: 'farrowing.' . $window['window_code'],
                    recipient: $recipient,
                    subject: $subject,
                    headline: $headline,
                    lines: $lines,
                    actionText: 'Open Breeding Record',
                    actionUrl: $this->safeRoute('reproduction-cycles.show', ['reproductionCycle' => $cycle->id]),
                    payload: [
                        'reproduction_cycle_id' => $cycle->id,
                        'sow_id' => $cycle->sow_id,
                        'expected_farrow_date' => $expectedDate,
                        'window_code' => $window['window_code'],
                    ],
                );
            }
        }
    }

    protected function dispatchProtocolAlerts(string $recipient, Carbon $now): void
    {
        $targetT3Date = $now->copy()->startOfDay()->addDays(3)->toDateString();
        $eligibility = new ProtocolEligibilityService();

        $pigs = Pig::query()
            ->activeLifecycle()
            ->with([
                'birthCycle:id,actual_farrow_date',
                'healthLogs' => function ($query) {
                    $query
                        ->select(['id', 'pig_id', 'purpose', 'weight', 'log_date'])
                        ->where('purpose', 'weight_update')
                        ->whereNotNull('weight')
                        ->orderByDesc('log_date')
                        ->orderByDesc('id');
                },
                'protocolExecutions.medication',
                'protocolExecutions.vaccination',
                'reproductionCyclesAsSow' => function ($query) {
                    $query->select(['id', 'sow_id', 'service_date', 'actual_farrow_date']);
                },
            ])
            ->get();

        foreach ($pigs as $pig) {
            if (!$eligibility->qualifiesForAnyClientProtocol($pig)) {
                continue;
            }

            $summary = $pig->protocol_summary;

            if (!is_array($summary)) {
                continue;
            }

            foreach ($summary['upcoming'] ?? [] as $row) {
                $scheduledDate = $this->normalizeDateString($row['due_start'] ?? null);

                if ($scheduledDate !== $targetT3Date) {
                    continue;
                }

                $this->sendProtocolEmail($recipient, $pig, $row, 't3', $scheduledDate);
            }

            foreach ($summary['due_today'] ?? [] as $row) {
                $scheduledDate = $this->normalizeDateString($row['due_start'] ?? null);

                if ($scheduledDate === null) {
                    continue;
                }

                $this->sendProtocolEmail($recipient, $pig, $row, 'd0', $scheduledDate);
            }

            foreach ($summary['overdue'] ?? [] as $row) {
                $scheduledDate = $this->normalizeDateString($row['due_start'] ?? null);

                if ($scheduledDate === null) {
                    continue;
                }

                $this->sendProtocolEmail($recipient, $pig, $row, 'overdue', $scheduledDate);
            }
        }
    }

    protected function sendProtocolEmail(string $recipient, Pig $pig, array $row, string $windowCode, string $scheduledDate): void
    {
        $pigLabel = $this->pigLabel($pig);
        $ruleId = (int) ($row['rule_id'] ?? 0);
        $action = (string) ($row['action'] ?? 'Protocol action');
        $requirement = (string) ($row['requirement'] ?? '');
        $dueEndDate = $this->normalizeDateString($row['due_end'] ?? null);

        $subject = match ($windowCode) {
            't3' => '[Pigstep] Protocol due in 3 days — ' . $pigLabel,
            'overdue' => '[Pigstep] Protocol overdue — ' . $pigLabel,
            default => '[Pigstep] Protocol due today — ' . $pigLabel,
        };

        $headline = match ($windowCode) {
            't3' => 'Protocol due in 3 days',
            'overdue' => 'Protocol overdue',
            default => 'Protocol due today',
        };

        $lines = $windowCode === 'overdue'
            ? [
                'Pig ' . $pigLabel . ' has unresolved protocol action "' . $action . '" that was scheduled for ' . $this->displayDate($scheduledDate) . '.',
                'Open the pig record and resolve the protocol occurrence as completed, skipped, or deferred.',
            ]
            : [
                'Pig ' . $pigLabel . ' has protocol action "' . $action . '" scheduled for ' . $this->displayDate($scheduledDate) . '.',
            ];

        if ($requirement !== '') {
            $lines[] = 'Requirement level: ' . ucfirst($requirement) . '.';
        }

        if ($dueEndDate !== null && $dueEndDate !== $scheduledDate) {
            $lines[] = 'Due window ends on ' . $this->displayDate($dueEndDate) . '.';
        }

        $this->sendEmail(
            fingerprint: 'email:protocol:' . $windowCode . ':' . $pig->id . ':' . $ruleId . ':' . $scheduledDate,
            alertType: 'protocol.' . $windowCode,
            recipient: $recipient,
            subject: $subject,
            headline: $headline,
            lines: $lines,
            actionText: 'Open Pig Record',
            actionUrl: $this->safeRoute('pigs.show', ['pig' => $pig->id]),
            payload: [
                'pig_id' => $pig->id,
                'protocol_rule_id' => $ruleId,
                'scheduled_for_date' => $scheduledDate,
                'due_end_date' => $dueEndDate,
                'action' => $action,
                'window_code' => $windowCode,
            ],
        );
    }

    protected function dispatchOperationalReminders(string $recipient, Carbon $now, FarmSetting $settings): void
    {
        $dateKey = $now->toDateString();

        if ($this->isDailyReminderDue($now, '05:00')) {
            $this->sendEmail(
                fingerprint: 'email:ops:server_ready:' . $dateKey,
                alertType: 'ops.server_ready',
                recipient: $recipient,
                subject: '[Pigstep] Server ready for use',
                headline: 'Pigstep is ready',
                lines: [
                    'Server is up and Pigstep is ready to use.',
                    'This is your daily startup reminder.',
                ],
                actionText: 'Open Dashboard',
                actionUrl: $this->safeRoute('dashboard'),
                payload: [
                    'date' => $dateKey,
                    'scheduled_time' => '05:00',
                    'catch_up_enabled' => true,
                ],
            );
        }

        $serverCloseTime = $this->normalizeTimeString($settings->server_close_reminder_time);

        if ($this->isDailyReminderDue($now, $serverCloseTime)) {
            $this->sendEmail(
                fingerprint: 'email:ops:server_close:' . $dateKey,
                alertType: 'ops.server_close',
                recipient: $recipient,
                subject: '[Pigstep] Server closing reminder',
                headline: 'Server about to close',
                lines: [
                    'Pigstep server is about to close for the day.',
                    'Service will resume at 5:00 AM.',
                ],
                actionText: 'Open Dashboard',
                actionUrl: $this->safeRoute('dashboard'),
                payload: [
                    'date' => $dateKey,
                    'close_time' => $serverCloseTime,
                    'resume_time' => '05:00',
                    'catch_up_enabled' => true,
                ],
            );
        }

        $feedReminderTime = $this->normalizeTimeString($settings->feed_reminder_time);

        if ($this->isDailyReminderDue($now, $feedReminderTime)) {
            $this->sendEmail(
                fingerprint: 'email:ops:feed:' . $dateKey,
                alertType: 'ops.feed',
                recipient: $recipient,
                subject: '[Pigstep] Daily pig feeding reminder',
                headline: 'Feed the pigs',
                lines: [
                    'This is your daily reminder to feed the pigs.',
                    'Please complete feeding and record any important follow-up inside Pigstep if needed.',
                ],
                actionText: 'Open Dashboard',
                actionUrl: $this->safeRoute('dashboard'),
                payload: [
                    'date' => $dateKey,
                    'feed_time' => $feedReminderTime,
                    'catch_up_enabled' => true,
                ],
            );
        }
    }

    protected function isDailyReminderDue(Carbon $now, ?string $scheduledTime): bool
    {
        if ($scheduledTime === null) {
            return false;
        }

        $scheduledMoment = $now->copy()->startOfDay();
        [$hour, $minute] = array_map('intval', explode(':', $scheduledTime));

        $scheduledMoment->setTime($hour, $minute, 0);

        return $now->greaterThanOrEqualTo($scheduledMoment);
    }

    protected function sendEmail(
        string $fingerprint,
        string $alertType,
        string $recipient,
        string $subject,
        string $headline,
        array $lines,
        ?string $actionText = null,
        ?string $actionUrl = null,
        array $payload = [],
    ): void {
        $delivery = EmailAlertDelivery::query()->firstOrNew([
            'fingerprint' => $fingerprint,
        ]);

        if ($delivery->exists && $delivery->status === EmailAlertDelivery::STATUS_SENT) {
            return;
        }

        $delivery->fill([
            'alert_type' => $alertType,
            'recipient' => $recipient,
            'subject' => $subject,
            'payload_json' => $payload,
            'status' => $delivery->status ?: EmailAlertDelivery::STATUS_PENDING,
        ]);

        try {
            Mail::to($recipient)->send(new PigstepAlertMail(
                subjectLine: $subject,
                headline: $headline,
                lines: $lines,
                actionText: $actionText,
                actionUrl: $actionUrl,
            ));

            $delivery->markAsSent();
        } catch (Throwable $throwable) {
            $delivery->markAsFailed($throwable->getMessage());
        }
    }

    protected function pigLabel(?Pig $pig, ?int $fallbackPigId = null): string
    {
        if ($pig !== null && filled($pig->ear_tag)) {
            return (string) $pig->ear_tag;
        }

        if ($pig !== null && $pig->id !== null) {
            return 'Pig #' . $pig->id;
        }

        return 'Pig #' . ($fallbackPigId ?? '?');
    }

    protected function normalizeDateString(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (blank($value)) {
            return null;
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    protected function normalizeTimeString(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i');
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (Throwable) {
            return null;
        }
    }

    protected function displayDate(string $date): string
    {
        return Carbon::parse($date)->format('M d, Y');
    }

    protected function safeRoute(string $routeName, array $parameters = []): ?string
    {
        if (!Route::has($routeName)) {
            return null;
        }

        try {
            return route($routeName, $parameters);
        } catch (Throwable) {
            return null;
        }
    }
}
