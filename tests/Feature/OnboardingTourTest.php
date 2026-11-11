<?php

use App\Enums\Role;
use App\Livewire\OnboardingTour;
use App\Models\User;
use App\Support\OnboardingTours;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function onboardingStudent(): User
{
    PermissionRole::findOrCreate(Role::STUDENT->value, 'web');

    $user = User::factory()->create();
    $user->assignRole(Role::STUDENT->value);

    return $user;
}

test('a new user sees onboarding on their dashboard and can start without completing it', function () {
    $user = onboardingStudent();

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Welcome to DutyTrack!');

    Livewire::actingAs($user)
        ->test(OnboardingTour::class)
        ->call('start')
        ->assertSet('mode', 'tour')
        ->assertSet('step', 0)
        ->assertSee('Your student workspace');

    expect($user->fresh()->has_completed_onboarding)->toBeFalse();
});

test('tour steps move back and forward and finish persists only the authenticated user', function () {
    $user = onboardingStudent();
    $other = onboardingStudent();

    $tour = Livewire::actingAs($user)
        ->test(OnboardingTour::class)
        ->call('start')
        ->call('next')
        ->assertSet('step', 1)
        ->call('previous')
        ->assertSet('step', 0);

    for ($step = 0; $step < count(OnboardingTours::for($user)) - 1; $step++) {
        $tour->call('next');
    }

    $tour->assertSet('step', count(OnboardingTours::for($user)) - 1)
        ->call('next')
        ->assertSet('mode', 'closed');

    expect($user->fresh()->has_completed_onboarding)->toBeTrue()
        ->and($other->fresh()->has_completed_onboarding)->toBeFalse();
});

test('skip and maybe later persist completion and refresh respects the saved value', function () {
    $user = onboardingStudent();

    Livewire::actingAs($user)->test(OnboardingTour::class)->call('dismiss');

    expect($user->fresh()->has_completed_onboarding)->toBeTrue();

    $this->actingAs($user)->get(route('student.dashboard'))
        ->assertOk()
        ->assertDontSee('Welcome to DutyTrack!');
});

test('help resets onboarding and shows the tour again on the dashboard', function () {
    $user = onboardingStudent();
    $user->forceFill(['has_completed_onboarding' => true])->save();

    Livewire::actingAs($user)
        ->test(OnboardingTour::class)
        ->call('replay')
        ->assertRedirect(route('student.dashboard'));

    expect($user->fresh()->has_completed_onboarding)->toBeFalse();

    $this->actingAs($user->fresh())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Welcome to DutyTrack!')
        ->assertSee('Help');
});

test('each supported role gets only its own navigation walkthrough', function () {
    $expected = [
        Role::OJTCOORDINATOR->value => ['nav-student-management', 'nav-requirements', 'nav-attendance', 'nav-announcements', 'nav-audit-logs'],
        Role::SUPERVISOR->value => ['nav-assigned-students', 'nav-tasks', 'nav-reports', 'nav-evaluations'],
        Role::STUDENT->value => ['nav-requirements', 'nav-attendance', 'nav-evaluations', 'nav-reports', 'nav-tasks', 'nav-notes', 'nav-pomodoro'],
    ];

    foreach (array_keys($expected) as $role) {
        PermissionRole::findOrCreate($role, 'web');
    }

    foreach ($expected as $role => $targets) {
        $user = User::factory()->create();
        $user->assignRole($role);
        $tourTargets = array_column(OnboardingTours::for($user), 'target');

        expect($tourTargets)->toContain(...$targets);

        $dashboard = match ($role) {
            Role::OJTCOORDINATOR->value => 'coordinator.dashboard',
            Role::SUPERVISOR->value => 'supervisor.dashboard',
            default => 'student.dashboard',
        };
        $response = $this->actingAs($user)->get(route($dashboard))->assertOk()->assertSee('Help');

        foreach ($tourTargets as $target) {
            $response->assertSee('data-tour="'.$target.'"', false);
        }

        foreach (array_diff(['nav-students', 'nav-audit-logs', 'nav-requirements', 'nav-assigned-students', 'nav-pomodoro'], $targets) as $restrictedTarget) {
            expect($tourTargets)->not->toContain($restrictedTarget);
        }
    }
});
