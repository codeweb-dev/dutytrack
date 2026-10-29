<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WithSorting
{
    public string $sortBy = '';

    public string $sortDirection = 'asc';

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    /**
     * Order by the selected column; columns map a sort key to a column name or subquery.
     * Unknown keys are ignored so the caller's default ordering applies.
     */
    protected function applySorting(Builder $query, array $columns): Builder
    {
        if (isset($columns[$this->sortBy])) {
            $query->orderBy($columns[$this->sortBy], $this->sortDirection === 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }
}
