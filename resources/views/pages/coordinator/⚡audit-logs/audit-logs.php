<?php

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit Logs')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'System Management', 'href' => '#'],
        ['label' => 'Audit Logs'],
    ];

    public string $title = 'Audit Logs';
    public string $description = 'View and analyze system audit logs for security and compliance purposes.';

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $search = '';
    public string $module = '';
    public string $action = '';

    public function updatedSortBy(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'module', 'action', 'sortBy', 'sortDirection');

        $this->resetPage();
        unset($this->auditLogs);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    public function updatedModule(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    public function updatedAction(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    #[Computed]
    public function modules(): array
    {
        return AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->filter()
            ->values()
            ->all();
    }

    #[Computed]
    public function actions(): array
    {
        return AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->filter()
            ->values()
            ->all();
    }

    #[Computed]
    public function auditLogs(): LengthAwarePaginator
    {
        $query = AuditLog::query();

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('actor_name', 'like', $search)
                    ->orWhere('actor_role', 'like', $search)
                    ->orWhere('module', 'like', $search)
                    ->orWhere('action', 'like', $search)
                    ->orWhere('target_label', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if ($this->module !== '') {
            $query->where('module', $this->module);
        }

        if ($this->action !== '') {
            $query->where('action', $this->action);
        }

        $allowedSorts = [
            'created_at' => 'created_at',
            'actor_name' => 'actor_name',
            'module' => 'module',
            'action' => 'action',
            'target_label' => 'target_label',
        ];

        $sortColumn = $allowedSorts[$this->sortBy] ?? 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10);
    }

    #[On('audit-log-deleted')]
    public function refreshAuditLogs(): void
    {
        $this->resetPage();
        unset($this->auditLogs);
    }

    public function badgeColor(string $action): string
    {
        return match ($action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'deactivated' => 'zinc',
            'activated' => 'green',
            'reset_password' => 'amber',
            'assigned' => 'sky',
            'reassigned' => 'purple',
            'published' => 'green',
            'archived' => 'zinc',
            default => 'zinc',
        };
    }
};
