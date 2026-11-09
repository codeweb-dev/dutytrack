<?php

use App\Support\CertificateDocument;
use App\Support\HourFormatter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('OJT Certificate')] class extends Component
{
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'OJT Certificate']];

    public string $title = 'OJT Certificate';

    public string $description = 'View and download your Certificate of Completion once your required OJT hours are rendered.';

    #[Computed]
    public function certificate(): array
    {
        return CertificateDocument::data(Auth::user());
    }
};
?>

@php
    $certificate = $this->certificate;
    $percent = $certificate['required'] > 0 ? min(100, round($certificate['rendered'] / $certificate['required'] * 100)) : 0;
@endphp

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    @if ($certificate['eligible'])
        <flux:card class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <div class="flex size-16 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                <flux:icon.academic-cap class="size-8" />
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <flux:heading size="lg">Certificate of Completion</flux:heading>
                    <flux:badge color="green" size="sm" icon="check-badge">Completed</flux:badge>
                </div>
                <flux:text class="mt-1">
                    Congratulations, {{ $certificate['name'] }}! You rendered {{ HourFormatter::format($certificate['rendered']) }}
                    @if ($certificate['company']) at {{ $certificate['company'] }}@endif
                    @if ($certificate['from']) from {{ $certificate['from'] }} to {{ $certificate['to'] }}@endif.
                </flux:text>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                <flux:button icon="eye" :href="route('student.certificate.view')" target="_blank">View</flux:button>
                <flux:button variant="primary" icon="arrow-down-tray" :href="route('student.certificate.download')">Download PDF</flux:button>
            </div>
        </flux:card>
    @else
        <flux:card class="flex flex-col items-center px-6 py-12 text-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.lock-closed class="size-8" />
            </div>
            <flux:heading size="lg" class="mt-6">Your certificate is locked</flux:heading>

            @if ($certificate['required'] > 0)
                <flux:text class="mt-2 max-w-md">
                    It unlocks once you render all {{ HourFormatter::format($certificate['required']) }} of your OJT.
                </flux:text>

                <div class="mt-6 w-full max-w-md">
                    <div class="flex justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ HourFormatter::format($certificate['rendered']) }} rendered</span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700" role="progressbar"
                        aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-amber-500" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @else
                <flux:text class="mt-2 max-w-md">
                    Your required OJT hours have not been set yet. Please contact your OJT coordinator.
                </flux:text>
            @endif
        </flux:card>
    @endif
</div>
