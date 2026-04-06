<?php

class HealthLog
{
    private static string $file = __DIR__ . '/../storage/health_logs.json';

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            file_put_contents(self::$file, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }

        return json_decode(file_get_contents(self::$file), true) ?? [];
    }

    public static function byPig(int $pigId): array
    {
        $records = array_values(array_filter(self::all(), function ($row) use ($pigId) {
            return (int) ($row['pig_id'] ?? 0) === $pigId;
        }));

        usort($records, function ($a, $b) {
            return strcmp(($b['date'] ?? ''), ($a['date'] ?? ''));
        });

        return $records;
    }

    public static function create(array $data): void
    {
        $records = self::all();
        $records[] = [
            'pig_id' => (int) ($data['pig_id'] ?? 0),
            'symptoms' => trim($data['symptoms'] ?? ''),
            'temperature' => trim($data['temperature'] ?? ''),
            'notes' => trim($data['notes'] ?? ''),
            'date' => trim($data['date'] ?? ''),
        ];

        file_put_contents(self::$file, json_encode($records, JSON_PRETTY_PRINT));
    }
}
