<?php

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('System Announcements')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'System Announcements'],
    ];

    public string $title = 'System Announcements';
    public string $description = 'Manage and view system-wide announcements.';

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $search = '';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
        unset($this->announcements);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->announcements);
    }

    #[On('announcement-saved')]
    public function refreshAnnouncements(): void
    {
        $this->resetPage();
        unset($this->announcements);
    }

    #[Computed]
    public function announcements(): LengthAwarePaginator
    {
        $query = Announcement::query();

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhere('type', 'like', $search);
            });
        }

        $allowedSorts = [
            'title' => 'title',
            'type' => 'type',
            'created_at' => 'created_at',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(9);
    }

    public function badgeColor(string $type): string
    {
        return match ($type) {
            'warning' => 'amber',
            'urgent' => 'red',
            'maintenance' => 'sky',
            default => 'zinc',
        };
    }
};
