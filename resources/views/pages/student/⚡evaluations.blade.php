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

    public string $description = 'View and download your practicum evaluation once your coordinator approves it.';

    #[Computed]
    public function evaluation(): ?Evaluation
    {
        return Evaluation::query()
            ->with('evaluator')
            ->where('student_id', Auth::id())
            ->where('status', 'reviewed')
            ->latest('evaluation_date')
            ->latest('created_at')
            ->first();
    }
};
?>

<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    @if ($evaluation = $this->evaluation)
        <iframe src="{{ route('student.evaluations.view', $evaluation) }}" title="Practicum Student Evaluation Form"
            class="mx-auto aspect-[17/22] w-full max-w-4xl rounded-xl border border-zinc-200 bg-white dark:border-zinc-700"></iframe>

        <div class="flex justify-center">
            <flux:button variant="primary" icon="arrow-down-tray" :href="route('student.evaluations.download', $evaluation)">Download Evaluation</flux:button>
        </div>
    @else
        <flux:card class="flex flex-col items-center px-6 py-12 text-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.document-text class="size-8" />
            </div>
            <flux:heading size="lg" class="mt-6">No evaluation yet</flux:heading>
            <flux:text class="mt-2 max-w-md">Your practicum evaluation will appear here once your coordinator approves it.</flux:text>
        </flux:card>
    @endif
</div>
