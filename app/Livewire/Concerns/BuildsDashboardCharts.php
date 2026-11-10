<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Chart data for the role dashboards, rendered by <x-dashboard.attendance-chart> and <x-dashboard.placement-mix>.
 */
trait BuildsDashboardCharts
{
    protected function weeklyAttendanceChart(Builder $attendance): array
    {
        $days = collect(range(6, 0, -1))
            ->map(fn (int $offset) => now()->subDays($offset)->startOfDay())
            ->values();

        $counts = $attendance
            ->selectRaw('DATE(attendance_date) as day_key, COUNT(*) as total')
            ->whereBetween('attendance_date', [$days->first(), $days->last()->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->pluck('total', 'day_key');

        $values = $days->map(fn ($day) => (int) ($counts[$day->toDateString()] ?? 0));
        $step = max((int) ceil($values->max() / 4), 1);
        $top = $step * 4;

        // Coordinates are percentages of the plot area (y grows downward).
        $points = $days->map(fn ($day, int $i) => [
            'label' => $day->format('D'),
            'date' => $day->format('F j'),
            'value' => $values[$i],
            'x' => round($i * 100 / ($days->count() - 1), 2),
            'y' => round(100 - $values[$i] / $top * 100, 2),
        ]);

        // Horizontal-midpoint control points give a smooth curve that never overshoots the data.
        $line = 'M'.$points[0]['x'].' '.$points[0]['y'];
        for ($i = 1; $i < $points->count(); $i++) {
            [$a, $b] = [$points[$i - 1], $points[$i]];
            $mid = ($a['x'] + $b['x']) / 2;
            $line .= " C{$mid} {$a['y']} {$mid} {$b['y']} {$b['x']} {$b['y']}";
        }

        return [
            'points' => $points->all(),
            'line' => $line,
            'area' => $line.' L100 100 L0 100 Z',
            'ticks' => range(0, $top, $step),
            'total' => $values->sum(),
        ];
    }

    protected function placementMixChart(Builder $assignments): array
    {
        $counts = $assignments
            ->selectRaw('ojt_type, COUNT(*) as total')
            ->groupBy('ojt_type')
            ->pluck('total', 'ojt_type');

        $total = (int) $counts->sum();
        $types = ['internal' => ['stroke-red-500', 'bg-red-500'], 'external' => ['stroke-blue-500', 'bg-blue-500']];
        $gap = collect($types)->keys()->filter(fn ($type) => ($counts[$type] ?? 0) > 0)->count() > 1 ? 0.8 : 0;
        $offset = 0;
        $segments = [];

        // The donut circle has a circumference of 100, so each slice length is its percentage.
        foreach ($types as $type => [$stroke, $dot]) {
            $value = (int) ($counts[$type] ?? 0);
            $percentage = $total > 0 ? $value / $total * 100 : 0;

            $segments[] = [
                'label' => ucfirst($type),
                'value' => $value,
                'percentage' => round($percentage),
                'stroke' => $stroke,
                'dot' => $dot,
                'dash' => max($percentage - $gap, 0).' '.(100 - max($percentage - $gap, 0)),
                'offset' => -$offset,
            ];

            $offset += $percentage;
        }

        return ['total' => $total, 'segments' => $segments];
    }
}
