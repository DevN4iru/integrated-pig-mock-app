<?php

class Pen
{
    private static string $file = __DIR__ . '/../storage/pens.json';

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            $default = [
                'Fattening Pen 1',
                'Fattening Pen 2',
                'Fattening Pen 3',
                'Fattening Pen 4',
                'Fattening Pen 5',
                'Fattening Pen 6',
                'Sow Pen 1',
                'Sow Pen 2',
            ];

            file_put_contents(self::$file, json_encode($default, JSON_PRETTY_PRINT));
            return $default;
        }

        $data = json_decode(file_get_contents(self::$file), true) ?? [];
        return array_values(array_unique(array_map('strval', $data)));
    }

    public static function create(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }

        $pens = self::all();

        if (!in_array($name, $pens, true)) {
            $pens[] = $name;
            sort($pens);
            file_put_contents(self::$file, json_encode(array_values($pens), JSON_PRETTY_PRINT));
        }
    }

    public static function delete(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }

        $pens = array_values(array_filter(self::all(), function ($pen) use ($name) {
            return $pen !== $name;
        }));

        file_put_contents(self::$file, json_encode($pens, JSON_PRETTY_PRINT));
    }
}
