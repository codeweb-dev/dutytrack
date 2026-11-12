<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Holidays
{
    // shortcut: national public holidays only; add a local source if school or employer closures should count.
    public static function forYear(int $year): array
    {
        $key = "public-holidays-ph-v5-{$year}";

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        // Failures are retried after a minute; caching them for a day would block every time-in that long.
        $result = self::fetch($year);
        Cache::put($key, $result, $result['available'] ? now()->addDay() : now()->addMinute());

        return $result;
    }

    protected static function fetch(int $year): array
    {
        try {
            $response = Http::acceptJson()->timeout(3)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH");

            if (! $response->successful()) {
                return ['available' => false, 'holidays' => []];
            }

            $holidays = collect($response->json())
                ->filter(fn ($holiday) => is_array($holiday) && isset($holiday['date']) && ($holiday['localName'] ?? $holiday['name'] ?? null))
                ->map(fn ($holiday) => [
                    'date' => $holiday['date'],
                    'name' => implode(' / ', array_unique(array_filter([$holiday['localName'] ?? null, $holiday['name'] ?? null]))),
                ])
                ->keyBy('date');

            $holidays->put("{$year}-11-02", ['date' => "{$year}-11-02", 'name' => "All Souls' Day"]);

            return ['available' => true, 'holidays' => $holidays->sortKeys()->values()->all()];
        } catch (\Throwable) {
            return ['available' => false, 'holidays' => []];
        }
    }
}
