<?php

require_once __DIR__ . '/Pig.php';

class Mortality
{
    private static string $file = __DIR__ . '/../storage/mortality.json';

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            $seed = require __DIR__ . '/../data/mortality.php';
            file_put_contents(self::$file, json_encode($seed, JSON_PRETTY_PRINT));
            return $seed;
        }

        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    public static function create(array $data): void
    {
        $records = self::all();
        $records[] = [
            'pig_id' => (int) ($data['pig_id'] ?? 0),
            'date' => trim($data['date'] ?? ''),
            'cause' => trim($data['cause'] ?? ''),
            'notes' => trim($data['notes'] ?? ''),
        ];

        file_put_contents(self::$file, json_encode($records, JSON_PRETTY_PRINT));
    }

    public static function findByPigId(int $pigId): ?array
    {
        foreach (self::all() as $record) {
            if ((int) ($record['pig_id'] ?? 0) === $pigId) {
                return $record;
            }
        }

        return null;
    }

    public static function summary(): array
    {
        $pigs = Pig::all();
        $records = self::all();

        $deadPigs = array_values(array_filter($pigs, function ($pig) {
            return ($pig['status'] ?? '') === 'dead';
        }));

        $totalDead = count($deadPigs);

        $deadWithoutRecord = array_values(array_filter($deadPigs, function ($pig) use ($records) {
            foreach ($records as $record) {
                if ((int) ($record['pig_id'] ?? 0) === (int) ($pig['id'] ?? 0)) {
                    return false;
                }
            }
            return true;
        }));

        if (!empty($deadWithoutRecord)) {
            $recentDead = $deadWithoutRecord[0];

            return [
                'total_dead' => $totalDead,
                'recent_case' => 'Pig ID ' . (int) ($recentDead['id'] ?? 0),
                'recent_cause' => 'Marked dead, no mortality record yet',
            ];
        }

        usort($records, function ($a, $b) {
            return strcmp(($b['date'] ?? ''), ($a['date'] ?? ''));
        });

        if (!empty($records)) {
            $recent = $records[0];

            return [
                'total_dead' => $totalDead,
                'recent_case' => 'Pig ID ' . (int) ($recent['pig_id'] ?? 0),
                'recent_cause' => (string) ($recent['cause'] ?? ''),
            ];
        }

        return [
            'total_dead' => $totalDead,
            'recent_case' => 'None',
            'recent_cause' => '',
        ];
    }
}
