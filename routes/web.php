<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::group(['middleware' => ['role:admin']], function () {
    // User Management
    Route::livewire('/admin/students', 'pages::admin.students')->name('admin.students');    
    Route::livewire('/admin/coordinators', 'pages::admin.coordinators')->name('admin.coordinators');
    Route::livewire('/admin/professors', 'pages::admin.professors')->name('admin.professors');
    Route::livewire('/admin/supervisors', 'pages::admin.supervisors')->name('admin.supervisors');

    // Monitoring
    Route::livewire('/admin/attendance', 'pages::admin.attendance')->name('admin.attendance');
    Route::livewire('/admin/duty-hours', 'pages::admin.duty-hours')->name('admin.duty-hours');
    Route::livewire('/admin/ojt-assignments', 'pages::admin.ojt-assignments')->name('admin.ojt-assignments');

    // Reports
    Route::livewire('/admin/attendance-reports', 'pages::admin.attendance-reports')->name('admin.attendance-reports');
    Route::livewire('/admin/duty-hours-reports', 'pages::admin.duty-hours-reports')->name('admin.duty-hours-reports');
    Route::livewire('/admin/deployment-reports', 'pages::admin.deployment-reports')->name('admin.deployment-reports');
    Route::livewire('/admin/evaluation-reports', 'pages::admin.evaluation-reports')->name('admin.evaluation-reports');

    // System Management
    Route::livewire('/admin/announcements', 'pages::admin.announcements')->name('admin.announcements');
    Route::livewire('/admin/audit-logs', 'pages::admin.audit-logs')->name('admin.audit-logs');
});

require __DIR__.'/settings.php';
