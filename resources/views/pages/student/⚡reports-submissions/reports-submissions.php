<?php

use App\Models\AttendanceLog;
use App\Models\AttendanceSubmission;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Accomplishment Report')] class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Accomplishment Report'],
    ];

    public string $title = 'Accomplishment Report';

    public string $description = 'Add daily accomplishments and photos after completing your attendance.';

    public ?int $editingAttendanceId = null;

    public string $accomplishment = '';

    public array $new_photos = [];

    public string $from_date = '';

    public string $to_date = '';

    public function updatedFromDate(): void
    {
        $this->resetPage();
        unset($this->completedAttendances);
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
        unset($this->completedAttendances);
    }

    public function openSubmissionModal(int $attendanceId): void
    {
        $attendance = $this->attendanceQuery()
            ->whereKey($attendanceId)
            ->firstOrFail();

        $this->editingAttendanceId = $attendance->id;
        $this->accomplishment = $attendance->submission?->accomplishment ?? '';
        $this->new_photos = [];
        $this->resetValidation();

        Flux::modal('student-report-submission')->show();
    }

    public function saveSubmission(): void
    {
        $this->validate([
            'editingAttendanceId' => ['required', 'integer'],
            'accomplishment' => ['nullable', 'string', 'max:5000'],
            'new_photos' => ['array', 'max:6'],
            'new_photos.*' => ['image', 'max:4096'],
        ]);

        $attendance = $this->attendanceQuery()
            ->whereKey($this->editingAttendanceId)
            ->firstOrFail();

        $submission = AttendanceSubmission::query()->firstOrNew([
            'attendance_log_id' => $attendance->id,
        ]);

        $existingPhotos = $submission->photo_paths ?? [];
        $uploadedPhotos = collect($this->new_photos)
            ->filter(fn ($photo): bool => $photo instanceof TemporaryUploadedFile)
            ->map(fn (TemporaryUploadedFile $photo): string => $photo->store('accomplishment-photos', 'public'))
            ->all();

        $submission->fill([
            'student_id' => $attendance->student_id,
            'ojt_assignment_id' => $attendance->ojt_assignment_id,
            'accomplishment' => trim($this->accomplishment) !== '' ? trim($this->accomplishment) : null,
            'photo_paths' => array_values(array_merge($existingPhotos, $uploadedPhotos)),
        ])->save();

        $this->reset('editingAttendanceId', 'accomplishment', 'new_photos');
        unset($this->completedAttendances);

        Flux::modal('student-report-submission')->close();
        Flux::toast(variant: 'success', heading: 'Submission saved.', text: 'Your accomplishment report entry has been updated.');
    }

    public function downloadUrl(): string
    {
        return route('student.reports-submissions.download', array_filter([
            'from' => $this->from_date,
            'to' => $this->to_date,
        ]));
    }

    protected function attendanceQuery()
    {
        return AttendanceLog::query()
            ->with('submission')
            ->where('student_id', Auth::id())
            ->whereNotNull('morning_time_in')
            ->whereNotNull('morning_time_out')
            ->whereNotNull('afternoon_time_in')
            ->whereNotNull('afternoon_time_out');
    }

    #[Computed]
    public function completedAttendances(): LengthAwarePaginator
    {
        return $this->attendanceQuery()
            ->when($this->from_date !== '', fn ($query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date !== '', fn ($query) => $query->whereDate('attendance_date', '<=', $this->to_date))
            ->latest('attendance_date')
            ->paginate(10);
    }
};
