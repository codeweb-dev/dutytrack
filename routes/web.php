<?php

use App\Models\Evaluation;
use App\Models\StudentRequirement;
use App\Support\AccomplishmentReportDocument;
use App\Support\EvaluationDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/sw.js', [\App\Http\Controllers\PwaController::class, 'worker'])->name('pwa.worker');

Route::view('/', 'welcome')->name('home');

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
});

Route::group(['middleware' => ['role:ojt_coordinator']], function () {
    Route::livewire('/coordinator/dashboard', 'pages::coordinator.dashboard')->name('coordinator.dashboard');

    Route::livewire('/coordinator/student-management', 'pages::coordinator.student-management')->name('coordinator.student-management');
    Route::livewire('/coordinator/supervisor-management', 'pages::coordinator.supervisor-management')->name('coordinator.supervisor-management');
    Route::livewire('/coordinator/ojt-assignments', 'pages::coordinator.ojt-assignments')->name('coordinator.ojt-assignments');
    Route::livewire('/coordinator/requirements', 'pages::coordinator.requirements')->name('coordinator.requirements');
    Route::get('/coordinator/requirements/{requirement}/file', function (StudentRequirement $requirement) {
        $student = $requirement->student()
            ->where('coordinator_id', Auth::id())
            ->with('studentProfile')
            ->firstOrFail();

        abort_unless($requirement->file_path && Storage::disk('public')->exists($requirement->file_path), 404);

        return view('pages.coordinator.requirement-file-preview', [
            'requirement' => $requirement,
            'student' => $student,
            'extension' => strtolower(pathinfo($requirement->file_path, PATHINFO_EXTENSION)),
            'fileUrl' => route('coordinator.requirements.file.content', $requirement),
        ]);
    })->name('coordinator.requirements.file');
    Route::get('/coordinator/requirements/{requirement}/file/content', function (StudentRequirement $requirement) {
        abort_unless($requirement->student()->where('coordinator_id', Auth::id())->exists(), 404);
        abort_unless($requirement->file_path && Storage::disk('public')->exists($requirement->file_path), 404);

        return Storage::disk('public')->response($requirement->file_path);
    })->name('coordinator.requirements.file.content');
    Route::livewire('/coordinator/attendance', 'pages::coordinator.attendance')->name('coordinator.attendance');
    Route::livewire('/coordinator/messages', 'pages::messages')->name('coordinator.messages');

    // System Management
    Route::livewire('/coordinator/announcements', 'pages::coordinator.announcements')->name('coordinator.announcements');
    Route::livewire('/coordinator/audit-logs', 'pages::coordinator.audit-logs')->name('coordinator.audit-logs');
    Route::livewire('/coordinator/archive', 'pages::coordinator.archive')->name('coordinator.archive');
});

Route::group(['middleware' => ['role:supervisor']], function () {
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
});

Route::group(['middleware' => ['role:student']], function () {
    Route::livewire('/student/dashboard', 'pages::student.dashboard')->name('student.dashboard');
    Route::livewire('/student/attendance', 'pages::student.attendance')->name('student.attendance');
    Route::redirect('/student/evaluations', '/student/dashboard#evaluations')->name('student.evaluations');
    Route::livewire('/student/reports-submissions', 'pages::student.reports-submissions')->name('student.reports-submissions');
    Route::livewire('/student/tasks', 'pages::student.tasks')->name('student.tasks');
    Route::livewire('/student/notes', 'pages::student.notes')->name('student.notes');
    Route::livewire('/student/messages', 'pages::messages')->name('student.messages');
    Route::livewire('/student/pomodoro', 'pages::student.pomodoro')->name('student.pomodoro');

    Route::get('/student/evaluations/{evaluation}/download', function (Evaluation $evaluation) {
        abort_unless($evaluation->student_id === Auth::id(), 403);

        return EvaluationDocument::download($evaluation);
    })->name('student.evaluations.download');

    Route::get('/student/reports-submissions/download', function () {
        return AccomplishmentReportDocument::download(
            Auth::user(),
            Request::query('from'),
            Request::query('to'),
        );
    })->name('student.reports-submissions.download');
});

require __DIR__.'/settings.php';
