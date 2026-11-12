<?php

namespace App\Livewire\Concerns;

use App\Models\User;
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

    /**
     * Last name of the user referenced by $userColumn; legacy users without split names fall back to the stored name.
     */
    protected function lastNameSubquery(string $userColumn): Builder
    {
        return User::withTrashed()->selectRaw("coalesce(nullif(last_name, ''), name)")->whereColumn('users.id', $userColumn);
    }

    /**
     * Default table order: last name A-Z, then first name.
     */
    protected function orderByLastName(Builder $query, string $userColumn): Builder
    {
        return $query
            ->orderBy($this->lastNameSubquery($userColumn))
            ->orderBy(User::withTrashed()->select('first_name')->whereColumn('users.id', $userColumn));
    }
}
