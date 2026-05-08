<?php

use App\Enums\Role as AppRole;
use App\Events\ConversationMessageSent;
use App\Events\ConversationTypingStarted;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatTypingIndicator;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Event;
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
            'year_level' => '4',
            'section' => 'A',
            'ojt_required_hours' => 600,
        ]);
    }

    return $user;
}

function createMessagingAssignment(User $student, ?User $professor = null, ?User $supervisor = null): OjtAssignment
{
    return OjtAssignment::query()->create([
        'student_id' => $student->id,
        'ojt_type' => $supervisor ? 'external' : 'internal',
        'professor_id' => $professor?->id,
        'company_supervisor_id' => $supervisor?->id,
        'start_date' => '2026-04-20',
        'required_hours' => 600,
        'status' => 'active',
    ]);
}

test('students can message their assigned professor and supervisor', function () {
    Event::fake([ConversationMessageSent::class]);

    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Message Student']);
    $professor = createMessagingUser(AppRole::PROFESSOR, ['name' => 'Assigned Professor']);
    $supervisor = createMessagingUser(AppRole::COMPANYSUPERVISOR, ['name' => 'Assigned Supervisor']);
    $otherProfessor = createMessagingUser(AppRole::PROFESSOR, ['name' => 'Other Professor']);

    createMessagingAssignment($student, $professor, $supervisor);

    $this->actingAs($student)
        ->get(route('student.messages'))
        ->assertOk()
        ->assertSee('Assigned Professor')
        ->assertSee('Assigned Supervisor')
        ->assertDontSee('Other Professor');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $professor->id)
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Good afternoon, professor.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $student->id)
        ->where('body', 'Good afternoon, professor.')
        ->exists())->toBeTrue();

    Event::assertDispatched(ConversationMessageSent::class);
});

test('professors can reply to assigned students only', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Professor Chat Student']);
    $otherStudent = createMessagingUser(AppRole::STUDENT, ['name' => 'Other Chat Student']);
    $professor = createMessagingUser(AppRole::PROFESSOR, ['name' => 'Messaging Professor']);
    $otherProfessor = createMessagingUser(AppRole::PROFESSOR, ['name' => 'Other Messaging Professor']);

    createMessagingAssignment($student, $professor);
    $otherAssignment = createMessagingAssignment($otherStudent, $otherProfessor);

    ChatConversation::query()->create([
        'ojt_assignment_id' => $otherAssignment->id,
        'student_id' => $otherStudent->id,
        'participant_id' => $otherProfessor->id,
        'participant_role' => 'professor',
    ]);

    $this->actingAs($professor)
        ->get(route('professor.messages'))
        ->assertOk()
        ->assertSee('Professor Chat Student')
        ->assertDontSee('Other Chat Student');

    $conversation = ChatConversation::query()
        ->where('student_id', $student->id)
        ->where('participant_id', $professor->id)
        ->firstOrFail();

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->set('body', 'Please submit your report today.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect(ChatMessage::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('sender_id', $professor->id)
        ->where('body', 'Please submit your report today.')
        ->exists())->toBeTrue();
});

test('professors cannot open conversations for students not assigned to them', function () {
    $student = createMessagingUser(AppRole::STUDENT);
    $professor = createMessagingUser(AppRole::PROFESSOR);
    $otherProfessor = createMessagingUser(AppRole::PROFESSOR);

    $assignment = createMessagingAssignment($student, $otherProfessor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $otherProfessor->id,
        'participant_role' => 'professor',
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->assertForbidden();
});

test('supervisors can message their assigned students', function () {
    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Supervisor Chat Student']);
    $supervisor = createMessagingUser(AppRole::COMPANYSUPERVISOR, ['name' => 'Messaging Supervisor']);

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

test('typing indicators show for the other participant', function () {
    Event::fake([ConversationTypingStarted::class]);

    $student = createMessagingUser(AppRole::STUDENT, ['name' => 'Typing Student']);
    $professor = createMessagingUser(AppRole::PROFESSOR, ['name' => 'Typing Professor']);

    $assignment = createMessagingAssignment($student, $professor);

    $conversation = ChatConversation::query()->create([
        'ojt_assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'participant_id' => $professor->id,
        'participant_role' => 'professor',
    ]);

    $this->actingAs($student);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('sendTyping')
        ->assertHasNoErrors();

    expect(ChatTypingIndicator::query()
        ->where('chat_conversation_id', $conversation->id)
        ->where('user_id', $student->id)
        ->where('expires_at', '>', now())
        ->exists())->toBeTrue();

    Event::assertDispatched(ConversationTypingStarted::class);

    $this->actingAs($professor);

    Livewire::test('pages::messages')
        ->call('selectConversation', $conversation->id)
        ->call('receiveTypingIndicator', [
            'conversation_id' => $conversation->id,
            'user_id' => $student->id,
            'name' => $student->name,
        ])
        ->assertSee('Typing Student is typing...');
});
