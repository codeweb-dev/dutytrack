<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasRole('ojt_coordinator') => redirect()->route('coordinator.dashboard'),
            $user->hasRole('professor') => redirect()->route('professor.dashboard'),
            $user->hasRole('company_supervisor') => redirect()->route('supervisor.dashboard'),
            $user->hasRole('student') => redirect()->route('student.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::livewire('/announcements', 'pages::announcement')->name('announcements');
});

Route::group(['middleware' => ['role:admin']], function () {
    Route::livewire('/admin/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');

    // User Management
    Route::livewire('/admin/students', 'pages::admin.students')->name('admin.students');
    Route::livewire('/admin/coordinators', 'pages::admin.coordinators')->name('admin.coordinators');
    Route::livewire('/admin/professors', 'pages::admin.professors')->name('admin.professors');
    Route::livewire('/admin/supervisors', 'pages::admin.supervisors')->name('admin.supervisors');

    // Monitoring & reports
    Route::livewire('/admin/attendance', 'pages::admin.attendance')->name('admin.attendance');
    Route::livewire('/admin/duty-hours', 'pages::admin.duty-hours')->name('admin.duty-hours');
    Route::livewire('/admin/ojt-assignments', 'pages::admin.ojt-assignments')->name('admin.ojt-assignments');
    Route::livewire('/admin/evaluations', 'pages::admin.evaluations')->name('admin.evaluations');

    // System Management
    Route::livewire('/admin/announcements', 'pages::admin.announcements')->name('admin.announcements');
    Route::livewire('/admin/audit-logs', 'pages::admin.audit-logs')->name('admin.audit-logs');
});

Route::group(['middleware' => ['role:ojt_coordinator']], function () {
    Route::livewire('/coordinator/dashboard', 'pages::coordinator.dashboard')->name('coordinator.dashboard');

    Route::livewire('/coordinator/student-management', 'pages::coordinator.student-management')->name('coordinator.student-management');
    Route::livewire('/coordinator/ojt-assignments', 'pages::coordinator.ojt-assignments')->name('coordinator.ojt-assignments');
    Route::livewire('/coordinator/requirements', 'pages::coordinator.requirements')->name('coordinator.requirements');
    Route::livewire('/coordinator/attendance', 'pages::coordinator.attendance')->name('coordinator.attendance');
    Route::livewire('/coordinator/duty-hours', 'pages::coordinator.duty-hours')->name('coordinator.duty-hours');
});

Route::group(['middleware' => ['role:professor']], function () {
    Route::livewire('/professor/dashboard', 'pages::professor.dashboard')->name('professor.dashboard');
    Route::livewire('/professor/assigned-students', 'pages::professor.assigned-students')->name('professor.assigned-students');
    Route::livewire('/professor/duty-hours', 'pages::professor.duty-hours')->name('professor.duty-hours');
    Route::livewire('/professor/tasks', 'pages::professor.tasks')->name('professor.tasks');
    Route::livewire('/professor/messages', 'pages::messages')->name('professor.messages');
});

Route::group(['middleware' => ['role:company_supervisor']], function () {
    Route::livewire('/supervisor/dashboard', 'pages::supervisor.dashboard')->name('supervisor.dashboard');
    Route::livewire('/supervisor/assigned-students', 'pages::supervisor.assigned-students')->name('supervisor.assigned-students');
    Route::livewire('/supervisor/duty-hours', 'pages::supervisor.duty-hours')->name('supervisor.duty-hours');
    Route::livewire('/supervisor/tasks', 'pages::supervisor.tasks')->name('supervisor.tasks');
    Route::livewire('/supervisor/messages', 'pages::messages')->name('supervisor.messages');
});

Route::group(['middleware' => ['role:student']], function () {
    Route::livewire('/student/dashboard', 'pages::student.dashboard')->name('student.dashboard');
    Route::livewire('/student/attendance', 'pages::student.attendance')->name('student.attendance');
    Route::livewire('/student/duty-hours', 'pages::student.duty-hours')->name('student.duty-hours');
    Route::livewire('/student/tasks', 'pages::student.tasks')->name('student.tasks');
    Route::livewire('/student/notes', 'pages::student.notes')->name('student.notes');
    Route::livewire('/student/messages', 'pages::messages')->name('student.messages');
    Route::livewire('/student/pomodoro', 'pages::student.pomodoro')->name('student.pomodoro');
    Route::livewire('/student/pet', 'pages::student.pet-streak')->name('student.pet-streak');
});

require __DIR__.'/settings.php';
