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
        ['label' => 'Documents', 'href' => '#'],
        ['label' => 'Resume Builder'],
    ];

    public string $title = 'Resume Builder';

    public string $description = 'Create, edit, and download professional resumes for your OJT applications.';

    public ?int $deletingId = null;

    protected function ownResume(int $resumeId): Resume
    {
        return Auth::user()->resumes()->findOrFail($resumeId);
    }

    protected function atLimit(): bool
    {
        if (Auth::user()->resumes()->count() < Resume::MAX_PER_STUDENT) {
            return false;
        }

        Flux::toast(variant: 'danger', text: 'You can only have up to '.Resume::MAX_PER_STUDENT.' resumes. Delete one to make room.');

        return true;
    }

    public function create(): void
    {
        if ($this->atLimit()) {
            return;
        }

        $resume = Auth::user()->resumes()->create([
            'title' => 'My OJT Resume',
            'template' => ResumeTemplate::Earist,
            'data' => Resume::prefilledData(Auth::user()),
        ]);

        $this->redirectRoute('student.resumes.edit', $resume, navigate: true);
    }

    public function duplicate(int $resumeId): void
    {
        $resume = $this->ownResume($resumeId);

        if ($this->atLimit()) {
            return;
        }

        $resume->duplicate();
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
