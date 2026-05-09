<?php

use App\Models\StudentNote;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Notes')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Productivity', 'href' => '#'],
        ['label' => 'Notes'],
    ];

    public string $title = 'Notes';

    public string $description = 'Keep private OJT notes, reminders, and drafts in one place.';

    public string $search = '';

    public ?int $selected_note_id = null;

    public string $note_title = '';

    public string $note_body = '';

    public bool $is_pinned = false;

    public ?string $last_saved = null;

    public function mount(): void
    {
        if ($note = $this->firstVisibleNote()) {
            $this->loadNote($note);
        }
    }

    protected function studentId(): int
    {
        return (int) Auth::id();
    }

    protected function baseNotesQuery(): Builder
    {
        return StudentNote::query()->where('student_id', $this->studentId());
    }

    protected function filteredNotesQuery(): Builder
    {
        return $this->baseNotesQuery()
            ->when($this->search !== '', function (Builder $query) {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', $search)
                        ->orWhere('body', 'like', $search);
                });
            });
    }

    protected function firstVisibleNote(): ?StudentNote
    {
        return $this->filteredNotesQuery()
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->first();
    }

    protected function loadNote(StudentNote $note): void
    {
        $this->selected_note_id = $note->id;
        $this->note_title = $note->title;
        $this->note_body = $note->body ?? '';
        $this->is_pinned = (bool) $note->is_pinned;
        $this->last_saved = $note->updated_at?->format('g:i A');
    }

    protected function resetEditor(): void
    {
        $this->selected_note_id = null;
        $this->note_title = '';
        $this->note_body = '';
        $this->is_pinned = false;
        $this->last_saved = null;
    }

    protected function selectedNote(): ?StudentNote
    {
        if (! $this->selected_note_id) {
            return null;
        }

        return $this->baseNotesQuery()->whereKey($this->selected_note_id)->first();
    }

    public function updatedSearch(): void
    {
        unset($this->notes);
    }

    public function updatedNoteTitle(): void
    {
        $this->saveCurrentNote();
    }

    public function updatedNoteBody(): void
    {
        $this->saveCurrentNote();
    }

    public function createNote(): void
    {
        $note = StudentNote::query()->create([
            'student_id' => $this->studentId(),
            'title' => 'Untitled Note',
            'body' => '',
        ]);

        $this->search = '';
        $this->loadNote($note);
        unset($this->notes);

        Flux::toast(variant: 'success', heading: 'Note created.', text: 'Your new note is ready.');
    }

    public function selectNote(int $noteId): void
    {
        $note = $this->baseNotesQuery()->whereKey($noteId)->firstOrFail();

        $this->loadNote($note);
    }

    public function saveCurrentNote(): void
    {
        $note = $this->selectedNote();

        if (! $note) {
            return;
        }

        $this->validate([
            'note_title' => ['nullable', 'string', 'max:255'],
            'note_body' => ['nullable', 'string', 'max:20000'],
        ]);

        $note->update([
            'title' => trim($this->note_title) !== '' ? trim($this->note_title) : 'Untitled Note',
            'body' => $this->note_body,
        ]);

        $this->last_saved = now()->format('g:i A');
        unset($this->notes);
    }

    public function togglePin(): void
    {
        $note = $this->selectedNote();

        if (! $note) {
            return;
        }

        $note->update(['is_pinned' => ! $note->is_pinned]);
        $this->is_pinned = (bool) $note->fresh()->is_pinned;
        unset($this->notes);
    }

    public function deleteSelectedNote(): void
    {
        $note = $this->selectedNote();

        if (! $note) {
            return;
        }

        $note->delete();
        unset($this->notes);

        if ($nextNote = $this->firstVisibleNote()) {
            $this->loadNote($nextNote);
        } else {
            $this->resetEditor();
        }

        Flux::toast(variant: 'success', heading: 'Note deleted.', text: 'The note was removed.');
    }

    public function preview(?string $body): string
    {
        $preview = trim((string) preg_replace('/\s+/', ' ', $body ?? ''));

        return $preview === '' ? 'No additional text' : Str::limit($preview, 90);
    }

    #[Computed]
    public function notes(): Collection
    {
        return $this->filteredNotesQuery()
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function totalNotes(): int
    {
        return $this->baseNotesQuery()->count();
    }
};
