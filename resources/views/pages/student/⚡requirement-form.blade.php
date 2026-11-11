<?php

use App\Models\StudentRequirement;
use App\Support\RequirementForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Fill Out Requirement')] class extends Component
{
    #[Locked]
    public string $key;

    public array $form = [];

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->form = RequirementForm::values($key, Auth::user(), $this->requirement->form_data ?? []);
    }

    #[Computed]
    public function requirement(): StudentRequirement
    {
        $requirement = StudentRequirement::query()->where('student_id', Auth::id())->where('key', $this->key)->first();

        // Approved requirements are locked.
        abort_if(! $requirement || $requirement->is_completed, 403);

        return $requirement;
    }

    /** Live preview of the unsaved answers, as base64 PDF bytes. */
    public function previewPdf(): string
    {
        return base64_encode(RequirementForm::render($this->requirement, $this->form));
    }

    public function submit(): void
    {
        $data = $this->validate(RequirementForm::rules($this->key), attributes: RequirementForm::attributes($this->key))['form'];
        $path = 'student-requirements/'.Auth::id().'/'.$this->key.'-'.now()->format('YmdHis').'.pdf';

        Storage::disk('local')->put($path, RequirementForm::render($this->requirement, $data));
        $this->requirement->submitFile($path, $data);

        session()->flash('requirement-submitted', $this->requirement->label);

        $this->redirectRoute('student.requirements', navigate: true);
    }
};
?>

<div x-data="{ tab: 'form' }" class="flex flex-col gap-4">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="/dashboard">
            <img src="{{ asset('logo.png') }}" alt="logo" class="h-4 w-4 object-cover" />
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('student.requirements')" wire:navigate>OJT Requirements</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $this->requirement->label }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Toolbar --}}
    <header class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-sm sm:gap-3 sm:p-3 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:button icon="arrow-left" variant="ghost" size="sm" :href="route('student.requirements')" wire:navigate aria-label="Back to requirements" />

        <div class="min-w-0 flex-1 basis-48 px-1">
            <h1 class="truncate text-base font-semibold text-zinc-950 dark:text-white">{{ $this->requirement->label }}</h1>
            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ RequirementForm::note($key) }}</p>
        </div>

        <flux:button type="submit" form="requirement-form" variant="primary" size="sm" icon="paper-airplane" class="max-sm:w-full">
            Submit to coordinator
        </flux:button>
    </header>

    {{-- Small screens: switch between form and preview instead of squeezing both. --}}
    <div role="tablist" aria-label="Requirement form view" class="grid grid-cols-2 gap-1 rounded-lg bg-zinc-100 p-1 text-sm lg:hidden dark:bg-zinc-800">
        @foreach (['form' => ['Form', 'pencil-square'], 'preview' => ['Preview', 'eye']] as $tab => [$label, $icon])
            <button type="button" role="tab" x-on:click="tab = '{{ $tab }}'" :aria-selected="tab === '{{ $tab }}'"
                :class="tab === '{{ $tab }}' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500'"
                class="flex items-center justify-center gap-2 rounded-md py-2 font-medium transition">
                <flux:icon :name="$icon" variant="mini" class="size-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:h-[calc(100dvh-15rem)] lg:min-h-[32rem] lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <form id="requirement-form" wire:submit="submit" :class="tab !== 'form' && 'max-lg:hidden'"
            x-on:input.debounce.600ms="$dispatch('requirement-preview')"
            class="@container space-y-4 lg:overflow-y-auto lg:overscroll-contain lg:pr-1 lg:pb-4">
            <flux:callout icon="sparkles" variant="secondary" inline>
                <flux:callout.text>We prefilled what we know from your profile and placement. Check everything, then submit. The preview shows exactly what your coordinator receives.</flux:callout.text>
            </flux:callout>

            <div class="grid items-start gap-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm @md:grid-cols-2 dark:border-zinc-800 dark:bg-zinc-900">
                @foreach (RequirementForm::fields($key) as $name => $field)
                    <div @class(['@md:col-span-2' => $field['wide'] || $field['type'] === 'checkboxes']) wire:key="field-{{ $name }}">
                        @switch($field['type'])
                            @case('textarea')
                                <flux:textarea wire:model="form.{{ $name }}" :label="$field['label']" rows="3"
                                    :badge="$field['required'] ? null : 'Optional'" :placeholder="$field['placeholder']" />
                            @break

                            @case('select')
                                <flux:select wire:model="form.{{ $name }}" :label="$field['label']" placeholder="Choose…"
                                    :badge="$field['required'] ? null : 'Optional'">
                                    @foreach ($field['options'] as $option)
                                        <flux:select.option :value="$option">{{ ucfirst($option) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @break

                            @case('checkboxes')
                                <flux:checkbox.group wire:model="form.{{ $name }}" :label="$field['label']" class="grid gap-2">
                                    @foreach ($field['options'] as $option)
                                        <flux:checkbox :value="$option" :label="$option" />
                                    @endforeach
                                </flux:checkbox.group>
                            @break

                            @default
                                <flux:input wire:model="form.{{ $name }}" :type="$field['type']" :label="$field['label']"
                                    :badge="$field['required'] ? null : 'Optional'" :placeholder="$field['placeholder']" />
                        @endswitch
                    </div>
                @endforeach
            </div>
        </form>

        {{-- Live PDF preview of the unsaved answers: the same document that gets submitted. --}}
        <x-pdf-preview source="async () => Uint8Array.from(atob(await $wire.previewPdf()), (c) => c.charCodeAt(0))"
            x-on:requirement-preview.window="load()" x-bind:class="tab !== 'preview' && 'max-lg:hidden'" />
    </div>
</div>
