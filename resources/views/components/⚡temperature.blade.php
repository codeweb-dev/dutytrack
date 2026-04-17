<?php

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public $temperature = 30;

    public function mount()
    {
        $this->temperature = Cache::remember('weather.cavite.temp', 600, function () {
            try {
                $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => 14.2794,
                    'longitude' => 120.8780,
                    'current_weather' => true,
                ]);

                if ($response->successful()) {
                    return round($response['current_weather']['temperature'] ?? 30);
                }

                return 30;
            } catch (\Exception $e) {
                return 30; // fallback if API fails
            }
        });
    }
};
?>

<div class="hidden md:block">
    @php
        $hour = now()->hour;
        $icon = ($hour >= 6 && $hour < 18) ? 'moon' : 'sun';
    @endphp

    <flux:heading size="sm" class="flex items-center gap-1">
        <flux:icon name="{{ $icon }}" class="w-4 h-4" />
        {{ $temperature }}°C
    </flux:heading>

    <flux:text size="xs">
        Cavite • {{ now()->format('l, M d') }}
    </flux:text>
</div>