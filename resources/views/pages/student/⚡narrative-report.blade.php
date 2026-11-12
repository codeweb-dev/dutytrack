<?php

use App\Models\StudentRequirement;
use App\Support\NarrativeReport;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Narrative Report')] class extends Component
{
    use WithFileUploads;

    public array $data = [];

    /** Pending org chart / sketch uploads, keyed by NarrativeReport::IMAGES. */
    public array $uploads = [];

    public function mount(): void
    {
        $defaults = NarrativeReport::defaults(Auth::user());

        $this->data = array_merge($defaults, array_intersect_key($this->requirement()->form_data ?? [], $defaults));
    }

    protected function requirement(): StudentRequirement
    {
        $requirement = StudentRequirement::query()->where('student_id', Auth::id())->where('key', NarrativeReport::KEY)->first();

        // Approved requirements are locked.
        abort_if(! $requirement || $requirement->is_completed, 403);

        return $requirement;
    }

    #[Computed]
    public function photoOptions()
    {
        return NarrativeReport::photoOptions(Auth::user());
    }

    #[Computed]
    public function letters(): array
    {
        return StudentRequirement::query()
            ->where('student_id', Auth::id())
            ->whereIn('key', ['application_for_ojt_individual', 'acceptance_letter'])
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (StudentRequirement $r) => [$r->label => filled($r->form_data)])
            ->all();
    }

    /** Autosaves the draft. Invalid input stays in the editor and the status tells the student what to fix. */
    public function save(): bool
    {
        try {
            $validated = $this->validate(NarrativeReport::rules(Auth::user()), attributes: NarrativeReport::attributes());
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->dispatch('resume-save-invalid');

            return false;
        }

        $this->requirement()->update(['form_data' => $validated['data']]);
        $this->dispatch('resume-saved');

        return true;
    }

    public function updatedUploads($file, string $type): void
    {
        abort_unless(in_array($type, NarrativeReport::IMAGES, true), 404);

        $this->validate(["uploads.$type" => ['image', 'max:5120']], attributes: ["uploads.$type" => 'image']);

        $this->deleteImage($type);
        $this->data[$type] = $file->store(NarrativeReport::folder(Auth::user()), 'local');
        unset($this->uploads[$type]);

        $this->save();
    }

    public function removeImage(string $type): void
    {
        abort_unless(in_array($type, NarrativeReport::IMAGES, true), 404);

        $this->deleteImage($type);
        $this->data[$type] = null;

        $this->save();
    }

    protected function deleteImage(string $type): void
    {
        $path = $this->data[$type] ?? null;

        // $data is client-editable; "../" would let the prefix check reach other students' files.
        if (is_string($path) && str_starts_with($path, NarrativeReport::folder(Auth::user())) && ! str_contains($path, '..')) {
            Storage::disk('local')->delete($path);
        }
    }

    /** Live preview of the current draft, as base64 PDF bytes. */
    public function previewPdf(): string
    {
        return base64_encode(NarrativeReport::render(Auth::user(), $this->data));
    }

    public function submit(): void
    {
        try {
            $data = $this->validate(NarrativeReport::rules(Auth::user(), submitting: true), attributes: NarrativeReport::attributes())['data'];
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            Flux::toast(variant: 'danger', heading: 'Some sections still need content.', text: 'Fill in the sections marked in red, then submit again.');

            return;
        }

        $requirement = $this->requirement();
        $path = 'student-requirements/'.Auth::id().'/'.NarrativeReport::KEY.'-'.now()->format('YmdHis').'.pdf';

        Storage::disk('local')->put($path, NarrativeReport::render(Auth::user(), $data));
        $requirement->submitFile($path, $data);

        session()->flash('requirement-submitted', $requirement->label);

        $this->redirectRoute('student.requirements', navigate: true);
    }
};
?>

@php
    $details = \App\Support\NarrativeReport::DETAILS;
    $sections = \App\Support\NarrativeReport::SECTIONS;
    $textarea = fn (string $key, int $rows = 8, ?string $placeholder = null) => compact('key', 'rows', 'placeholder');
@endphp

<div x-data="resumeEditor" x-on:input="queue()" x-on:change="queue()"
    x-on:resume-saved.window="saved()" x-on:resume-save-invalid.window="invalid()"
    class="flex flex-col gap-4">

    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="/dashboard">
            <img src="{{ asset('logo.png') }}" alt="logo" class="h-4 w-4 object-cover" />
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('student.requirements')" wire:navigate>OJT Requirements</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Narrative Report</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Toolbar --}}
    <header class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-sm sm:gap-3 sm:p-3 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:button icon="arrow-left" variant="ghost" size="sm" :href="route('student.requirements')" wire:navigate aria-label="Back to requirements" tooltip="Back to requirements" />

        <div class="min-w-0 flex-1 basis-40 px-1">
            <h1 class="truncate text-base font-semibold text-zinc-950 dark:text-white">Narrative Report</h1>
            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">Your draft saves automatically. Submit when every section is ready.</p>
        </div>

        {{-- Save status --}}
        <div role="status" aria-live="polite" class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
            :class="{
                'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300': status === 'saved',
                'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300': status === 'dirty',
                'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300': status === 'saving',
                'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300': status === 'invalid' || status === 'failed',
            }">
            <span class="size-1.5 rounded-full bg-current" :class="status === 'saving' && 'animate-pulse'"></span>
            <span x-text="{ saved: 'Saved', dirty: 'Unsaved changes', saving: 'Saving…', invalid: 'Fix highlighted fields', failed: 'Save failed' }[status]">Saved</span>
            <button type="button" x-show="status === 'failed'" x-cloak x-on:click="persist()" class="underline underline-offset-2">Retry</button>
        </div>

        <div class="flex w-full items-center gap-2 sm:w-auto">
            <flux:button size="sm" variant="outline" icon="cloud-arrow-up" x-on:click="persist()" aria-label="Save draft">
                <span class="max-sm:hidden">Save</span>
            </flux:button>
            <flux:button size="sm" variant="primary" icon="paper-airplane" class="max-sm:flex-1"
                x-on:click="persist().then(() => $wire.submit())">
                Submit to coordinator
            </flux:button>
        </div>
    </header>

    {{-- Small screens: switch between editor and preview instead of squeezing both. --}}
    <div role="tablist" aria-label="Narrative report view" class="grid grid-cols-2 gap-1 rounded-lg bg-zinc-100 p-1 text-sm lg:hidden dark:bg-zinc-800">
        @foreach (['editor' => ['Editor', 'pencil-square'], 'preview' => ['Preview', 'eye']] as $tab => [$label, $icon])
            <button type="button" role="tab" x-on:click="tab = '{{ $tab }}'" :aria-selected="tab === '{{ $tab }}'"
                :class="tab === '{{ $tab }}' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500'"
                class="flex items-center justify-center gap-2 rounded-md py-2 font-medium transition">
                <flux:icon :name="$icon" variant="mini" class="size-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:h-[calc(100dvh-17rem)] lg:min-h-[32rem] lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        {{-- Editor --}}
        <div :class="tab !== 'editor' && 'max-lg:hidden'" class="@container space-y-3 lg:overflow-y-auto lg:overscroll-contain lg:pr-1 lg:pb-4">
            <x-resume.section title="Cover and Signatories" icon="document-text" description="Title page, approval sheet, and certification details" open
                :invalid="$errors->hasAny(array_map(fn ($key) => 'data.'.$key, array_keys($details)))">
                <flux:callout icon="sparkles" variant="secondary" inline>
                    <flux:callout.text>Prefilled from your profile and placement.</flux:callout.text>
                </flux:callout>
                <div class="grid items-start gap-3 @md:grid-cols-2">
                    @foreach ($details as $key => $label)
                        <div @class(['@md:col-span-2' => in_array($key, ['name', 'program', 'company_name', 'company_location', 'coordinator_name'], true)])>
                            <flux:input wire:model="data.{{ $key }}" :label="$label" maxlength="255"
                                :badge="in_array($key, ['course_code', 'manager_name', 'manager_position', 'supervisor_position'], true) ? 'Optional' : null"
                                :placeholder="$key === 'course_code' ? 'COED 421' : null" />
                        </div>
                    @endforeach
                </div>
            </x-resume.section>

            @foreach ([
                ['Acknowledgment', 'heart', 'People who helped you during your OJT', [$textarea('acknowledgment', 10)]],
                ['Introduction', 'light-bulb', 'What the report covers and why you took the OJT', [$textarea('introduction', 8, 'Describe your OJT, the company, and what this report covers.')]],
                ['Course Syllabus', 'academic-cap', 'Optional', [$textarea('course_syllabus', 6)]],
                ['The Company', 'building-office-2', 'Vision and mission, profile, history, products/services', [
                    $textarea('vision_mission', 5), $textarea('corporate_profile', 5), $textarea('history', 5), $textarea('products_services', 5),
                ]],
                ['Job Description', 'briefcase', 'Your duties and responsibilities', [$textarea('job_description', 6)]],
                ['Experiences', 'calendar-days', 'Prefilled from your daily accomplishment reports', [$textarea('experiences', 14)]],
                ['Evaluation/Impression', 'chat-bubble-left-ellipsis', 'How you would evaluate your training', [$textarea('evaluation', 6)]],
                ['Insights Gained', 'sparkles', 'Prefilled from the skills in your accomplishment reports', [$textarea('insights', 8)]],
            ] as [$title, $icon, $description, $fields])
                <x-resume.section :title="$title" :icon="$icon" :description="$description"
                    :invalid="$errors->hasAny(array_map(fn ($field) => 'data.'.$field['key'], $fields))">
                    @foreach ($fields as $field)
                        <flux:textarea wire:model="data.{{ $field['key'] }}" :rows="$field['rows']" maxlength="20000"
                            :label="count($fields) > 1 ? $sections[$field['key']]['label'] : null"
                            :aria-label="$sections[$field['key']]['label']"
                            :badge="$sections[$field['key']]['required'] ? null : 'Optional'"
                            :placeholder="$field['placeholder']" />
                    @endforeach

                    @if ($title === 'The Company')
                        @foreach (['org_chart' => 'Organizational chart', 'sketch' => 'Location sketch'] as $type => $label)
                            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="image-{{ $type }}">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon :name="$data[$type] ? 'check-circle' : 'photo'" class="size-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $label }} <span class="text-xs font-normal text-zinc-500">Optional</span></div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        <span wire:loading.remove wire:target="uploads.{{ $type }}">{{ $data[$type] ? 'Added. It appears on its own page.' : 'JPG or PNG, up to 5 MB.' }}</span>
                                        <span wire:loading wire:target="uploads.{{ $type }}">Uploading…</span>
                                    </div>
                                    <flux:error name="uploads.{{ $type }}" />
                                </div>
                                <div class="flex items-center gap-1" x-data>
                                    <input type="file" class="sr-only" x-ref="file" wire:model="uploads.{{ $type }}" accept="image/png,image/jpeg" tabindex="-1" aria-label="Upload {{ $label }}" />
                                    <flux:button type="button" size="xs" icon="arrow-up-tray" x-on:click="$refs.file.click()">{{ $data[$type] ? 'Replace' : 'Upload' }}</flux:button>
                                    @if ($data[$type])
                                        <flux:button type="button" size="xs" variant="ghost" icon="trash" wire:click="removeImage('{{ $type }}')" aria-label="Remove {{ $label }}" tooltip="Remove {{ $label }}" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </x-resume.section>
            @endforeach

            <x-resume.section title="Photographs" icon="photo" description="Pick photos from your accomplishment reports"
                :count="count($data['photos'] ?? [])" :invalid="$errors->has('data.photos*')">
                @if ($this->photoOptions->isEmpty())
                    <flux:text>Photos you attach to your accomplishment reports will show up here.</flux:text>
                @else
                    <flux:text class="text-xs">Choose up to {{ \App\Support\NarrativeReport::MAX_PHOTOS }}. Each photo is captioned with its report date.</flux:text>
                    <flux:error name="data.photos" />
                    <div class="grid grid-cols-3 gap-2 @md:grid-cols-4">
                        @foreach ($this->photoOptions as $photo)
                            <label wire:key="photo-{{ $photo['key'] }}" class="group relative block cursor-pointer overflow-hidden rounded-lg ring-1 ring-zinc-200 has-checked:ring-2 has-checked:ring-red-500 dark:ring-zinc-700">
                                <input type="checkbox" class="peer sr-only" wire:model="data.photos" value="{{ $photo['key'] }}" />
                                <img src="{{ $photo['url'] }}" alt="Accomplishment photo from {{ $photo['date'] }}" loading="lazy" class="aspect-square w-full object-cover" />
                                <span class="absolute inset-x-0 bottom-0 bg-black/55 px-1.5 py-0.5 text-[10px] text-white">{{ $photo['date'] }}</span>
                                <span class="absolute top-1.5 right-1.5 hidden size-5 items-center justify-center rounded-full bg-red-600 text-white peer-checked:flex">
                                    <flux:icon name="check" variant="micro" class="size-3.5" />
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </x-resume.section>

            <x-resume.section title="Appendices" icon="paper-clip" description="Letters and Daily Time Record added for you">
                <flux:checkbox wire:model="data.include_letters" label="Application and acceptance letters"
                    :description="collect($this->letters)->map(fn ($filled, $label) => $label.($filled ? ' ✓' : ' (fill it out first to include it)'))->implode(' · ')" />
                <flux:checkbox wire:model="data.include_dtr" label="Daily Time Record" description="One page per month from your attendance." />
                <flux:text class="text-xs">Attach your Certificate of Completion, MOA, and curriculum vitae to the printed copy.</flux:text>
            </x-resume.section>
        </div>

        {{-- Live PDF preview of the saved draft: the same document that gets submitted. --}}
        <x-pdf-preview source="async () => Uint8Array.from(atob(await $wire.previewPdf()), (c) => c.charCodeAt(0))"
            x-on:resume-saved.window="load()" x-bind:class="tab !== 'preview' && 'max-lg:hidden'" />
    </div>
</div>
