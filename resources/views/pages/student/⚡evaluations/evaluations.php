<?php

use App\Models\Evaluation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Evaluations')] class extends Component
{
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Student Workspace', 'href' => '#'],
        ['label' => 'Evaluations'],
    ];

    public string $title = 'Evaluations';

    public string $description = 'View submitted practicum evaluations and download the official form.';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->evaluations);
    }

    public function evaluatorLabel(string $type): string
    {
        return match ($type) {
            'professor' => 'Professor',
            'company_supervisor' => 'Company Supervisor',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    #[Computed]
    public function evaluations(): LengthAwarePaginator
    {
        return Evaluation::query()
            ->with(['assignment', 'evaluator'])
            ->where('student_id', Auth::id())
            ->where('status', 'submitted')
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($nested) use ($search) {
                    $nested->where('cooperating_program', 'like', $search)
                        ->orWhere('remarks', 'like', $search)
                        ->orWhereHas('evaluator', function ($evaluator) use ($search) {
                            $evaluator->where('name', 'like', $search)
                                ->orWhere('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search);
                        });
                });
            })
            ->latest('evaluation_date')
            ->latest('created_at')
            ->paginate(10);
    }
};
?>
