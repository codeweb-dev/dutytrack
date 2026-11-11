<?php

use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Evaluation')] class extends Component
{
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'My Evaluation']];

    public string $title = 'My Evaluation';

    public string $description = 'Review your submitted practicum evaluation and download the official form.';

    #[Computed]
    public function evaluation(): ?Evaluation
    {
        return Evaluation::query()
            ->with('evaluator')
            ->where('student_id', Auth::id())
            ->where('status', 'submitted')
            ->latest('evaluation_date')
            ->latest('created_at')
            ->first();
    }
};
?>

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <flux:card>
        <flux:heading size="lg">My Evaluation Records</flux:heading>

        @if ($evaluation = $this->evaluation)
            <div class="mt-6 overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Evaluator</flux:table.column>
                        <flux:table.column>Inclusive Dates</flux:table.column>
                        <flux:table.column>Cooperating Program</flux:table.column>
                        <flux:table.column>Rating</flux:table.column>
                        <flux:table.column>Comments</flux:table.column>
                        <flux:table.column>Download</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        <flux:table.row :key="$evaluation->id">
                            <flux:table.cell>
                                {{ $evaluation->evaluator?->display_name ?? 'Supervisor' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $evaluation->inclusive_from?->format('M d, Y') ?? '' }}
                                @if ($evaluation->inclusive_to)
                                    to {{ $evaluation->inclusive_to->format('M d, Y') }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $evaluation->cooperating_program ?? '' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">
                                        {{ number_format((float) $evaluation->rating, 1) }}
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                        {{ $evaluation->evaluation_date?->format('M d, Y') }}
                                    </span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ \Illuminate\Support\Str::limit($evaluation->remarks ?? '', 80) }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-down-tray"
                                    href="{{ route('student.evaluations.download', $evaluation) }}" target="_blank">
                                    Docs
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                </flux:table>
            </div>
        @else
            <flux:text class="mt-6">Submitted practicum evaluations will appear here.</flux:text>
        @endif
    </flux:card>
</div>
