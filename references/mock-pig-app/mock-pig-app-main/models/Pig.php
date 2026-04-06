<?php

class Pig
{
    private static string $file = __DIR__ . '/../storage/pigs.json';

    private static function normalize(array $pig): array
    {
        return [
            'id' => (int) ($pig['id'] ?? 0),
            'ear_tag' => (string) ($pig['ear_tag'] ?? ''),
            'breed' => (string) ($pig['breed'] ?? ''),
            'sex' => (string) ($pig['sex'] ?? ''),
            'pen_location' => (string) ($pig['pen_location'] ?? ''),
            'status' => (string) ($pig['status'] ?? 'active'),
            'origin_date' => (string) ($pig['origin_date'] ?? ''),
            'latest_weight' => (float) ($pig['latest_weight'] ?? 0),
            'weight_date_added' => (string) ($pig['weight_date_added'] ?? ''),
            'asset_value' => (isset($pig['asset_value']) && $pig['asset_value'] !== '') ? (float) $pig['asset_value'] : null,
            'date_sold' => (string) ($pig['date_sold'] ?? ''),
            'weight_sold_kg' => (isset($pig['weight_sold_kg']) && $pig['weight_sold_kg'] !== '') ? (float) $pig['weight_sold_kg'] : null,
            'price_sold' => (isset($pig['price_sold']) && $pig['price_sold'] !== '') ? (float) $pig['price_sold'] : null,
        ];
    }

    public static function all(): array
    {
        if (!file_exists(self::$file)) {
            $seed = require __DIR__ . '/../data/pigs.php';
            $normalized = array_map([self::class, 'normalize'], $seed);
            file_put_contents(self::$file, json_encode($normalized, JSON_PRETTY_PRINT));
            return $normalized;
        }

        $json = file_get_contents(self::$file);
        $data = json_decode($json, true) ?? [];
        $normalized = array_map([self::class, 'normalize'], $data);

        file_put_contents(self::$file, json_encode($normalized, JSON_PRETTY_PRINT));

        return $normalized;
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $pig) {
            if ($pig['id'] === $id) {
                return $pig;
            }
        }

        return null;
    }

    public static function byPen(string $pen): array
    {
        return array_values(array_filter(self::all(), function ($pig) use ($pen) {
            return $pig['pen_location'] === $pen;
        }));
    }

    public static function create(array $data): void
    {
        $pigs = self::all();
        $nextId = empty($pigs) ? 1 : (max(array_column($pigs, 'id')) + 1);

        $data['id'] = $nextId;
        $pigs[] = self::normalize($data);

        file_put_contents(self::$file, json_encode($pigs, JSON_PRETTY_PRINT));
    }

    public static function update(array $data): void
    {
        $pigs = self::all();

        foreach ($pigs as &$pig) {
            if ($pig['id'] === (int) $data['id']) {
                $pig = self::normalize($data);
                break;
            }
        }

        file_put_contents(self::$file, json_encode($pigs, JSON_PRETTY_PRINT));
    }

    public static function delete(int $id): void
    {
        $pigs = array_filter(self::all(), fn($pig) => $pig['id'] !== $id);

        file_put_contents(self::$file, json_encode(array_values($pigs), JSON_PRETTY_PRINT));
    }

    public static function salesSummary(): array
    {
        $soldPigs = array_values(array_filter(self::all(), function ($pig) {
            return $pig['status'] === 'sold'
                && $pig['price_sold'] !== null
                && $pig['weight_sold_kg'] !== null;
        }));

        $soldCount = count($soldPigs);
        $totalSales = array_sum(array_map(fn($pig) => (float) $pig['price_sold'], $soldPigs));
        $totalSoldWeight = array_sum(array_map(fn($pig) => (float) $pig['weight_sold_kg'], $soldPigs));
        $avgPricePerKg = $totalSoldWeight > 0 ? $totalSales / $totalSoldWeight : 0;

        return [
            'sold_count' => $soldCount,
            'total_sales' => $totalSales,
            'total_sold_weight' => $totalSoldWeight,
            'avg_price_per_kg' => $avgPricePerKg,
        ];
    }

    public static function totalAssets(): int
    {
        $assets = array_filter(self::all(), function ($pig) {
            return ($pig['status'] ?? '') !== 'sold' && ($pig['status'] ?? '') !== 'dead';
        });

        return count($assets);
    }

    public static function totalAssetValue(): ?float
    {
        $assets = array_filter(self::all(), function ($pig) {
            return ($pig['status'] ?? '') !== 'sold' && ($pig['status'] ?? '') !== 'dead';
        });

        $withValues = array_filter($assets, function ($pig) {
            return $pig['asset_value'] !== null;
        });

        if (count($withValues) === 0) {
            return null;
        }

        return array_sum(array_map(fn($pig) => (float) $pig['asset_value'], $withValues));
    }

    public static function totalLossValue(): ?float
    {
        $dead = array_filter(self::all(), function ($pig) {
            return ($pig['status'] ?? '') === 'dead';
        });

        $withValues = array_filter($dead, function ($pig) {
            return $pig['asset_value'] !== null;
        });

        if (count($withValues) === 0) {
            return null;
        }

        return array_sum(array_map(fn($pig) => (float) $pig['asset_value'], $withValues));
    }
}
