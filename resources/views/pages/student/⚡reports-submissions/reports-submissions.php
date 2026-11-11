<?php

use App\Livewire\Concerns\WithSorting;
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
    use WithSorting;

    public array $breadcrumbs = [
        ['logo' => 'logo.png', 'href' => '/dashboard'],
        ['label' => 'Daily Logs', 'href' => '#'],
        ['label' => 'Accomplishment Report'],
    ];

    public string $title = 'Accomplishment Report';

    public string $description = 'Add daily accomplishments and photos after completing your attendance.';

    public ?int $editingAttendanceId = null;

    public string $accomplishment = '';

    public string $skills_learned = '';

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

    public function clearFilters(): void
    {
        $this->reset('from_date', 'to_date', 'sortBy', 'sortDirection');

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
        $this->skills_learned = $attendance->submission?->skills_learned ?? '';
        $this->new_photos = [];
        $this->resetValidation();

        Flux::modal('student-report-submission')->show();
    }

    public function saveSubmission(): void
    {
        $this->validate([
            'editingAttendanceId' => ['required', 'integer'],
            'accomplishment' => ['required', 'string', 'max:5000'],
            'skills_learned' => ['required', 'string', 'max:5000'],
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
        if (count($existingPhotos) + count($this->new_photos) > 6) {
            $this->addError('new_photos', 'A report can include up to six photos.');

            return;
        }

        if (count($existingPhotos) + count($this->new_photos) === 0) {
            $this->addError('new_photos', 'Please add at least one photo.');

            return;
        }

        $uploadedPhotos = collect($this->new_photos)
            ->filter(fn ($photo): bool => $photo instanceof TemporaryUploadedFile)
            ->map(fn (TemporaryUploadedFile $photo): string => $photo->store('accomplishment-photos/'.$this->editingAttendanceId, 'local'))
            ->all();

        $submission->fill([
            'student_id' => $attendance->student_id,
            'ojt_assignment_id' => $attendance->ojt_assignment_id,
            'accomplishment' => trim($this->accomplishment) !== '' ? trim($this->accomplishment) : null,
            'skills_learned' => trim($this->skills_learned) !== '' ? trim($this->skills_learned) : null,
            'photo_paths' => array_values(array_merge($existingPhotos, $uploadedPhotos)),
        ])->save();

        $this->reset('editingAttendanceId', 'accomplishment', 'skills_learned', 'new_photos');
        unset($this->completedAttendances);

        Flux::modal('student-report-submission')->close();
        Flux::toast(variant: 'success', heading: 'Report saved.', text: 'Your accomplishment report entry has been updated.');
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
        $query = $this->attendanceQuery()
            ->when($this->from_date !== '', fn ($query) => $query->whereDate('attendance_date', '>=', $this->from_date))
            ->when($this->to_date !== '', fn ($query) => $query->whereDate('attendance_date', '<=', $this->to_date));

        return $this->applySorting($query, [
            'date' => 'attendance_date',
            'hours' => 'hours_rendered',
        ])
            ->latest('attendance_date')
            ->paginate(10);
    }
};
