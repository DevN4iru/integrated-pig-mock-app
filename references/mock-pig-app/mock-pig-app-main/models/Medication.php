<?php

class Medication
{
    private static string $file = __DIR__ . '/../storage/medications.json';

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            $seed = require __DIR__ . '/../data/medications.php';
            $seed = array_map(function ($m) {
                $m['cost'] = (isset($m['cost']) && $m['cost'] !== '') ? (float) $m['cost'] : null;
                return $m;
            }, $seed);

            file_put_contents(self::$file, json_encode($seed, JSON_PRETTY_PRINT));
            return $seed;
        }

        $data = json_decode(file_get_contents(self::$file), true) ?? [];

        return array_map(function ($m) {
            $m['cost'] = (isset($m['cost']) && $m['cost'] !== '') ? (float) $m['cost'] : null;
            return $m;
        }, $data);
    }

    public static function byPig(int $pigId): array
    {
        return array_values(array_filter(self::all(), function ($m) use ($pigId) {
            return (int) ($m['pig_id'] ?? 0) === $pigId;
        }));
    }

    public static function create(array $data): void
    {
        $records = self::all();
        $records[] = [
            'pig_id' => (int) ($data['pig_id'] ?? 0),
            'drug_name' => trim($data['drug_name'] ?? ''),
            'dosage' => trim($data['dosage'] ?? ''),
            'start_date' => trim($data['start_date'] ?? ''),
            'end_date' => trim($data['end_date'] ?? ''),
            'notes' => trim($data['notes'] ?? ''),
            'cost' => (isset($data['cost']) && $data['cost'] !== '') ? (float) $data['cost'] : null,
        ];

        file_put_contents(self::$file, json_encode($records, JSON_PRETTY_PRINT));
    }

    public static function status(array $m): string
    {
        $today = date('Y-m-d');

        if (($m['end_date'] ?? '') === '') {
            return 'scheduled';
        }

        if ($m['end_date'] < $today) {
            return 'overdue';
        }

        if ($m['end_date'] === $today) {
            return 'ending';
        }

        return 'ongoing';
    }

    public static function dashboardSummary(): array
    {
        $ending = 0;
        $overdue = 0;

        foreach (self::all() as $record) {
            $status = self::status($record);

            if ($status === 'ending') $ending++;
            if ($status === 'overdue') $overdue++;
        }

        return [
            'medication_ending' => $ending,
            'medication_overdue' => $overdue,
        ];
    }

    public static function totalLiability(): ?float
    {
        $withCosts = array_filter(self::all(), function ($m) {
            return $m['cost'] !== null;
        });

        if (count($withCosts) === 0) {
            return null;
        }

        return array_sum(array_map(fn($m) => (float) $m['cost'], $withCosts));
    }
}
