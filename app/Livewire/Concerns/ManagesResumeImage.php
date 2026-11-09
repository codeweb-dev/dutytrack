<?php

namespace App\Livewire\Concerns;

use App\Models\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

/**
 * Upload/remove one private image (ID photo or signature) of the student's own resume.
 * Using components set $type and implement uploadRules().
 */
trait ManagesResumeImage
{
    use WithFileUploads;

    #[Locked]
    public int $resumeId;

    public $upload = null;

    abstract protected function uploadRules(): array;

    public function mount(int $resumeId): void
    {
        $this->resumeId = $resumeId;
        $this->resume();
    }

    protected function resume(): Resume
    {
        return Auth::user()->resumes()->findOrFail($this->resumeId);
    }

    public function saveUpload(): bool
    {
        try {
            $this->validate(['upload' => $this->uploadRules()]);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->reset('upload');

            return false;
        }

        $this->resume()->storeImage($this->type, $this->upload);
        $this->reset('upload');
        $this->dispatch('resume-saved');

        return true;
    }

    public function removeImage(): void
    {
        $this->resume()->deleteImage($this->type);
        $this->dispatch('resume-saved');
    }

    public function imageUrl(): ?string
    {
        $path = $this->resume()->imagePath($this->type);

        // Stored filenames are random, so the hash changes whenever the image does.
        return $path ? route('student.resumes.image', [$this->resumeId, $this->type]).'?v='.md5($path) : null;
    }
}
