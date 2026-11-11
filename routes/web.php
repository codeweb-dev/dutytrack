<?php

use App\Models\AttendanceSubmission;
use App\Models\Evaluation;
use App\Models\Resume;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Support\AccomplishmentReportDocument;
use App\Support\CertificateDocument;
use App\Support\DtrDocument;
use App\Support\EvaluationDocument;
use App\Support\ResumeDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [\App\Http\Controllers\PwaController::class, 'worker'])->name('pwa.worker');

Route::view('/', 'welcome')->name('home');

// Prefills sign-up from the student masterlist; throttled so the list can't be scraped quickly.
Route::get('/register/lookup', fn () => ($photo = \App\Actions\Fortify\CreateNewUser::defaultPhoto((string) Request::query('student_id')))
        ? [...config('masterlist')[strtoupper(trim((string) Request::query('student_id')))], 'photo_url' => asset('student/'.basename($photo))]
        : abort(404))
    ->middleware(['guest', 'throttle:30,1'])
    ->name('register.lookup');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('ojt_coordinator') => redirect()->route('coordinator.dashboard'),
            $user->hasRole('supervisor') => redirect()->route('supervisor.dashboard'),
            $user->hasRole('student') => redirect()->route('student.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::livewire('/announcements', 'pages::announcement')->name('announcements');

    Route::get('/reports/{submission}/photos/{index}', function (AttendanceSubmission $submission, int $index) {
        $user = Auth::user();
        $allowed = ($user->hasRole('student') && (int) $submission->student_id === (int) $user->id)
            || ($user->hasRole('ojt_coordinator') && $submission->student()->where('coordinator_id', $user->id)->exists())
            || ($user->hasRole('supervisor') && $submission->assignment()->where('company_supervisor_id', $user->id)->exists());
        $path = $submission->photo_paths[$index] ?? null;

        abort_unless($allowed && is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    })->whereNumber('index')->name('reports.photo');
});

Route::group(['middleware' => ['auth', 'verified', 'role:ojt_coordinator']], function () {
    Route::livewire('/coordinator/dashboard', 'pages::coordinator.dashboard')->name('coordinator.dashboard');

    Route::livewire('/coordinator/student-management', 'pages::coordinator.student-management')->name('coordinator.student-management');
    Route::livewire('/coordinator/supervisor-management', 'pages::coordinator.supervisor-management')->name('coordinator.supervisor-management');
    Route::livewire('/coordinator/ojt-assignments', 'pages::coordinator.ojt-assignments')->name('coordinator.ojt-assignments');
    Route::livewire('/coordinator/requirements', 'pages::coordinator.requirements')->name('coordinator.requirements');
    Route::livewire('/coordinator/evaluations', 'pages::coordinator.evaluations')->name('coordinator.evaluations');
    Route::get('/coordinator/evaluations/{evaluation}/download', function (Evaluation $evaluation) {
        abort_unless(
            $evaluation->evaluator_type === 'company_supervisor'
                && $evaluation->student()->where('coordinator_id', Auth::id())->exists(),
            404,
        );

        return EvaluationDocument::download($evaluation);
    })->name('coordinator.evaluations.download');
    Route::get('/coordinator/evaluations/{evaluation}/view', function (Evaluation $evaluation) {
        abort_unless(
            $evaluation->evaluator_type === 'company_supervisor'
                && $evaluation->student()->where('coordinator_id', Auth::id())->exists(),
            404,
        );

        return EvaluationDocument::inline($evaluation);
    })->name('coordinator.evaluations.view');
    Route::get('/coordinator/requirements/{requirement}/file', function (StudentRequirement $requirement) {
        $student = $requirement->student()
            ->where('coordinator_id', Auth::id())
            ->with('studentProfile')
            ->firstOrFail();

        abort_unless($requirement->file_path && Storage::disk('local')->exists($requirement->file_path), 404);

        return view('pages.coordinator.requirement-file-preview', [
            'requirement' => $requirement,
            'student' => $student,
            'extension' => strtolower(pathinfo($requirement->file_path, PATHINFO_EXTENSION)),
            'fileUrl' => route('coordinator.requirements.file.content', $requirement),
        ]);
    })->name('coordinator.requirements.file');
    Route::get('/coordinator/requirements/{requirement}/file/content', function (StudentRequirement $requirement) {
        abort_unless($requirement->student()->where('coordinator_id', Auth::id())->exists(), 404);
        abort_unless($requirement->file_path && Storage::disk('local')->exists($requirement->file_path), 404);

        return Storage::disk('local')->response($requirement->file_path);
    })->name('coordinator.requirements.file.content');
    Route::livewire('/coordinator/certificates', 'pages::coordinator.certificates')->name('coordinator.certificates');
    Route::get('/coordinator/certificates/{student}/view.pdf', function (User $student) {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 404);

        return CertificateDocument::inline($student, requireCompletion: false);
    })->name('coordinator.certificates.view');
    Route::livewire('/coordinator/attendance', 'pages::coordinator.attendance')->name('coordinator.attendance');
    Route::livewire('/coordinator/messages', 'pages::messages')->name('coordinator.messages');

    // System Management
    Route::livewire('/coordinator/announcements', 'pages::coordinator.announcements')->name('coordinator.announcements');
    Route::livewire('/coordinator/audit-logs', 'pages::coordinator.audit-logs')->name('coordinator.audit-logs');
    Route::livewire('/coordinator/archive', 'pages::coordinator.archive')->name('coordinator.archive');
});

Route::group(['middleware' => ['auth', 'verified', 'role:supervisor']], function () {
    Route::livewire('/supervisor/dashboard', 'pages::supervisor.dashboard')->name('supervisor.dashboard');
    Route::livewire('/supervisor/assigned-students', 'pages::supervisor.assigned-students')->name('supervisor.assigned-students');
    Route::livewire('/supervisor/attendance', 'pages::supervisor.attendance')->name('supervisor.attendance');
    Route::livewire('/supervisor/evaluations', 'pages::supervisor.evaluations')->name('supervisor.evaluations');
    Route::livewire('/supervisor/reports-submissions', 'pages::supervisor.reports-submissions')->name('supervisor.reports-submissions');
    Route::livewire('/supervisor/tasks', 'pages::supervisor.tasks')->name('supervisor.tasks');
    Route::livewire('/supervisor/messages', 'pages::messages')->name('supervisor.messages');

    Route::get('/supervisor/evaluations/{evaluation}/download', function (Evaluation $evaluation) {
        abort_unless($evaluation->evaluator_id === Auth::id() && $evaluation->evaluator_type === 'company_supervisor', 403);

        return EvaluationDocument::download($evaluation);
    })->name('supervisor.evaluations.download');
    Route::get('/supervisor/evaluations/{evaluation}/view', function (Evaluation $evaluation) {
        abort_unless($evaluation->evaluator_id === Auth::id() && $evaluation->evaluator_type === 'company_supervisor', 403);

        return EvaluationDocument::inline($evaluation);
    })->name('supervisor.evaluations.view');
});

Route::group(['middleware' => ['auth', 'verified', 'role:student']], function () {
    Route::livewire('/student/dashboard', 'pages::student.dashboard')->name('student.dashboard');
    Route::livewire('/student/requirements', 'pages::student.requirements')->name('student.requirements');
    Route::livewire('/student/requirements/narrative_report', 'pages::student.narrative-report')->name('student.requirements.narrative');
    Route::livewire('/student/requirements/{key}', 'pages::student.requirement-form')
        ->whereIn('key', array_keys(\App\Support\RequirementForm::FORMS))
        ->name('student.requirements.form');
    Route::livewire('/student/attendance', 'pages::student.attendance')->name('student.attendance');
    Route::get('/student/attendance/dtr', fn () => DtrDocument::download(Auth::user(), Request::query('month')))->name('student.attendance.dtr');
    Route::get('/student/attendance/dtr/view', fn () => DtrDocument::inline(Auth::user(), Request::query('month')))->name('student.attendance.dtr.view');
    Route::livewire('/student/evaluations', 'pages::student.evaluations')->name('student.evaluations');
    Route::livewire('/student/reports-submissions', 'pages::student.reports-submissions')->name('student.reports-submissions');
    Route::livewire('/student/tasks', 'pages::student.tasks')->name('student.tasks');
    Route::livewire('/student/notes', 'pages::student.notes')->name('student.notes');
    Route::livewire('/student/messages', 'pages::messages')->name('student.messages');
    Route::livewire('/student/pomodoro', 'pages::student.pomodoro')->name('student.pomodoro');
    Route::livewire('/student/resumes', 'pages::student.resumes')->name('student.resumes');
    Route::livewire('/student/resumes/{resume}', 'pages::student.resume-builder')->name('student.resumes.edit');

    Route::get('/student/resumes/{resume}/preview.pdf', function (Resume $resume) {
        abort_unless((int) $resume->student_id === (int) Auth::id(), 404);

        return ResumeDocument::inline($resume);
    })->name('student.resumes.preview');

    Route::get('/student/resumes/{resume}/download', function (Resume $resume) {
        abort_unless((int) $resume->student_id === (int) Auth::id(), 404);

        return ResumeDocument::download($resume);
    })->name('student.resumes.download');

    Route::get('/student/resumes/{resume}/images/{type}', function (Resume $resume, string $type) {
        $path = $resume->imagePath($type);

        abort_unless((int) $resume->student_id === (int) Auth::id() && $path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, headers: ['Cache-Control' => 'private, max-age=31536000']);
    })->whereIn('type', Resume::IMAGES)->name('student.resumes.image');

    Route::livewire('/student/certificate', 'pages::student.certificate')->name('student.certificate');
    Route::get('/student/certificate/view.pdf', fn () => CertificateDocument::inline(Auth::user()))->name('student.certificate.view');
    Route::get('/student/certificate/download', fn () => CertificateDocument::download(Auth::user()))->name('student.certificate.download');

    Route::get('/student/requirements/{requirement}/file', function (StudentRequirement $requirement) {
        abort_unless((int) $requirement->student_id === (int) Auth::id(), 404);
        abort_unless($requirement->file_path && Storage::disk('local')->exists($requirement->file_path), 404);

        return Storage::disk('local')->response($requirement->file_path);
    })->name('student.requirements.file');

    Route::get('/student/evaluations/{evaluation}/download', function (Evaluation $evaluation) {
        abort_unless($evaluation->student_id === Auth::id() && $evaluation->status === 'reviewed', 403);

        return EvaluationDocument::download($evaluation);
    })->name('student.evaluations.download');

    Route::get('/student/evaluations/{evaluation}/view', function (Evaluation $evaluation) {
        abort_unless($evaluation->student_id === Auth::id() && $evaluation->status === 'reviewed', 403);

        return EvaluationDocument::inline($evaluation);
    })->name('student.evaluations.view');

    Route::get('/student/reports-submissions/download', function () {
        return AccomplishmentReportDocument::download(
            Auth::user(),
            Request::query('from'),
            Request::query('to'),
        );
    })->name('student.reports-submissions.download');
});

require __DIR__.'/settings.php';
