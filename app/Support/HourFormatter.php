<?php

namespace App\Support;

class HourFormatter
{
    public static function format(float|int|string|null $hours): string
    {
        $totalMinutes = (int) round(max((float) ($hours ?? 0), 0) * 60);

        if ($totalMinutes === 0) {
            return '0 hrs';
        }

        $wholeHours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($wholeHours === 0) {
            return self::plural($minutes, 'min');
        }

        $label = self::plural($wholeHours, 'hr');

        if ($minutes > 0) {
            $label .= ' '.$minutes.' min';
        }

        return $label;
    }

    protected static function plural(int $value, string $unit): string
    {
        return number_format($value).' '.$unit.($unit === 'min' || $value === 1 ? '' : 's');
    }
}
