<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid min-h-[calc(100vh-14rem)] lg:grid-cols-[22rem_minmax(0,1fr)]">
            <aside class="flex min-h-0 flex-col border-b border-zinc-200 bg-zinc-50/80 dark:border-zinc-800 dark:bg-zinc-900/60 lg:border-b-0 lg:border-r">
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Notes</h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $this->totalNotes }} {{ \Illuminate\Support\Str::plural('note', $this->totalNotes) }}
                            </p>
                        </div>

                        <flux:button type="button" size="sm" variant="primary" icon="plus" wire:click="createNote">
                            New
                        </flux:button>
                    </div>

                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                        placeholder="Search notes" clearable class="mt-4" />
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-2">
                    @forelse ($this->notes as $note)
                        <button type="button" wire:key="student-note-{{ $note->id }}"
                            wire:click="selectNote({{ $note->id }})"
                            @class([
                                'w-full rounded-lg px-3 py-3 text-left transition',
                                'bg-red-100 text-zinc-950 ring-1 ring-red-200 dark:bg-red-500/20 dark:text-white dark:ring-red-400/20' => $selected_note_id === $note->id,
                                'hover:bg-white dark:hover:bg-zinc-800' => $selected_note_id !== $note->id,
                            ])>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold">
                                        {{ $note->title }}
                                    </div>
                                    <div class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $this->preview($note->body) }}
                                    </div>
                                </div>

                                @if ($note->is_pinned)
                                    <flux:icon name="bookmark" class="mt-0.5 size-4 shrink-0 text-red-600 dark:text-red-300" />
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $note->updated_at?->format('M d, g:i A') }}
                            </div>
                        </button>
                    @empty
                        <div class="flex min-h-72 flex-col items-center justify-center px-6 text-center">
                            <flux:icon name="document-text" class="size-10 text-zinc-400" />
                            <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">No notes found</h3>
                            <flux:button type="button" size="sm" variant="primary" icon="plus" wire:click="createNote" class="mt-4">
                                New Note
                            </flux:button>
                        </div>
                    @endforelse
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
                                <flux:button type="button" size="sm" variant="danger" icon="trash" wire:click="deleteSelectedNote">
                                    Delete
                                </flux:button>
                            </div>
                        </div>

                        <div class="flex min-h-0 flex-1 flex-col px-5 py-5">
                            <label for="note-title" class="mb-2 flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Note title <flux:badge size="sm">Optional</flux:badge>
                            </label>
                            <input id="note-title" type="text" wire:model.live.debounce.500ms="note_title" aria-label="Note title"
                                placeholder="Untitled Note"
                                class="w-full border-0 bg-transparent p-0 text-3xl font-semibold text-zinc-950 outline-none placeholder:text-zinc-300 focus:ring-0 dark:text-white dark:placeholder:text-zinc-700" />
                            @error('note_title')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror

                            <label for="note-body" class="mt-5 mb-2 flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Note body <flux:badge size="sm">Optional</flux:badge>
                            </label>
                            <textarea id="note-body" wire:model.live.debounce.700ms="note_body" aria-label="Note body"
                                placeholder="Start typing..."
                                class="min-h-112 flex-1 resize-none border-0 bg-transparent p-0 text-base leading-8 text-zinc-800 outline-none placeholder:text-zinc-300 focus:ring-0 dark:text-zinc-100 dark:placeholder:text-zinc-700"></textarea>
                            @error('note_body')
                                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="flex min-h-full flex-col items-center justify-center px-6 py-24 text-center">
                        <flux:icon name="document-text" class="size-12 text-zinc-400" />
                        <h2 class="mt-4 text-xl font-semibold text-zinc-950 dark:text-white">No note selected</h2>
                        <flux:button type="button" variant="primary" icon="plus" wire:click="createNote" class="mt-6">
                            New Note
                        </flux:button>
                    </div>
                @endif
            </main>
        </div>
    </section>
</div>
