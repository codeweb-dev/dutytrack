<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::group(['middleware' => ['role:admin']], function () {
    Route::livewire('/admin/students', 'pages::admin.students')->name('admin.students');    
    Route::livewire('/admin/supervisors', 'pages::admin.supervisors')->name('admin.supervisors');
    Route::livewire('/admin/partners', 'pages::admin.partners')->name('admin.partners');
    Route::livewire('/admin/attendance', 'pages::admin.attendance')->name('admin.attendance');
    Route::livewire('/admin/duty-hours', 'pages::admin.duty-hours')->name('admin.duty-hours');
    Route::livewire('/admin/productivity', 'pages::admin.productivity')->name('admin.productivity');
    Route::livewire('/admin/reports', 'pages::admin.reports')->name('admin.reports');
    Route::livewire('/admin/evaluation', 'pages::admin.evaluation')->name('admin.evaluation');
    Route::livewire('/admin/announcements', 'pages::admin.announcements')->name('admin.announcements');
    Route::livewire('/admin/audit-logs', 'pages::admin.audit-logs')->name('admin.audit-logs');
    Route::livewire('/admin/settings', 'pages::admin.settings')->name('admin.settings');
});

require __DIR__.'/settings.php';
