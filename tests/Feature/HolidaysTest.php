<?php

use App\Support\Holidays;
use Illuminate\Support\Facades\Http;

test('a failed holiday lookup is retried instead of blocking time-in for a day', function () {
    Http::fake(['date.nager.at/*' => Http::sequence()->pushStatus(500)->push([])]);

    expect(Holidays::forYear(2031)['available'])->toBeFalse();

    $this->travel(2)->minutes();

    expect(Holidays::forYear(2031)['available'])->toBeTrue();
});
