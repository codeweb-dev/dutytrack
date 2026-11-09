<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid min-h-[calc(100vh-14rem)] lg:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="flex min-h-0 flex-col border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 lg:border-b-0 lg:border-r">
                <div class="space-y-3 px-4 pt-4 pb-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-zinc-950 dark:text-white">
                                {{ $showArchivedNotes ? 'Archived' : 'Notes' }}
                            </h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                @if ($showArchivedNotes)
                                    {{ $this->archivedNotes->count() }} {{ \Illuminate\Support\Str::plural('note', $this->archivedNotes->count()) }}
                                @else
                                    {{ $this->totalNotes }} {{ \Illuminate\Support\Str::plural('note', $this->totalNotes) }}
                                @endif
                            </p>
                        </div>

                        @unless ($showArchivedNotes)
                            <flux:tooltip content="New note">
                                <flux:button type="button" size="sm" variant="ghost" icon="pencil-square" wire:click="createNote" aria-label="New note" />
                            </flux:tooltip>
                        @endunless
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm"
                            :placeholder="$showArchivedNotes ? 'Search archived' : 'Search'" clearable class="flex-1" />

                        <flux:tooltip :content="$showArchivedNotes ? 'Back to notes' : 'Archived notes'">
                            <flux:button type="button" size="sm" icon="archive-box" wire:click="$toggle('showArchivedNotes')"
                                :variant="$showArchivedNotes ? 'primary' : 'ghost'"
                                :aria-label="$showArchivedNotes ? 'Back to notes' : 'Show archived notes'"
                                :aria-pressed="$showArchivedNotes ? 'true' : 'false'" />
                        </flux:tooltip>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-2 pb-2">
                    @if ($showArchivedNotes)
                        @forelse ($this->archivedNotes as $note)
                            <div wire:key="archived-student-note-{{ $note->id }}"
                                class="flex items-center gap-2 border-b border-zinc-200/70 px-3 py-2.5 last:border-b-0 dark:border-zinc-800">
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $note->title }}</div>
                                    <div class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $note->deleted_at?->format('n/j/y') }}</span>
                                        {{ $this->preview($note->body) }}
                                    </div>
                                </div>
                                <flux:tooltip content="Restore">
                                    <flux:button type="button" size="xs" variant="ghost" icon="arrow-uturn-left"
                                        wire:click="restoreNote({{ $note->id }})" aria-label="Restore {{ $note->title }}" />
                                </flux:tooltip>
                            </div>
                        @empty
                            <div class="flex min-h-72 flex-col items-center justify-center px-6 text-center">
                                <flux:icon name="archive-box" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-3 text-sm text-zinc-500">No archived notes</p>
                            </div>
                        @endforelse
                    @else
                        @forelse ($this->notes->groupBy(fn ($note) => $note->is_pinned ? 'Pinned' : 'Notes') as $group => $notes)
                            @if ($this->notes->contains('is_pinned', true))
                                <h3 class="px-3 pt-3 pb-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $group }}</h3>
                            @endif

                            @foreach ($notes as $note)
                                <button type="button" wire:key="student-note-{{ $note->id }}"
                                    wire:click="selectNote({{ $note->id }})"
                                    @class([
                                        'block w-full rounded-lg px-3 py-2.5 text-left transition',
                                        'bg-red-500/15 dark:bg-red-500/20' => $selected_note_id === $note->id,
                                        'hover:bg-zinc-200/60 dark:hover:bg-zinc-800' => $selected_note_id !== $note->id,
                                    ])>
                                    <div class="flex items-center gap-1.5">
                                        @if ($note->is_pinned)
                                            <flux:icon name="bookmark" variant="micro" class="shrink-0 text-red-500" />
                                        @endif
                                        <span class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $note->title }}</span>
                                    </div>
                                    <div class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        <span class="text-zinc-700 dark:text-zinc-300">
                                            {{ $note->updated_at?->isToday() ? $note->updated_at->format('g:i A') : $note->updated_at?->format('n/j/y') }}
                                        </span>
                                        {{ $this->preview($note->body) }}
                                    </div>
                                </button>
                            @endforeach
                        @empty
                            <div class="flex min-h-72 flex-col items-center justify-center px-6 text-center">
                                <flux:icon name="document-text" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-3 text-sm text-zinc-500">{{ $search !== '' ? 'No matching notes' : 'No notes yet' }}</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </aside>

            <main class="min-h-0 bg-white dark:bg-zinc-950">
                @if ($selected_note_id)
                    <div class="flex min-h-full flex-col" wire:key="note-editor-{{ $selected_note_id }}">
                        <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($last_saved)
                                    Edited {{ $last_saved }}
                                @else
                                    Not saved yet
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <flux:button type="button" size="sm" variant="ghost" :icon="$is_pinned ? 'bookmark-slash' : 'bookmark'"
                                    wire:click="togglePin">
                                    {{ $is_pinned ? 'Unpin' : 'Pin' }}
                                </flux:button>
                                <flux:button type="button" size="sm" variant="ghost" icon="archive-box" wire:click="deleteSelectedNote">
                                    Archive
                                </flux:button>
                            </div>
                        </div>

                        <div class="flex min-h-0 flex-1 flex-col px-5 py-5">
                            <input id="note-title" type="text" wire:model.live.debounce.500ms="note_title" aria-label="Note title"
                                placeholder="Untitled Note"
                                class="w-full border-0 bg-transparent p-0 text-3xl font-semibold text-zinc-950 outline-none placeholder:text-zinc-300 focus:ring-0 dark:text-white dark:placeholder:text-zinc-700" />
                            @error('note_title')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                            <textarea id="note-body" wire:model.live.debounce.700ms="note_body" aria-label="Note body"
                                placeholder="Start typing..."
                                class="mt-5 min-h-112 flex-1 resize-none border-0 bg-transparent p-0 text-base leading-8 text-zinc-800 outline-none placeholder:text-zinc-300 focus:ring-0 dark:text-zinc-100 dark:placeholder:text-zinc-700"></textarea>
                            @error('note_body')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="flex min-h-full flex-col items-center justify-center px-6 py-24 text-center">
                        <flux:icon name="document-text" class="size-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-4 text-sm text-zinc-500">No note selected</p>
                        <flux:button type="button" variant="primary" icon="plus" wire:click="createNote" class="mt-6">
                            New Note
                        </flux:button>
                    </div>
                @endif
            </main>
        </div>
    </section>
</div>
