<div class="flex flex-col gap-6">
    @php($atLimit = $this->resumes->count() >= \App\Models\Resume::MAX_PER_STUDENT)
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

        @if ($this->resumes->isNotEmpty())
            <div class="flex shrink-0 items-center gap-3">
                <flux:text class="text-sm">{{ $this->resumes->count() }} / {{ \App\Models\Resume::MAX_PER_STUDENT }}</flux:text>
                <flux:button variant="primary" icon="plus" wire:click="create" :disabled="$atLimit">
                    New Resume
                </flux:button>
            </div>
        @endif
    </div>

    @if ($atLimit)
        <flux:callout variant="warning" icon="exclamation-triangle"
            heading="You've reached the limit of {{ \App\Models\Resume::MAX_PER_STUDENT }} resumes. Delete one to create or duplicate another." />
    @endif

    @if ($this->resumes->isEmpty())
        <section class="flex flex-col items-center rounded-lg border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid w-full max-w-md grid-cols-4 gap-3">
                @foreach (\App\Enums\ResumeTemplate::cases() as $template)
                    <x-resume.template-thumb :template="$template" />
                @endforeach
            </div>
            <flux:heading size="lg" class="mt-8">Build your first resume</flux:heading>
            <flux:text class="mt-2 max-w-md">
                Start from the EARIST Classic template. We'll fill in what we already know from your DutyTrack
                profile, and you can switch templates any time without losing anything.
            </flux:text>
            <flux:button variant="primary" icon="plus" wire:click="create" class="mt-6">
                Create Resume
            </flux:button>
        </section>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->resumes as $resume)
                <article wire:key="resume-{{ $resume->id }}"
                    class="group flex gap-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 hover:shadow dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                    <a href="{{ route('student.resumes.edit', $resume) }}" wire:navigate class="w-20 shrink-0" tabindex="-1">
                        <x-resume.template-thumb :template="$resume->template" />
                    </a>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <a href="{{ route('student.resumes.edit', $resume) }}" wire:navigate
                            class="truncate font-semibold text-zinc-950 hover:underline dark:text-white">
                            {{ $resume->title }}
                        </a>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $resume->template->label() }}</div>
                        <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                            Edited {{ $resume->updated_at->diffForHumans() }}
                        </div>

                        <div class="mt-auto flex flex-wrap gap-1 pt-3">
                            <flux:button size="sm" variant="primary" icon="pencil-square" :href="route('student.resumes.edit', $resume)" wire:navigate>
                                Edit
                            </flux:button>
                            <flux:button size="sm" variant="ghost" icon="arrow-down-tray" :href="route('student.resumes.download', $resume)" aria-label="Download {{ $resume->title }} as PDF" />
                            <flux:button size="sm" variant="ghost" icon="document-duplicate" wire:click="duplicate({{ $resume->id }})" :disabled="$atLimit" aria-label="Duplicate {{ $resume->title }}" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $resume->id }})" aria-label="Delete {{ $resume->title }}" />
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <flux:modal name="delete-resume" class="w-full max-w-md">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">Delete this resume?</flux:heading>
                <flux:text>
                    The resume, its ID photo, and its signature will be permanently removed. This cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:modal.close class="w-full">
                    <flux:button variant="filled" class="w-full">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="trash" wire:click="delete" class="w-full">Delete Resume</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
