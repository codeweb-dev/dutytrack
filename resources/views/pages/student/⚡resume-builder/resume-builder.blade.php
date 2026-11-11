@php
    $templates = \App\Enums\ResumeTemplate::cases();
    $current = \App\Enums\ResumeTemplate::tryFrom($template) ?? \App\Enums\ResumeTemplate::Earist;
    $levels = ['College', 'Senior High School', 'Junior High School', 'Elementary', 'Vocational / Technical', 'Graduate Studies', 'Other'];
@endphp

<div x-data="resumeEditor" x-on:input="queue()" x-on:change="queue()"
    x-on:resume-saved.window="saved()" x-on:resume-save-invalid.window="invalid()"
    class="flex flex-col gap-4">

    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="/dashboard">
            <img src="{{ asset('logo.png') }}" alt="logo" class="h-4 w-4 object-cover" />
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('student.resumes')" wire:navigate>Resume Builder</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $title ?: 'Untitled' }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    {{-- Toolbar --}}
    <header class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 bg-white p-2 shadow-sm sm:gap-3 sm:p-3 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:button icon="arrow-left" variant="ghost" size="sm" :href="route('student.resumes')" wire:navigate aria-label="Back to my resumes" />

        <div class="min-w-0 flex-1 basis-40">
            <label for="resume-title" class="sr-only">Resume title</label>
            <input id="resume-title" type="text" wire:model="title" maxlength="120" placeholder="Resume title"
                class="w-full truncate rounded-md border border-transparent bg-transparent px-2 py-1 text-base font-semibold text-zinc-950 outline-none hover:border-zinc-200 focus:border-zinc-300 focus:ring-2 focus:ring-red-500/20 dark:text-white dark:hover:border-zinc-700" />
            <flux:error name="title" class="px-2" />
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
            <flux:modal.trigger name="resume-templates">
                <flux:button size="sm" variant="outline" class="min-w-0 max-sm:flex-1" aria-label="Change template, current: {{ $current->label() }}">
                    <span class="w-4"><x-resume.template-thumb :template="$current" class="shadow-none" /></span>
                    <span class="truncate">{{ $current->label() }}</span>
                    <flux:icon name="chevron-down" variant="micro" />
                </flux:button>
            </flux:modal.trigger>

            <flux:button size="sm" variant="outline" icon="cloud-arrow-up" x-on:click="persist()" aria-label="Save resume">
                <span class="max-sm:hidden">Save</span>
            </flux:button>

            <flux:button size="sm" variant="primary" icon="arrow-down-tray"
                x-on:click="download(@js(route('student.resumes.download', $resumeId)))">
                Download<span class="max-sm:hidden">&nbsp;PDF</span>
            </flux:button>
        </div>
    </header>

    {{-- Small screens: switch between editor and preview instead of squeezing both. --}}
    <div role="tablist" aria-label="Resume builder view" class="grid grid-cols-2 gap-1 rounded-lg bg-zinc-100 p-1 text-sm lg:hidden dark:bg-zinc-800">
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
            <x-resume.section title="Personal Information" icon="user" description="Name, contact details, and 2x2 photo" open
                :invalid="$errors->hasAny(['data.first_name', 'data.middle_name', 'data.last_name', 'data.phone', 'data.email', 'data.address'])">
                <div class="grid items-start gap-3 @xs:grid-cols-3">
                    <flux:input wire:model="data.first_name" label="First name" autocomplete="given-name" maxlength="60" />
                    <flux:input wire:model="data.middle_name" label="Middle name" placeholder="Optional" autocomplete="additional-name" maxlength="60" />
                    <flux:input wire:model="data.last_name" label="Last name" autocomplete="family-name" maxlength="60" />
                </div>
                <p class="-mt-1 text-xs text-zinc-500 dark:text-zinc-400" wire:key="formatted-name">
                    Appears on your resume as
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $this->fullName() ?: '—' }}</span>
                </p>
                <div class="grid items-start gap-3 @xs:grid-cols-2">
                    <flux:input wire:model="data.phone" label="Phone number" type="tel" autocomplete="tel" placeholder="0912 345 6789" maxlength="30" />
                    <flux:input wire:model="data.email" label="Email address" type="email" autocomplete="email" placeholder="you@example.com" maxlength="120" />
                </div>
                <flux:input wire:model="data.address" label="Complete address" autocomplete="street-address" placeholder="Blk 1 Lot 2, Brgy. Sample, General Mariano Alvarez, Cavite" maxlength="255" />

                <livewire:resume.photo :resume-id="$resumeId" :key="'resume-photo-'.$resumeId" />
            </x-resume.section>

            <x-resume.section title="Career Objective" icon="light-bulb" description="What you hope to achieve in your OJT"
                :invalid="$errors->has('data.objective')">
                <flux:textarea wire:model="data.objective" rows="5" maxlength="2000" label="Career objective"
                    placeholder="To obtain an On-the-Job Training position where I can apply my knowledge in information technology, develop my practical skills, and contribute to the success of the organization." />
                <flux:text class="text-xs">Keep it to two or three sentences. Line breaks are kept in the PDF.</flux:text>
            </x-resume.section>

            <x-resume.section title="Technical Skills" icon="wrench-screwdriver" description="Tools, software, and hands-on skills"
                :count="count(array_filter($data['technical_skills'], 'filled'))" :invalid="$errors->has('data.technical_skills.*')">
                <x-resume.list-field field="technical_skills" :items="$data['technical_skills']" label="Technical skill"
                    placeholder="e.g. Hardware Troubleshooting" add-label="Add skill" />
            </x-resume.section>

            <x-resume.section title="Personal Skills" icon="sparkles" description="Soft skills and work habits"
                :count="count(array_filter($data['personal_skills'], 'filled'))" :invalid="$errors->has('data.personal_skills.*')">
                <x-resume.list-field field="personal_skills" :items="$data['personal_skills']" label="Personal skill"
                    placeholder="e.g. Written and Verbal Communication" add-label="Add skill" />
            </x-resume.section>

            <x-resume.section title="Education" icon="academic-cap" description="College, senior high, junior high, and more"
                :count="count($data['education'])" :invalid="$errors->has('data.education.*')">
                @forelse ($data['education'] as $i => $entry)
                    <x-resume.entry wire:key="education-{{ $i }}" field="education" :index="$i" :count="count($data['education'])"
                        label="education entry" :title="($entry['school'] ?? '') ?: (($entry['level'] ?? '') ?: 'New education entry')">
                        <flux:select wire:model="data.education.{{ $i }}.level" label="Education level">
                            @foreach ($levels as $level)
                                <flux:select.option :value="$level">{{ $level }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="data.education.{{ $i }}.program" label="Degree / program / strand"
                            placeholder="Bachelor of Science in Information Technology" maxlength="150" />
                        <div class="@xs:col-span-2">
                            <flux:input wire:model="data.education.{{ $i }}.school" label="School name" placeholder="EARIST Cavite Campus" maxlength="150" />
                        </div>
                        <div class="@xs:col-span-2">
                            <flux:input wire:model="data.education.{{ $i }}.address" label="School address" placeholder="General Mariano Alvarez, Cavite" maxlength="255" />
                        </div>
                        <flux:input wire:model="data.education.{{ $i }}.start_year" label="Start year" type="number" inputmode="numeric" min="1950" max="2100" placeholder="2023" />
                        <flux:input wire:model="data.education.{{ $i }}.end_year" label="End / expected year" type="number" inputmode="numeric" min="1950" max="2100" placeholder="2027" />
                        <div class="@xs:col-span-2">
                            <flux:checkbox wire:model="data.education.{{ $i }}.current" label="Currently enrolled"
                                description="Shows as “Graduating, [year]”, or “Present” if no year is set." />
                        </div>
                    </x-resume.entry>
                @empty
                    <p class="rounded-md bg-zinc-50 px-3 py-4 text-center text-sm text-zinc-500 dark:bg-zinc-800/60">No education entries yet.</p>
                @endforelse
                <flux:button size="sm" variant="ghost" icon="plus" wire:click="addItem('education')">Add education</flux:button>
            </x-resume.section>

            <x-resume.section title="Achievements / Responsibilities / Organizations" icon="trophy" description="Awards, honors, positions, and memberships"
                :count="count($data['achievements'])" :invalid="$errors->has('data.achievements.*')">
                @forelse ($data['achievements'] as $i => $entry)
                    <x-resume.entry wire:key="achievements-{{ $i }}" field="achievements" :index="$i" :count="count($data['achievements'])"
                        label="achievement" :title="($entry['title'] ?? '') ?: 'New achievement'">
                        <div class="@xs:col-span-2">
                            <flux:input wire:model="data.achievements.{{ $i }}.title" label="Achievement / position" placeholder="With High Honors" maxlength="150" />
                        </div>
                        <flux:input wire:model="data.achievements.{{ $i }}.organization" label="Organization / school" placeholder="Senior High School" maxlength="150" />
                        <flux:input wire:model="data.achievements.{{ $i }}.period" label="Year or date range" placeholder="2019 - 2020" maxlength="40" />
                        <div class="@xs:col-span-2">
                            <flux:textarea wire:model="data.achievements.{{ $i }}.description" label="Description (optional)" rows="2" maxlength="500" />
                        </div>
                    </x-resume.entry>
                @empty
                    <p class="rounded-md bg-zinc-50 px-3 py-4 text-center text-sm text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        Add honors, awards, class officer roles, or organizations you belong to.
                    </p>
                @endforelse
                <flux:button size="sm" variant="ghost" icon="plus" wire:click="addItem('achievements')">Add achievement</flux:button>
            </x-resume.section>

            <x-resume.section title="Working Experience" icon="briefcase" description="Optional. Hidden on your resume when empty"
                :count="count($data['experience'])" :invalid="$errors->has('data.experience.*')">
                @forelse ($data['experience'] as $i => $entry)
                    <x-resume.entry wire:key="experience-{{ $i }}" field="experience" :index="$i" :count="count($data['experience'])"
                        label="work experience" :title="($entry['title'] ?? '') ?: 'New work experience'">
                        <flux:input wire:model="data.experience.{{ $i }}.title" label="Job title" placeholder="Factory Worker" maxlength="120" />
                        <flux:input wire:model="data.experience.{{ $i }}.type" label="Employment type (optional)" placeholder="Part-time, Production" maxlength="60" />
                        <div class="@xs:col-span-2">
                            <flux:input wire:model="data.experience.{{ $i }}.company" label="Company name" maxlength="150" />
                        </div>
                        <flux:input wire:model="data.experience.{{ $i }}.start" label="Start date" type="month" placeholder="YYYY-MM" />
                        <flux:input wire:model="data.experience.{{ $i }}.end" label="End date" type="month" placeholder="YYYY-MM"
                            x-bind:disabled="$wire.data.experience[{{ $i }}]?.current" />
                        <div class="@xs:col-span-2">
                            <flux:checkbox wire:model="data.experience.{{ $i }}.current" label="I currently work here" />
                        </div>
                        <div class="@xs:col-span-2">
                            <flux:textarea wire:model="data.experience.{{ $i }}.description" label="Short job description" rows="2" maxlength="1000" />
                        </div>
                    </x-resume.entry>
                @empty
                    <p class="rounded-md bg-zinc-50 px-3 py-4 text-center text-sm text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        No work experience yet? That's fine. This section won't appear on your resume.
                    </p>
                @endforelse
                <flux:button size="sm" variant="ghost" icon="plus" wire:click="addItem('experience')">Add work experience</flux:button>
            </x-resume.section>

            <x-resume.section title="Certification & Signature" icon="pencil-square" description="Certification statement and your signature">
                <flux:switch wire:model="data.show_certification" label="Include certification statement and signature" align="left" />
                <blockquote class="rounded-md border-l-2 border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 italic dark:border-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    {{ \App\Models\Resume::CERTIFICATION }}
                </blockquote>

                <livewire:resume.signature :resume-id="$resumeId" :key="'resume-signature-'.$resumeId" />
            </x-resume.section>
        </div>

        {{-- Live PDF preview: the same file the Download button produces. --}}
        <x-pdf-preview :source="\Illuminate\Support\Js::from(route('student.resumes.preview', $resumeId))"
            x-on:resume-saved.window="load()" x-bind:class="tab !== 'preview' && 'max-lg:hidden'" />
    </div>

    <flux:modal name="resume-templates" class="w-full max-w-2xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Choose a template</flux:heading>
                <flux:text class="mt-1">Your information stays the same. Only the design changes.</flux:text>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4" role="radiogroup" aria-label="Resume templates">
                @foreach ($templates as $option)
                    <button type="button" role="radio" aria-checked="{{ $current === $option ? 'true' : 'false' }}"
                        wire:click="switchTemplate('{{ $option->value }}')" x-on:click="$flux.modal('resume-templates').close()"
                        @class([
                            'group flex flex-col gap-2 rounded-lg p-2 text-left transition focus-visible:outline-2 focus-visible:outline-red-500',
                            'bg-red-50 ring-2 ring-red-500 dark:bg-red-500/10' => $current === $option,
                            'ring-1 ring-zinc-200 hover:bg-zinc-50 hover:ring-zinc-300 dark:ring-zinc-700 dark:hover:bg-zinc-800' => $current !== $option,
                        ])>
                        <x-resume.template-thumb :template="$option" />
                        <span class="flex items-center gap-1 text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $option->label() }}
                            @if ($current === $option)
                                <flux:icon name="check-circle" variant="mini" class="size-4 text-red-600" />
                            @endif
                        </span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $option->description() }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </flux:modal>
</div>
