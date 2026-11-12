<?php

use App\Enums\Role as AppRole;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function createNarrativeStudent(): User
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    $student = User::factory()->student()->create();
    StudentRequirement::ensureForStudent($student);

    return $student;
}

test('removing a narrative image cannot delete another students file', function () {
    Storage::fake('local');

    $student = createNarrativeStudent();
    $otherStudent = createNarrativeStudent();
    $victimFile = 'student-requirements/'.$otherStudent->id.'/acceptance_letter.pdf';
    Storage::disk('local')->put($victimFile, 'pdf');

    $this->actingAs($student);

    Livewire::test('pages::student.narrative-report')
        ->set('data.org_chart', 'student-requirements/'.$student->id.'/narrative/../../'.$otherStudent->id.'/acceptance_letter.pdf')
        ->call('removeImage', 'org_chart');

    Storage::disk('local')->assertExists($victimFile);
});
