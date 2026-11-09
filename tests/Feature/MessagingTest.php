<?php

use App\Enums\Role as AppRole;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function ensureMessagingRolesExist(): void
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }
}

function createMessagingUser(AppRole $role, array $attributes = []): User
{
    ensureMessagingRolesExist();

    $user = User::factory()->create($attributes);
    $user->assignRole($role->value);

    if ($role === AppRole::STUDENT) {
        $user->studentProfile()->create([
            'student_id' => 'MSG-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            'course' => 'BSIT',
            'ojt_required_hours' => 600,
        ]);
    }

    return $user;
}

function createMessagingAssignment(User $student, ?User $mentor = null, ?User $supervisor = null): OjtAssignment
{
    return OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => $supervisor ? 'external' : 'internal',
        'company_supervisor_id' => $supervisor?->id ?? $mentor?->id,
    ]);
}

test('students can message only their assigned supervisor', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Message Student']);
    $mentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Assigned Mentor']);
    $supervisor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Assigned Supervisor']);
    $otherMentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Other Mentor']);

    createMessagingAssignment($student, $mentor);

    $this->actingAs($student)
        ->get(route('student.messages'))
        ->assertOk()
        ->assertSee('Assigned Mentor')
        ->assertDontSee('Assigned Supervisor')
        ->assertDontSee('Other Mentor');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $mentor->id)
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Good afternoon, mentor.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $student->id)
        ->where('body', 'Good afternoon, mentor.')
        ->exists())->toBeTrue();

});

test('students can message the coordinator who created them before an ojt assignment exists', function () {
    $coordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Student Coordinator']);
    $student = createMessagingUser(AppRole::STUDENT, [
        'name' => 'Coordinator Chat Student',
        'coordinator_id' => $coordinator->id,
    ]);

    $this->actingAs($student)
        ->get(route('student.messages'))
        ->assertOk()
        ->assertSee('Student Coordinator')
        ->assertSee('Coordinator');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $coordinator->id)
        ->where('participant_role', 'coordinator')
        ->firstOrFail();

    expect($conversation->ojt_assignment_id)->toBeNull();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Good afternoon, coordinator.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $student->id)
        ->where('body', 'Good afternoon, coordinator.')
        ->exists())->toBeTrue();

});

test('coordinators can message students they created only', function () {
    $coordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Messaging Coordinator']);
    $otherCoordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Other Messaging Coordinator']);
    $student = createMessagingUser(AppRole::STUDENT, [
        'name' => 'Owned Coordinator Student',
        'coordinator_id' => $coordinator->id,
    ]);
    $otherStudent = createMessagingUser(AppRole::STUDENT, [
        'name' => 'Other Coordinator Student',
        'coordinator_id' => $otherCoordinator->id,
    ]);

    ChatConversation::query()->create([
        'student_id' => $otherStudent->id,
        'participant_id' => $otherCoordinator->id,
        'participant_role' => 'coordinator',
    ]);

    $this->actingAs($coordinator)
        ->get(route('coordinator.messages'))
        ->assertOk()
        ->assertSee('Owned Coordinator Student')
        ->assertDontSee('Other Coordinator Student');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $coordinator->id)
        ->where('participant_role', 'coordinator')
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Please check your requirements.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $coordinator->id)
        ->where('body', 'Please check your requirements.')
        ->exists())->toBeTrue();
});

test('coordinators cannot open conversations for students they did not create', function () {
    $coordinator = createMessagingUser(AppRole::OJTCOORDINATOR);
    $otherCoordinator = createMessagingUser(AppRole::OJTCOORDINATOR);
    $student = createMessagingUser(AppRole::STUDENT, ['coordinator_id' => $otherCoordinator->id]);

    $conversation = ChatConversation::query()->create([
        'student_id' => $student->id,
        'participant_id' => $otherCoordinator->id,
        'participant_role' => 'coordinator',
    ]);

    $this->actingAs($coordinator);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertForbidden();
});

test('mentors can reply to assigned students only', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Mentor Chat Student']);
    $otherStudent = createMessagingUser(AppRole::STUDENT, ['name' => 'Other Chat Student']);
    $mentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Messaging Mentor']);
    $otherMentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Other Messaging Mentor']);

    createMessagingAssignment($student, $mentor);
    $otherAssignment = createMessagingAssignment($otherStudent, $otherMentor);

    ChatConversation::query()->create([
        'ojt_assignment_id' => $otherAssignment->id,
        'student_id' => $otherStudent->id,
        'participant_id' => $otherMentor->id,
        'participant_role' => 'supervisor',
    ]);

    $this->actingAs($mentor)
        ->get(route('supervisor.messages'))
        ->assertOk()
        ->assertSee('Mentor Chat Student')
        ->assertDontSee('Other Chat Student');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $mentor->id)
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Please submit your report today.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $mentor->id)
        ->where('body', 'Please submit your report today.')
        ->exists())->toBeTrue();
});

test('mentors cannot open conversations for students not assigned to them', function () {
    $student = createMessagingUser(AppRole::STUDENT);
    $mentor = createMessagingUser(AppRole::SUPERVISOR);
    $otherMentor = createMessagingUser(AppRole::SUPERVISOR);

    $assignment = createMessagingAssignment($student, $otherMentor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $otherMentor->id,
        'participant_role' => 'supervisor',
    ]);

    $this->actingAs($mentor);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertForbidden();
});

test('supervisors can message their assigned students', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Supervisor Chat Student']);
    $supervisor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Messaging Supervisor']);

    createMessagingAssignment($student, null, $supervisor);

    $this->actingAs($supervisor)
        ->get(route('supervisor.messages'))
        ->assertOk()
        ->assertSee('Supervisor Chat Student');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $supervisor->id)
        ->where('participant_role', 'supervisor')
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Your attendance looks good.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $supervisor->id)
        ->where('body', 'Your attendance looks good.')
        ->exists())->toBeTrue();
});

test('users can edit and delete their own messages only', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Message Owner Student']);
    $mentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Message Owner Mentor']);

    $assignment = createMessagingAssignment($student, $mentor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $mentor->id,
        'participant_role' => 'supervisor',
    ]);

    $studentMessage = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'Original student message.',
    ]);

    $mentorMessage = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $mentor->id,
        'body' => 'Mentor message.',
    ]);

    $this->actingAs($student);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('startEditingMessage', $studentMessage->id)
        ->set('editingBody', 'Edited student message.')
        ->call('saveMessageEdit')
        ->assertHasNoErrors();

    expect($studentMessage->refresh()->body)->toBe('Edited student message.');
    expect($studentMessage->edited_at)->not->toBeNull();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('deleteMessage', $studentMessage->id)
        ->assertHasNoErrors();

    expect(ChatMessage::query()->whereKey($studentMessage->id)->exists())->toBeFalse();
    $deletedMessage = ChatMessage::withTrashed()->findOrFail($studentMessage->id);
    expect($deletedMessage->body)->toBe('');
    expect($deletedMessage->deleted_at)->not->toBeNull();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertSee('This message was deleted.')
        ->assertDontSee('Edited student message.')
        ->assertDontSee(' · edited')
        ->assertDontSee('wire:click="startEditingMessage('.$studentMessage->id.')"', false)
        ->assertDontSee('wire:click="deleteMessage('.$studentMessage->id.')"', false);

    expect(fn () => Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('startEditingMessage', $studentMessage->id))->toThrow(ModelNotFoundException::class);

    expect(fn () => Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('editingMessageId', $studentMessage->id)
        ->set('editingBody', 'Restored private text.')
        ->call('saveMessageEdit'))->toThrow(ModelNotFoundException::class);

    expect(fn () => Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('deleteMessage', $studentMessage->id))->toThrow(ModelNotFoundException::class);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('startEditingMessage', $mentorMessage->id)
        ->assertForbidden();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('deleteMessage', $mentorMessage->id)
        ->assertForbidden();

    $this->actingAs($mentor);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertSee('This message was deleted.')
        ->assertDontSee('Edited student message.');
});

test('messages show edited label only after an actual edit', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Edited Label Student']);
    $mentor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Edited Label Mentor']);

    $assignment = createMessagingAssignment($student, $mentor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $mentor->id,
        'participant_role' => 'supervisor',
    ]);

    $message = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'Fresh message.',
    ]);

    $this->actingAs($student);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertSee('Fresh message.')
        ->assertDontSee('edited');

    $message->update([
        'body' => 'Actually edited.',
        'edited_at' => now(),
    ]);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertSee('Actually edited.')
        ->assertSee('edited');
});

test('manual refresh shows new messages and marks them read', function () {
    $student = createMessagingUser(AppRole::STUDENT);
    $mentor = createMessagingUser(AppRole::SUPERVISOR);

    $assignment = createMessagingAssignment($student, $mentor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $mentor->id,
        'participant_role' => 'supervisor',
    ]);

    $this->actingAs($mentor);

    $chat = Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertSee('Refresh')
        ->assertSee('wire:poll.5s="pollMessages"', false);

    $message = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'New message after opening.',
    ]);

    $chat->call('refreshMessages')->assertSee('New message after opening.');
    expect($message->refresh()->read_at)->not->toBeNull();

    $chat->call('pollMessages')->assertNotDispatched('message-received');

    $polled = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'Arrived via poll.',
    ]);

    $chat->call('pollMessages')->assertSee('Arrived via poll.')->assertDispatched('message-received');
    expect($polled->refresh()->read_at)->not->toBeNull();
});

test('deleted latest message remains in the preview and unread count ignores it', function () {
    $student = createMessagingUser(AppRole::STUDENT);
    $mentor = createMessagingUser(AppRole::SUPERVISOR);
    $assignment = createMessagingAssignment($student, $mentor);
    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $mentor->id,
        'participant_role' => 'supervisor',
    ]);
    ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'Older message.',
    ]);
    $latest = ChatMessage::query()->create([
        'chat_conversation_id' => $conversation->id,
        'sender_id' => $student->id,
        'body' => 'Private latest message.',
    ]);

    $this->actingAs($student);
    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('deleteMessage', $latest->id);

    expect($conversation->fresh()->latestMessage->id)->toBe($latest->id);
    expect($conversation->messages()->whereNull('read_at')->count())->toBe(1);

    $this->actingAs($mentor);
    $chat = Livewire::test('pages::messages')
        ->assertSee('This message was deleted.')
        ->assertSee('Older message.')
        ->assertDontSee('Private latest message.');

    expect(substr_count($chat->html(), 'This message was deleted.'))->toBe(2);
    expect($latest->refresh()->read_at)->toBeNull();
});

test('contacts can be filtered by role tab', function () {
    $coordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Tab Coordinator']);
    $supervisor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Tab Supervisor']);
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Tab Student', 'coordinator_id' => $coordinator->id]);

    createMessagingAssignment($student, $supervisor);

    $this->actingAs($student);

    $component = Livewire::test('pages::messages');
    $peerNames = fn () => $component->instance()->conversations->map(fn ($conversation) => $component->instance()->peerFor($conversation)->name)->all();

    expect($component->instance()->contactRoles)->toBe(['Coordinator', 'Supervisor']);

    $component->set('roleFilter', 'Supervisor');
    expect($peerNames())->toBe(['Tab Supervisor']);

    $component->set('roleFilter', 'Coordinator');
    expect($peerNames())->toBe(['Tab Coordinator']);

    $component->set('roleFilter', 'all');
    expect($peerNames())->toHaveCount(2);
});

test('coordinators can message supervisors they created and supervisors can reply', function () {
    $coordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Supervisor Chat Coordinator']);
    $otherCoordinator = createMessagingUser(AppRole::OJTCOORDINATOR, ['name' => 'Other Chat Coordinator']);
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Coordinator Chat Student', 'coordinator_id' => $coordinator->id]);
    $supervisor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Coordinator Chat Supervisor', 'coordinator_id' => $coordinator->id]);
    $otherSupervisor = createMessagingUser(AppRole::SUPERVISOR, ['name' => 'Unrelated Chat Supervisor', 'coordinator_id' => $otherCoordinator->id]);

    createMessagingAssignment($student, $supervisor);

    $this->actingAs($coordinator);

    $component = Livewire::test('pages::messages');
    $peerNames = fn () => $component->instance()->conversations->map(fn ($conversation) => $component->instance()->peerFor($conversation)->name)->sort()->values()->all();

    expect($component->instance()->contactRoles)->toBe(['Student', 'Supervisor'])
        ->and($peerNames())->toBe(['Coordinator Chat Student', 'Coordinator Chat Supervisor']);

    $component->set('roleFilter', 'Supervisor');
    expect($peerNames())->toBe(['Coordinator Chat Supervisor']);

    $conversation = ChatConversation::query()
        ->where('student_id', $supervisor->id)
        ->where('participant_id', $coordinator->id)
        ->where('participant_role', 'coordinator')
        ->firstOrFail();

    $component->call('selectConversation', $conversation->id)
        ->set('body', 'Hello, supervisor.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    $otherConversation = ChatConversation::query()->where('student_id', $otherSupervisor->id)->first();
    expect($otherConversation)->toBeNull();

    $this->actingAs($supervisor);

    $component = Livewire::test('pages::messages');
    $peerNames = fn () => $component->instance()->conversations->map(fn ($conversation) => $component->instance()->peerFor($conversation)->name)->sort()->values()->all();
    expect($peerNames())->toBe(['Coordinator Chat Student', 'Supervisor Chat Coordinator'])
        ->and($component->instance()->contactRoles)->toBe(['Coordinator', 'Student'])
        ->and($component->instance()->roleLabelFor($conversation->fresh()))->toBe('Coordinator');

    $component->call('selectConversation', $conversation->id)
        ->set('body', 'Hello, coordinator.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()->where('chat_conversation_id', $conversation->id)->count())->toBe(2);

    $this->actingAs($otherSupervisor);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertForbidden();
});
