<?php

use App\Models\StudentRequirement;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('OJT Requirements')] class extends Component
{
    use WithFileUploads;

    public array $requirementFiles = [];

    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'OJT Requirements']];

    public string $title = 'OJT Student Requirements';

    public string $description = 'Fill out each form online and it goes straight to your coordinator for approval.';

    public function mount(): void
    {
        // Set by the fill-out page after it submits a form.
        if ($submitted = session('requirement-submitted')) {
            Flux::toast(variant: 'success', heading: $submitted.' submitted.', text: 'Your coordinator can now review the generated document.');
        }
    }

    #[Computed]
    public function requirements()
    {
        return StudentRequirement::ensureForStudent(Auth::user());
    }

    #[Computed]
    public function requirementSummary(): array
    {
        $requirements = $this->requirements;
        $approved = $requirements->where('is_completed', true)->count();
        $forChecking = $requirements->where('is_completed', false)->whereNotNull('submitted_at')->count();

        return [
            'total' => $requirements->count(),
            'approved' => $approved,
            'for_checking' => $forChecking,
            'missing' => $requirements->count() - $approved - $forChecking,
            'progress' => $requirements->isNotEmpty() ? round(($approved / $requirements->count()) * 100) : 0,
        ];
    }

    #[Computed]
    public function requirementSteps(): array
    {
        $requirements = $this->requirements->groupBy('step');
        $icons = [
            'orientation' => 'academic-cap',
            'medical' => 'heart',
            'forms' => 'clipboard-document-list',
        ];

        return collect(StudentRequirement::stepLabels())
            ->map(function (string $label, string $step) use ($requirements, $icons): array {
                $items = $requirements->get($step, collect());
                $completed = $items->where('is_completed', true)->count();

                return [
                    'key' => $step,
                    'label' => $label,
                    'icon' => $icons[$step] ?? 'clipboard-document-check',
                    'items' => $items,
                    'total' => $items->count(),
                    'completed' => $completed,
                    'is_completed' => $items->isNotEmpty() && $completed === $items->count(),
                ];
            })
            ->values()
            ->all();
    }

    public function updatedRequirementFiles($file, string $key): void
    {
        $this->submitRequirement($key);
    }

    public function submitRequirement(string $key): void
    {
        $this->validate([
            "requirementFiles.$key" => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ], attributes: ["requirementFiles.$key" => 'file']);

        $requirement = StudentRequirement::query()->where('student_id', Auth::id())->where('key', $key)->firstOrFail();

        // Approved requirements are locked.
        abort_if($requirement->is_completed, 403);

        $requirement->submitFile($this->requirementFiles[$key]->store('student-requirements/'.Auth::id(), 'local'));

        unset($this->requirementFiles[$key], $this->requirements, $this->requirementSummary, $this->requirementSteps);

        Flux::toast(variant: 'success', heading: 'Requirement submitted.', text: 'Your coordinator can now review the attached file.');
    }
};
?>

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach ([['Approved', 'approved', 'check-circle'], ['For Checking', 'for_checking', 'document-magnifying-glass'], ['Missing', 'missing', 'exclamation-circle']] as [$label, $key, $icon])
            <flux:card size="xs">
                <flux:heading class="flex items-center gap-2">{{ $label }}
                    <flux:icon :name="$icon" class="ml-auto size-5 text-zinc-400" />
                </flux:heading>
                <flux:heading size="2xl" class="mt-2">{{ $this->requirementSummary[$key] }}
                    <span class="text-base font-normal text-zinc-400">/ {{ $this->requirementSummary['total'] }}</span>
                </flux:heading>
            </flux:card>
        @endforeach

        <flux:card size="xs">
            <flux:heading class="flex items-center gap-2">Progress
                <flux:icon name="chart-pie" class="ml-auto size-5 text-zinc-400" />
            </flux:heading>
            <flux:heading size="2xl" class="mt-2">{{ $this->requirementSummary['progress'] }}%</flux:heading>
        </flux:card>
    </section>

    <section class="grid items-start gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)]">
        <flux:card>
            <flux:heading size="lg">Requirement Checklist</flux:heading>
            <flux:text class="mt-2">Fill out each form, or upload a file. You can resubmit until your coordinator approves it.</flux:text>

            <div class="mt-6 space-y-6">
                @foreach ($this->requirementSteps as $step)
                    <div wire:key="step-{{ $step['key'] }}">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $step['label'] }}</div>
                            <flux:badge size="sm" :color="$step['is_completed'] ? 'green' : null" inset="top bottom">
                                {{ $step['completed'] }}/{{ $step['total'] }}
                            </flux:badge>
                        </div>

                        <div class="divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                            @foreach ($step['items'] as $requirement)
                                @php
                                    $field = 'requirementFiles.'.$requirement->key;
                                    $fillUrl = match (true) {
                                        $requirement->key === \App\Support\NarrativeReport::KEY => route('student.requirements.narrative'),
                                        \App\Support\RequirementForm::has($requirement->key) => route('student.requirements.form', $requirement->key),
                                        default => null,
                                    };
                                @endphp

                                <div class="p-4" wire:key="requirement-{{ $requirement->id }}">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <span @class([
                                                'flex size-9 shrink-0 items-center justify-center rounded-lg',
                                                'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400' => $requirement->is_completed,
                                                'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400' => ! $requirement->is_completed && $requirement->submitted_at,
                                                'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' => ! $requirement->is_completed && ! $requirement->submitted_at,
                                            ])>
                                                <flux:icon :name="$requirement->is_completed ? 'check-circle' : 'document-text'" class="size-5" />
                                            </span>

                                            <span class="min-w-0">
                                                <span class="block text-sm font-medium text-zinc-900 dark:text-white">{{ $requirement->label }}</span>
                                                <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">
                                                    @if ($requirement->is_completed)
                                                        Approved {{ $requirement->completed_at?->format('M d, Y') }}
                                                    @elseif ($requirement->submitted_at)
                                                        Submitted {{ $requirement->submitted_at->format('M d, Y') }} · waiting for coordinator
                                                    @else
                                                        No file submitted yet
                                                    @endif
                                                </span>
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 pl-12 sm:justify-end sm:pl-0" x-data>
                                            @if ($requirement->is_completed)
                                                <flux:badge color="green" size="sm" inset="top bottom">Approved</flux:badge>
                                            @elseif ($requirement->submitted_at)
                                                <flux:badge color="sky" size="sm" inset="top bottom">For checking</flux:badge>
                                            @else
                                                <flux:badge color="amber" size="sm" inset="top bottom">Missing</flux:badge>
                                            @endif

                                            @if ($requirement->template_path && ! $fillUrl)
                                                <flux:button href="{{ asset($requirement->template_path) }}" target="_blank" download
                                                    icon="document-arrow-down" size="xs" variant="ghost">
                                                    Template
                                                </flux:button>
                                            @endif

                                            @if ($requirement->file_path)
                                                <flux:button href="{{ route('student.requirements.file', $requirement) }}" target="_blank"
                                                    icon="paper-clip" size="xs" variant="ghost">
                                                    View file
                                                </flux:button>
                                            @endif

                                            @unless ($requirement->is_completed)
                                                <input type="file" class="sr-only" x-ref="file" wire:model="{{ $field }}"
                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" tabindex="-1"
                                                    aria-label="Upload {{ $requirement->label }}" />

                                                @if ($fillUrl)
                                                    {{-- Forms signed on paper can still be uploaded as a scan. --}}
                                                    <flux:button type="button" x-on:click="$refs.file.click()" size="xs" variant="ghost"
                                                        icon="arrow-up-tray" tooltip="Upload a signed copy instead"
                                                        aria-label="Upload a signed copy of {{ $requirement->label }}"
                                                        wire:loading.attr="disabled" wire:target="{{ $field }}" />

                                                    <flux:button :href="$fillUrl" wire:navigate size="xs"
                                                        icon="pencil-square" :variant="$requirement->submitted_at ? 'filled' : 'primary'">
                                                        {{ $requirement->submitted_at ? 'Edit' : ($requirement->form_data ? 'Continue' : 'Fill out') }}
                                                    </flux:button>
                                                @else
                                                    <flux:button type="button" x-on:click="$refs.file.click()" size="xs"
                                                        icon="arrow-up-tray" :variant="$requirement->submitted_at ? 'filled' : 'primary'"
                                                        wire:loading.attr="disabled" wire:target="{{ $field }}">
                                                        <span wire:loading.remove wire:target="{{ $field }}">{{ $requirement->submitted_at ? 'Replace' : 'Upload' }}</span>
                                                        <span wire:loading wire:target="{{ $field }}">Uploading…</span>
                                                    </flux:button>
                                                @endif
                                            @endunless
                                        </div>
                                    </div>

                                    <flux:error :name="$field" class="mt-2 sm:pl-12" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <div class="flex flex-col gap-6">
            <flux:card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">Your Progress</flux:heading>
                        <flux:text class="mt-2">Approved requirements per step.</flux:text>
                    </div>

                    <flux:badge :color="$this->requirementSummary['missing'] + $this->requirementSummary['for_checking'] === 0 ? 'green' : 'red'" inset="top bottom">
                        {{ $this->requirementSummary['approved'] }}/{{ $this->requirementSummary['total'] }}
                    </flux:badge>
                </div>

                <div class="mt-4 flex items-center gap-4">
                    <flux:progress :value="$this->requirementSummary['progress']" />
                    <span class="text-sm tabular-nums whitespace-nowrap">{{ $this->requirementSummary['progress'] }}%</span>
                </div>

                <ol class="mt-6">
                    @foreach ($this->requirementSteps as $step)
                        <li class="relative flex gap-3 pb-6 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute top-10 bottom-1 left-5 w-px bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                            @endunless

                            <span @class([
                                'flex size-10 shrink-0 items-center justify-center rounded-full ring-2',
                                'bg-green-600 text-white ring-green-600' => $step['is_completed'],
                                'bg-white text-zinc-500 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700' => ! $step['is_completed'],
                            ])>
                                <flux:icon :name="$step['is_completed'] ? 'check' : $step['icon']" class="size-5" />
                            </span>

                            <div class="min-w-0 pt-1">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $step['label'] }}</div>
                                <flux:text class="mt-0.5">
                                    {{ $step['is_completed'] ? 'Complete' : $step['completed'].' of '.$step['total'].' approved' }}
                                </flux:text>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </flux:card>

            <flux:callout icon="information-circle" variant="secondary">
                <flux:callout.heading>How it works</flux:callout.heading>
                <flux:callout.text>
                    Click <strong>Fill out</strong> and check the details we prefilled from your profile. Submitting creates the
                    document and sends it to your coordinator. Have a signed paper copy? Upload it instead (PDF, Word, JPG, or PNG, max 10 MB).
                    Approved requirements are locked.
                </flux:callout.text>
            </flux:callout>
        </div>
    </section>
</div>
