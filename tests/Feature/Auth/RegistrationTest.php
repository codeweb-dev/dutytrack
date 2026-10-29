<?php

use App\Enums\Role as AppRole;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role as PermissionRole;

beforeEach(function () {
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
});

function studentSignup(array $overrides = []): array
{
    return array_merge([
        'student_id' => '2026-0001',
        'first_name' => 'Jamie',
        'middle_name' => 'Lopez',
        'last_name' => 'Student',
        'email' => 'jamie@example.com',
        'contact_number' => '9123456789',
        'course' => 'BSIT',
        'address' => '123 Main St',
        'guardian_name' => 'Maria Student',
        'guardian_address' => '123 Main St',
        'guardian_contact_number' => '9987654321',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('students can sign up and are routed to the coordinator of their program', function () {
    Storage::fake('public');

    $coordinator = User::factory()->create();
    $coordinator->assignRole(AppRole::OJTCOORDINATOR->value);
    $coordinator->coordinatorProfile()->create(['department' => 'Computer Studies', 'program' => 'Information Technology', 'position' => 'OJT Coordinator']);

    $response = $this->post(route('register.store'), studentSignup([
        'photo' => UploadedFile::fake()->image('me.jpg'),
    ]));

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $student = User::query()->with('studentProfile')->where('email', 'jamie@example.com')->first();

    expect($student->hasRole(AppRole::STUDENT->value))->toBeTrue();
    expect($student->name)->toBe('Jamie Lopez Student');
    expect($student->coordinator_id)->toBe($coordinator->id);
    expect($student->studentProfile->year_level)->toBe('4th Year');
    expect($student->studentProfile->guardian_name)->toBe('Maria Student');
    expect($student->studentRequirements()->count())->toBe(count(StudentRequirement::definitions()));
    Storage::disk('public')->assertExists($student->profile_photo_path);
});

test('sign up rejects an existing email or student id', function () {
    $this->post(route('register.store'), studentSignup());
    auth()->logout();

    $this->post(route('register.store'), studentSignup(['email' => 'other@example.com']))
        ->assertSessionHasErrors('student_id');

    $this->post(route('register.store'), studentSignup(['student_id' => '2026-0002']))
        ->assertSessionHasErrors('email');
});
