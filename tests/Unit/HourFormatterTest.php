<?php

use App\Support\HourFormatter;

test('hour formatter displays readable hour values', function () {
    expect(HourFormatter::format(0))->toBe('0 hrs')
        ->and(HourFormatter::format(0.33))->toBe('20 min')
        ->and(HourFormatter::format(1))->toBe('1 hr')
        ->and(HourFormatter::format(1.5))->toBe('1 hr 30 min')
        ->and(HourFormatter::format(486))->toBe('486 hrs');
});
