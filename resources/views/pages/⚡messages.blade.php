<?php

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Messages')] class extends Component
{
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Messages']];

    public string $title = 'Messages';

    public string $description = 'Chat with the students and OJT contacts connected to your account.';

    public ?int $selectedConversationId = null;

    public string $search = '';

    public string $body = '';

    public ?int $editingMessageId = null;

    public string $editingBody = '';

    #[Locked]
    public ?int $lastSeenMessageId = null;

    public function mount(): void
    {
        $this->syncConversations();

        $this->selectedConversationId = $this->conversations->first()?->id;

        if ($this->selectedConversationId) {
            $this->markConversationRead($this->selectedConversationId);
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $this->authorizeConversation($conversationId);

        $this->selectedConversationId = $conversationId;
        $this->body = '';

        $this->markConversationRead($conversationId);
        unset($this->selectedConversation, $this->chatMessages, $this->conversations);

        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function sendMessage(): void
    {
        $this->validate(
            [
                'body' => ['required', 'string', 'max:2000'],
            ],
            [],
            [
                'body' => 'message',
            ],
        );

        abort_unless($this->selectedConversationId, 404);

        $conversation = $this->authorizeConversation($this->selectedConversationId);

        ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => trim($this->body),
        ]);

        $conversation->touch();

        $this->body = '';
        unset($this->selectedConversation, $this->chatMessages, $this->conversations);

        $this->dispatch('message-sent');
    }

    public function startEditingMessage(int $messageId): void
    {
        $message = $this->authorizeOwnMessage($messageId);

        $this->editingMessageId = $message->id;
        $this->editingBody = $message->body;
        $this->resetValidation();
    }

    public function cancelEditingMessage(): void
    {
        $this->editingMessageId = null;
        $this->editingBody = '';
    }

    public function saveMessageEdit(): void
    {
        $this->validate(
            [
                'editingMessageId' => ['required', 'integer'],
                'editingBody' => ['required', 'string', 'max:2000'],
            ],
            [],
            [
                'editingBody' => 'message',
            ],
        );

        $message = $this->authorizeOwnMessage((int) $this->editingMessageId);

        $message->update([
            'body' => trim($this->editingBody),
            'edited_at' => now(),
        ]);

        $this->cancelEditingMessage();
        unset($this->selectedConversation, $this->chatMessages, $this->conversations);
    }

    public function deleteMessage(int $messageId): void
    {
        $message = $this->authorizeOwnMessage($messageId);
        $conversation = $message->conversation;

        $message->delete();
        $conversation?->touch();

        if ($this->editingMessageId === $messageId) {
            $this->cancelEditingMessage();
        }

        unset($this->selectedConversation, $this->chatMessages, $this->conversations);
    }

    public function restoreMessage(int $messageId): void
    {
        $message = $this->authorizeOwnMessage($messageId, withTrashed: true);

        abort_unless($message->trashed(), 404);

        $conversation = $message->conversation;
        $message->restore();
        $conversation?->touch();

        unset($this->selectedConversation, $this->chatMessages, $this->conversations);
    }

    public function refreshMessages(): void
    {
        if ($this->selectedConversationId) {
            $this->authorizeConversation($this->selectedConversationId);
            $this->markConversationRead($this->selectedConversationId);
        }

        unset($this->selectedConversation, $this->chatMessages, $this->conversations);

        $this->dispatch('message-received');
    }

    public function pollMessages(): void
    {
        if ($this->selectedConversationId) {
            $this->markConversationRead($this->selectedConversationId);
        }

        // Only scroll when something new arrived, so reading older messages isn't interrupted.
        $latestId = $this->chatMessages->max('id');

        if ($this->lastSeenMessageId !== null && $latestId !== $this->lastSeenMessageId) {
            $this->dispatch('message-received');
        }

        $this->lastSeenMessageId = $latestId;
    }

    protected function syncConversations(): void
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            $this->ensureCoordinatorConversation($user);

            OjtAssignment::query()
                ->where('student_id', $user->id)
                ->get()
                ->each(function (OjtAssignment $assignment): void {
                    $this->ensureConversation($assignment, $assignment->company_supervisor_id, 'supervisor');
                });

            return;
        }

        if ($user->hasRole('ojt_coordinator')) {
            User::query()
                ->where('coordinator_id', $user->id)
                ->role('student')
                ->get()
                ->each(fn (User $student) => $this->ensureCoordinatorConversation($student, $user->id));

            return;
        }

        if ($user->hasRole('supervisor')) {
            OjtAssignment::query()->where('company_supervisor_id', $user->id)->get()->each(fn (OjtAssignment $assignment) => $this->ensureConversation($assignment, $user->id, 'supervisor'));
        }
    }

    protected function ensureCoordinatorConversation(User $student, ?int $coordinatorId = null): void
    {
        $coordinatorId ??= $student->coordinator_id;

        if (! $coordinatorId) {
            return;
        }

        ChatConversation::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'participant_id' => $coordinatorId,
                'participant_role' => 'coordinator',
            ],
            [
                'ojt_assignment_id' => null,
            ],
        );
    }

    protected function ensureConversation(OjtAssignment $assignment, ?int $participantId, string $role): void
    {
        if (! $participantId) {
            return;
        }

        ChatConversation::query()->firstOrCreate(
            [
                'ojt_assignment_id' => $assignment->id,
                'participant_id' => $participantId,
                'participant_role' => $role,
            ],
            [
                'student_id' => $assignment->student_id,
            ],
        );
    }

    protected function conversationQuery(): Builder
    {
        $user = Auth::user();

        return ChatConversation::query()->when($user->hasRole('student'), fn (Builder $query) => $query->where('student_id', $user->id))->when($user->hasRole('ojt_coordinator'), fn (Builder $query) => $query->where('participant_id', $user->id)->where('participant_role', 'coordinator'))->when($user->hasRole('supervisor'), fn (Builder $query) => $query->where('participant_id', $user->id)->where('participant_role', 'supervisor'));
    }

    protected function authorizeConversation(int $conversationId): ChatConversation
    {
        $conversation = ChatConversation::query()->findOrFail($conversationId);

        abort_unless($this->conversationQuery()->whereKey($conversationId)->exists(), 403);

        return $conversation->load(['student.studentProfile', 'participant', 'latestMessage.sender']);
    }

    protected function authorizeOwnMessage(int $messageId, bool $withTrashed = false): ChatMessage
    {
        $query = ChatMessage::query()->with('conversation');

        if ($withTrashed) {
            $query->withTrashed();
        }

        $message = $query->findOrFail($messageId);

        abort_unless((int) $message->sender_id === (int) Auth::id(), 403);
        abort_unless($this->conversationQuery()->whereKey($message->chat_conversation_id)->exists(), 403);

        return $message;
    }

    protected function markConversationRead(int $conversationId): void
    {
        ChatMessage::query()
            ->where('chat_conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function peerFor(ChatConversation $conversation): ?User
    {
        return (int) $conversation->student_id === (int) Auth::id() ? $conversation->participant : $conversation->student;
    }

    public function roleLabelFor(ChatConversation $conversation): string
    {
        if ((int) $conversation->student_id !== (int) Auth::id()) {
            return 'Student';
        }

        return match ($conversation->participant_role) {
            'supervisor' => 'Supervisor',
            'coordinator' => 'Coordinator',
            default => 'Supervisor',
        };
    }

    public function roleColorFor(ChatConversation $conversation): string
    {
        return match ($this->roleLabelFor($conversation)) {
            'Supervisor' => 'amber',
            'Coordinator' => 'red',
            default => 'zinc',
        };
    }

    public function chatBadgeFor(ChatConversation $conversation): string
    {
        return $conversation->participant_role === 'coordinator' ? 'Coordinator Chat' : 'OJT Chat';
    }

    public function emptyMessageFor(ChatConversation $conversation): string
    {
        return $conversation->participant_role === 'coordinator'
            ? 'Send the first message to your coordinator.'
            : 'Send the first message for this OJT assignment.';
    }

    #[Computed]
    public function conversations()
    {
        $conversations = $this->conversationQuery()
            ->with(['student.studentProfile', 'participant', 'latestMessage.sender'])
            ->withCount([
                'messages as unread_count' => fn (Builder $query) => $query->where('sender_id', '!=', Auth::id())->whereNull('read_at'),
            ])
            ->get()
            ->sortBy(fn (ChatConversation $conversation) => mb_strtolower($this->peerFor($conversation)?->last_name ?? ''))
            ->values();

        $search = mb_strtolower(trim($this->search));

        return $search === '' ? $conversations : $conversations->filter(fn (ChatConversation $conversation) => str_contains(mb_strtolower($this->peerFor($conversation)?->name ?? ''), $search))->values();
    }

    #[Computed]
    public function selectedConversation(): ?ChatConversation
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return $this->authorizeConversation($this->selectedConversationId);
    }

    #[Computed]
    public function chatMessages()
    {
        if (! $this->selectedConversationId) {
            return collect();
        }

        $this->authorizeConversation($this->selectedConversationId);

        return ChatMessage::query()->withTrashed()->with('sender')->where('chat_conversation_id', $this->selectedConversationId)->oldest()->get();
    }
};
?>

<div wire:poll.5s="pollMessages" class="duty-messages flex h-[calc(100vh-7rem)] min-h-0 flex-col gap-6 overflow-hidden">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="flex items-center gap-2 lg:hidden">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            aria-label="Search contacts" placeholder="Search contacts..." clearable class="min-w-0 flex-1" />
        <flux:modal.trigger name="mobile-contacts">
            <flux:button type="button" icon="users">Contacts</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid min-h-0 flex-1 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:grid-cols-[20rem_1fr]">
        <aside class="hidden min-h-0 flex-col border-b border-zinc-200 dark:border-zinc-800 lg:flex lg:border-b-0 lg:border-r">
            <div class="shrink-0 border-b border-zinc-200 p-4 dark:border-zinc-800">
                <flux:heading size="lg">Conversations</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Your available contacts</flux:text>
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    aria-label="Search contacts" placeholder="Search contacts..." clearable class="mt-4" />
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                @forelse ($this->conversations as $conversation)
                    @php($peer = $this->peerFor($conversation))

                    <button type="button" wire:click="selectConversation({{ $conversation->id }})"
                        class="flex w-full items-center gap-3 rounded-lg p-3 text-left transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $selectedConversationId === $conversation->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                        <flux:avatar size="sm" :name="$peer?->display_name ?? 'Contact'"
                            :src="$peer?->profile_photo_path ? $peer->profilePhotoUrl() : null" />

                        <span class="min-w-0 flex-1">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $peer?->display_name ?? 'Contact' }}
                                </span>

                                @if ($conversation->unread_count > 0)
                                    <flux:badge color="red" size="sm" class="shrink-0">
                                        {{ $conversation->unread_count }}</flux:badge>
                                @endif
                            </span>

                            <span class="mt-1 flex min-w-0 items-center gap-2">
                                <flux:badge :color="$this->roleColorFor($conversation)" size="sm"
                                    class="shrink-0">
                                    {{ $this->roleLabelFor($conversation) }}
                                </flux:badge>
                                <span class="truncate text-xs text-zinc-500">
                                    {{ $conversation->latestMessage?->deleted_at ? 'Message archived.' : ($conversation->latestMessage?->body ?? 'No messages yet.') }}
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="p-8 text-center">
                        <flux:icon name="chat-bubble-left-right" class="mx-auto size-10 text-zinc-400" />
                        @if (filled($search))
                            <flux:heading size="md" class="mt-3">No contacts found</flux:heading>
                            <flux:text class="mt-1 text-zinc-500">Try a different name.</flux:text>
                        @else
                            <flux:heading size="md" class="mt-3">No contacts yet</flux:heading>
                            <flux:text class="mt-1 text-zinc-500">Messages appear after your account is connected to a contact.
                            </flux:text>
                        @endif
                    </div>
                @endforelse
            </div>
        </aside>

        <section class="flex min-h-0 flex-col">
            @if ($this->selectedConversation)
                @php($peer = $this->peerFor($this->selectedConversation))

                <header
                    class="flex shrink-0 items-center justify-between gap-3 border-b border-zinc-200 p-4 pb-5.5 dark:border-zinc-800">
                    <div class="flex min-w-0 items-center gap-3">
                        <flux:avatar :name="$peer?->display_name ?? 'Contact'"
                            :src="$peer?->profile_photo_path ? $peer->profilePhotoUrl() : null" />

                        <div class="min-w-0">
                            <flux:heading class="truncate">{{ $peer?->display_name ?? 'Contact' }}</flux:heading>
                            <flux:text class="truncate text-zinc-500">
                                {{ $this->roleLabelFor($this->selectedConversation) }}
                                @if ($peer?->studentProfile?->student_id)
                                    • {{ $peer->studentProfile->student_id }}
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:badge :color="$this->roleColorFor($this->selectedConversation)" inset="top bottom">
                            {{ $this->chatBadgeFor($this->selectedConversation) }}
                        </flux:badge>
                    </div>
                </header>

                <div class="flex-1 space-y-4 overflow-y-auto bg-zinc-50 p-4 dark:bg-zinc-950/40" x-data
                    x-on:message-sent.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                    x-on:message-received.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                    x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                    @forelse ($this->chatMessages as $message)
                        @php($mine = (int) $message->sender_id === (int) auth()->id())

                        <div class="flex gap-3 {{ $mine ? 'justify-end' : 'justify-start' }}">
                            @unless ($mine)
                                <flux:avatar size="sm" :name="$message->sender?->display_name ?? 'Contact'"
                                    :src="$message->sender?->profile_photo_path ? $message->sender->profilePhotoUrl() : null" />
                            @endunless

                            <div class="group max-w-[min(34rem,80%)]">
                                <div class="flex items-center gap-2 {{ $mine ? 'flex-row-reverse' : '' }}">
                                    @if ($editingMessageId === $message->id)
                                        <div
                                            class="min-w-[min(28rem,70vw)] rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                            <flux:textarea wire:model="editingBody" rows="auto" class="text-sm" label="Message" required
                                                x-on:keydown.enter="if (!$event.shiftKey && !$event.isComposing) { $event.preventDefault(); $wire.saveMessageEdit() }"
                                                x-on:keydown.escape="$wire.cancelEditingMessage()" />
                                            <div class="mt-3 flex justify-end gap-2">
                                                <flux:button type="button" size="sm" variant="ghost"
                                                    wire:click="cancelEditingMessage">
                                                    Cancel
                                                </flux:button>
                                                <flux:button type="button" size="sm" variant="primary"
                                                    icon="check" wire:click="saveMessageEdit">
                                                    Save
                                                </flux:button>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="rounded-lg px-4 py-3 text-sm shadow-sm {{ $mine ? 'bg-red-600 text-white' : 'border border-zinc-200 bg-white text-zinc-900 dark:border-zinc-800 dark:bg-zinc-900 dark:text-white' }}">
                                            {{ $message->deleted_at ? 'This message is archived.' : $message->body }}
                                        </div>
                                    @endif

                                    @if ($mine && ! $message->deleted_at && $editingMessageId !== $message->id)
                                        <div class="flex shrink-0 items-center gap-1">
                                            <flux:button type="button" size="sm" variant="ghost"
                                                icon="pencil-square" title="Edit message"
                                                wire:click="startEditingMessage({{ $message->id }})" />
                                            <flux:modal.trigger name="delete-message-{{ $message->id }}">
                                                <flux:button type="button" size="sm" variant="ghost"
                                                    icon="archive-box" title="Archive message" />
                                            </flux:modal.trigger>
                                        </div>
                                    @endif

                                    @if ($mine && $message->deleted_at)
                                        <flux:button type="button" size="sm" variant="ghost" icon="arrow-uturn-left"
                                            wire:click="restoreMessage({{ $message->id }})">
                                            Restore
                                        </flux:button>
                                    @endif

                                    @if ($mine && ! $message->deleted_at)
                                    <flux:modal name="delete-message-{{ $message->id }}" class="w-[calc(100vw-2rem)] max-w-sm!">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">Archive message?</flux:heading>
                                                <flux:text class="mt-2">
                                                    This hides the message from the conversation. You can restore it later.
                                                </flux:text>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>
                                                <flux:button wire:click="deleteMessage({{ $message->id }})"
                                                    variant="filled">Archive message
                                                </flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-zinc-500 {{ $mine ? 'text-right' : '' }}">
                                    {{ $message->created_at->format('M d, g:i A') }}
                                    @if ($message->edited_at && ! $message->deleted_at)
                                        <span> · edited</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-center">
                            <div>
                                <flux:icon name="chat-bubble-left-ellipsis" class="mx-auto size-10 text-zinc-400" />
                                <flux:heading size="md" class="mt-3">Start the conversation</flux:heading>
                                <flux:text class="mt-1 text-zinc-500">{{ $this->emptyMessageFor($this->selectedConversation) }}
                                </flux:text>
                            </div>
                        </div>
                    @endforelse
                </div>

                <form wire:submit="sendMessage" class="shrink-0 border-t border-zinc-200 p-4 dark:border-zinc-800">
                    <flux:field data-hide-required-indicator>
                        <flux:label>Message</flux:label>
                        <div class="flex items-center gap-3">
                            <flux:textarea wire:model="body" rows="auto" required placeholder="Type your message..." class="min-w-0 flex-1"
                                x-on:keydown.enter="if (!$event.shiftKey && !$event.isComposing) { $event.preventDefault(); $el.closest('form').requestSubmit() }" />
                            <flux:button type="submit" variant="primary" icon="paper-airplane" />
                        </div>
                        <flux:error name="body" />
                    </flux:field>
                </form>
            @else
                <div class="flex flex-1 items-center justify-center p-8 text-center">
                    <div>
                        <flux:icon name="chat-bubble-left-right" class="mx-auto size-12 text-zinc-400" />
                        <flux:heading size="lg" class="mt-3">Select a conversation</flux:heading>
                        <flux:text class="mt-1 text-zinc-500">Choose an assignment contact to start messaging.
                        </flux:text>
                    </div>
                </div>
            @endif
        </section>
    </div>

    <flux:modal name="mobile-contacts" class="w-[calc(100vw-2rem)] max-w-lg!">
        <div class="mb-4">
            <flux:heading size="lg">Choose a contact</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Select a conversation to open.</flux:text>
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                aria-label="Search contacts" placeholder="Search contacts..." clearable class="mt-4" />
        </div>

        <div class="max-h-[55dvh] space-y-1 overflow-y-auto">
            @forelse ($this->conversations as $conversation)
                @php($peer = $this->peerFor($conversation))

                <flux:modal.close>
                    <button type="button" wire:click="selectConversation({{ $conversation->id }})"
                        aria-pressed="{{ $selectedConversationId === $conversation->id ? 'true' : 'false' }}"
                        class="flex w-full items-center gap-3 rounded-lg p-3 text-left transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $selectedConversationId === $conversation->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                        <flux:avatar size="sm" :name="$peer?->display_name ?? 'Contact'"
                            :src="$peer?->profile_photo_path ? $peer->profilePhotoUrl() : null" />
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $peer?->display_name ?? 'Contact' }}
                        </span>
                        <flux:badge :color="$this->roleColorFor($conversation)" size="sm" class="shrink-0">
                            {{ $this->roleLabelFor($conversation) }}
                        </flux:badge>
                        @if ($conversation->unread_count > 0)
                            <flux:badge color="red" size="sm" class="shrink-0">
                                {{ $conversation->unread_count }}</flux:badge>
                        @endif
                    </button>
                </flux:modal.close>
            @empty
                <div class="p-8 text-center">
                    <flux:icon name="chat-bubble-left-right" class="mx-auto size-10 text-zinc-400" />
                    <flux:heading size="md" class="mt-3">
                        {{ filled($search) ? 'No contacts found' : 'No contacts yet' }}
                    </flux:heading>
                    <flux:text class="mt-1 text-zinc-500">
                        {{ filled($search) ? 'Try a different name.' : 'Messages appear after your account is connected to a contact.' }}
                    </flux:text>
                </div>
            @endforelse
        </div>
    </flux:modal>
</div>
