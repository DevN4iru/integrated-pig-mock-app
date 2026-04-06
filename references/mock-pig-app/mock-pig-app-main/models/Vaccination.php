<?php

class Vaccination
{
    private static string $file = __DIR__ . '/../storage/vaccinations.json';

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            $seed = require __DIR__ . '/../data/vaccinations.php';
            file_put_contents(self::$file, json_encode($seed, JSON_PRETTY_PRINT));
            return $seed;
        }

        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    public static function byPig(int $pigId): array
    {
        return array_values(array_filter(self::all(), function ($v) use ($pigId) {
            return (int) $v['pig_id'] === $pigId;
        }));
    }

    public static function create(array $data): void
    {
        $records = self::all();
        $records[] = [
            'pig_id' => (int) $data['pig_id'],
            'vaccine_name' => trim($data['vaccine_name'] ?? ''),
            'date_given' => trim($data['date_given'] ?? ''),
            'next_due' => trim($data['next_due'] ?? ''),
            'notes' => trim($data['notes'] ?? ''),
        ];

        file_put_contents(self::$file, json_encode($records, JSON_PRETTY_PRINT));
    }

    public static function status(array $v): string
    {
        $today = date('Y-m-d');
        $threeDays = date('Y-m-d', strtotime('+3 days'));

        if (($v['next_due'] ?? '') === '') {
            return 'scheduled';
        }

        if ($v['next_due'] < $today) {
            return 'missed';
        }

        if ($v['next_due'] >= $today && $v['next_due'] <= $threeDays) {
            return 'upcoming';
        }

        return 'scheduled';
    }

    public static function commonVaccines(): array
    {
        return [
            'Erysipelas',
            'Leptospirosis',
            'Parvovirus',
            'Clostridial',
            'Circovirus',
            'Swine Influenza',
            'Ileitis',
            'ASF Vaccine',
        ];
    }

    public static function dashboardSummary(): array
    {
        $records = self::all();

        $upcoming = 0;
        $missed = 0;

        foreach ($records as $record) {
            $status = self::status($record);

            if ($status === 'upcoming') {
                $upcoming++;
            }

            if ($status === 'missed') {
                $missed++;
            }
        }

        return [
            'vaccine_upcoming' => $upcoming,
            'vaccine_missed' => $missed,
        ];
    }
}
