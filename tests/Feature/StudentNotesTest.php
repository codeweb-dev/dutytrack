<?php

use App\Enums\Role as AppRole;
use App\Models\StudentNote;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureStudentNotesRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createStudentNotesStudent(array $attributes = []): User
{
    ensureStudentNotesRolesExist();

    return User::factory()->student()->create($attributes);
}

test('students can create and edit notes', function () {
    $student = createStudentNotesStudent();

    $this->actingAs($student);

    Livewire::test('pages::student.notes')
        ->call('createNote')
        ->set('note_title', 'Daily OJT reminders')
        ->set('note_body', 'Prepare screenshots and write the accomplishment report.')
        ->call('saveCurrentNote')
        ->call('togglePin')
        ->assertSet('is_pinned', true)
        ->assertHasNoErrors();

    $note = StudentNote::query()->where('student_id', $student->id)->first();

    expect($note)->not->toBeNull();
    expect($note->title)->toBe('Daily OJT reminders');
    expect($note->body)->toBe('Prepare screenshots and write the accomplishment report.');
    expect($note->is_pinned)->toBeTrue();

    $this->get(route('student.notes'))
        ->assertOk()
        ->assertSee('Notes')
        ->assertSee('Daily OJT reminders')
        ->assertSee('Prepare screenshots');
});

test('students only see their own notes', function () {
    $student = createStudentNotesStudent(['name' => 'Notes Owner']);
    $otherStudent = createStudentNotesStudent(['name' => 'Other Notes Owner']);

    StudentNote::query()->create([
        'student_id' => $student->id,
        'title' => 'My private practicum note',
        'body' => 'This belongs to the logged in student.',
    ]);

    $otherNote = StudentNote::query()->create([
        'student_id' => $otherStudent->id,
        'title' => 'Another student note',
        'body' => 'This should stay hidden.',
    ]);

    $this->actingAs($student)
        ->get(route('student.notes'))
        ->assertOk()
        ->assertSee('My private practicum note')
        ->assertDontSee('Another student note');

    $this->actingAs($student);

    Livewire::test('pages::student.notes')
        ->call('selectNote', $otherNote->id)
        ->assertNotFound();
});

test('the archive button swaps the sidebar to archived notes', function () {
    $student = createStudentNotesStudent();
    StudentNote::query()->create(['student_id' => $student->id, 'title' => 'Still active', 'body' => '']);
    $archived = StudentNote::query()->create(['student_id' => $student->id, 'title' => 'Old reminder', 'body' => '']);
    $archived->delete();

    $this->actingAs($student);

    Livewire::test('pages::student.notes')
        ->assertSee('Still active')
        ->assertDontSee('Old reminder')
        ->set('showArchivedNotes', true)
        ->assertSee('Old reminder')
        ->assertDontSee('Still active')
        ->call('restoreNote', $archived->id)
        ->assertSet('showArchivedNotes', false)
        ->assertSee('Old reminder');
});
