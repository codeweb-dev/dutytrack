<?php

use App\Enums\Role as AppRole;
use App\Models\StudentRequirement;
use App\Models\User;
use Database\Seeders\CoordinatorSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

beforeEach(function () {
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    Storage::fake('public');
});

function studentSignup(array $overrides = []): array
{
    return array_merge([
        'student_id' => '2026-0001',
        'birthdate' => '2004-01-01',
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
        'photo' => UploadedFile::fake()->image('me.jpg'),
    ], $overrides);
}

test('profile photo is required to sign up', function () {
    $this->post(route('register.store'), studentSignup(['photo' => null]))
        ->assertSessionHasErrors('photo');
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('students can sign up and are routed to the coordinator of their program', function () {
    Storage::fake('public');

    $this->seed(CoordinatorSeeder::class);

    $response = $this->post(route('register.store'), studentSignup([
        'photo' => UploadedFile::fake()->image('me.jpg'),
    ]));

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();

    $student = User::query()->with('studentProfile')->where('email', 'jamie@example.com')->first();

    expect($student->hasRole(AppRole::STUDENT->value))->toBeTrue();
    expect($student->name)->toBe('Jamie Lopez Student');
    expect($student->status)->toBe('pending');
    expect($student->coordinator->email)->toBe('rowe@dutytrack.test');
    expect($student->studentProfile->ojt_required_hours)->toBe(500);
    expect($student->studentProfile->guardian_name)->toBe('Maria Student');
    expect($student->studentRequirements()->count())->toBe(count(StudentRequirement::definitions()));
    Storage::disk('public')->assertExists($student->profile_photo_path);
});

test('sign up rejects an existing email or student id', function () {
    $this->post(route('register.store'), studentSignup());

    $this->post(route('register.store'), studentSignup(['email' => 'other@example.com']))
        ->assertSessionHasErrors('student_id');

    $this->post(route('register.store'), studentSignup(['student_id' => '2026-0002']))
        ->assertSessionHasErrors('email');
});

test('pending students cannot log in until their coordinator approves them', function () {
    $this->seed(CoordinatorSeeder::class);
    $this->post(route('register.store'), studentSignup(['course' => 'BSCS']));

    $this->post(route('login.store'), ['email' => 'jamie@example.com', 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    $dumpit = User::where('email', 'dumpit@dutytrack.test')->first();
    $student = User::where('email', 'jamie@example.com')->first();
    expect($student->coordinator_id)->toBe($dumpit->id);
    expect($student->studentProfile->ojt_required_hours)->toBe(300);

    $this->actingAs($dumpit);
    Livewire::test('pages::coordinator.student-management')->call('approve', $student->id);
    expect($student->fresh()->status)->toBe('active');
    auth()->logout();

    $this->post(route('login.store'), ['email' => 'jamie@example.com', 'password' => 'password'])
        ->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($student);
});

test('coordinators can reject a pending sign-up so the student can register again', function () {
    $this->seed(CoordinatorSeeder::class);
    $this->post(route('register.store'), studentSignup(['course' => 'BSCS']));
    $student = User::where('email', 'jamie@example.com')->first();

    $this->actingAs(User::where('email', 'dumpit@dutytrack.test')->first());
    Livewire::test('pages::coordinator.student-management')->call('reject', $student->id);
    auth()->logout();

    expect(User::find($student->id))->toBeNull();

    $this->post(route('register.store'), studentSignup(['course' => 'BSCS']))->assertSessionHasNoErrors();
});

test('student id lookup returns masterlist details for prefilling sign up', function () {
    $this->getJson(route('register.lookup', ['student_id' => ' 2316-02373c ']))
        ->assertOk()
        ->assertExactJson([
            'course' => 'BSCS',
            'last_name' => 'Labrague',
            'first_name' => 'Allen',
            'middle_name' => 'Ingraham',
            'extension_name' => null,
        ]);

    $this->getJson(route('register.lookup', ['student_id' => 'NOT-AN-ID']))->assertNotFound();
});
