<?php

use App\Enums\ResumeTemplate;
use App\Models\Resume;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Resume Builder')] class extends Component
{
    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Resume Builder'],
    ];

    public string $title = 'Resume Builder';

    public string $description = 'Create, edit, and download professional resumes for your OJT applications.';

    public ?int $deletingId = null;

    protected function ownResume(int $resumeId): Resume
    {
        return Auth::user()->resumes()->findOrFail($resumeId);
    }

    public function create(): void
    {
        $resume = Auth::user()->resumes()->create([
            'title' => 'My OJT Resume',
            'template' => ResumeTemplate::Earist,
            'data' => Resume::prefilledData(Auth::user()),
        ]);

        $this->redirectRoute('student.resumes.edit', $resume, navigate: true);
    }

    public function duplicate(int $resumeId): void
    {
        $this->ownResume($resumeId)->duplicate();
        unset($this->resumes);

        Flux::toast(variant: 'success', text: 'Resume duplicated.');
    }

    public function confirmDelete(int $resumeId): void
    {
        $this->deletingId = $this->ownResume($resumeId)->id;
        Flux::modal('delete-resume')->show();
    }

    public function delete(): void
    {
        $this->ownResume((int) $this->deletingId)->delete();
        $this->deletingId = null;
        unset($this->resumes);

        Flux::modal('delete-resume')->close();
        Flux::toast(variant: 'success', text: 'Resume deleted.');
    }

    #[Computed]
    public function resumes(): Collection
    {
        return Auth::user()->resumes()->latest('updated_at')->get();
    }
};
