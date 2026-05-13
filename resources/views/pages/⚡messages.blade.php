<?php

use App\Events\ConversationMessageSent;
use App\Events\ConversationTypingStarted;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatTypingIndicator;
use App\Models\OjtAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Messages')] class extends Component {
    public array $breadcrumbs = [['logo' => 'logo.png', 'href' => '/dashboard'], ['label' => 'Messages']];

    public string $title = 'Messages';

    public string $description = 'Chat with the student, professor, or supervisor connected to an OJT assignment.';

    public ?int $selectedConversationId = null;

    public ?int $latestMessageId = null;

    public string $body = '';

    public ?int $editingMessageId = null;

    public string $editingBody = '';

    public function mount(): void
    {
        $this->syncConversations();

        $this->selectedConversationId = $this->conversations->first()?->id;

        if ($this->selectedConversationId) {
            $this->markConversationRead($this->selectedConversationId);
            $this->latestMessageId = $this->latestMessageIdFor($this->selectedConversationId);
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $this->authorizeConversation($conversationId);

        $this->selectedConversationId = $conversationId;
        $this->body = '';

        $this->markConversationRead($conversationId);
        $this->latestMessageId = $this->latestMessageIdFor($conversationId);
        unset($this->selectedConversation, $this->chatMessages, $this->conversations, $this->typingUsers);

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

        $message = ChatMessage::query()->create([
            'chat_conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => trim($this->body),
        ]);

        $conversation->touch();

        broadcast(new ConversationMessageSent($message));

        $this->body = '';
        $this->latestMessageId = $message->id;
        $this->clearMyTypingIndicator($conversation->id);
        unset($this->selectedConversation, $this->chatMessages, $this->conversations, $this->typingUsers);

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

        $this->latestMessageId = $this->selectedConversationId ? $this->latestMessageIdFor($this->selectedConversationId) : null;

        unset($this->selectedConversation, $this->chatMessages, $this->conversations);
    }

    public function sendTyping(): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        $conversation = $this->authorizeConversation($this->selectedConversationId);

        ChatTypingIndicator::query()->updateOrCreate(
            [
                'chat_conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
            ],
            [
                'expires_at' => now()->addSeconds(3),
            ],
        );

        broadcast(new ConversationTypingStarted($conversation, (int) Auth::id(), Auth::user()?->name ?? 'Someone'));
    }

    #[On('message-realtime-received')]
    public function receiveRealtimeMessage(array $payload): void
    {
        $this->handleBroadcastedMessage($payload);
    }

    #[On('message-typing-received')]
    public function receiveTypingIndicator(array $payload): void
    {
        $conversationId = (int) ($payload['conversation_id'] ?? 0);
        $userId = (int) ($payload['user_id'] ?? 0);

        if (!$conversationId || $userId === (int) Auth::id()) {
            return;
        }

        if (!$this->conversationQuery()->whereKey($conversationId)->exists()) {
            return;
        }

        if ($this->selectedConversationId === $conversationId) {
            unset($this->typingUsers);
        }
    }

    public function handleBroadcastedMessage(array $payload): void
    {
        $conversationId = (int) ($payload['conversation_id'] ?? 0);

        if (!$conversationId || !$this->conversationQuery()->whereKey($conversationId)->exists()) {
            return;
        }

        if ($this->selectedConversationId === $conversationId) {
            $this->markConversationRead($conversationId);
            $this->latestMessageId = (int) ($payload['message_id'] ?? $this->latestMessageIdFor($conversationId));
        }

        unset($this->selectedConversation, $this->chatMessages, $this->conversations, $this->typingUsers);

        $this->dispatch('message-received');
    }

    public function refreshMessages(): void
    {
        $this->pruneExpiredTypingIndicators();
        unset($this->conversations, $this->typingUsers);

        if (!$this->selectedConversationId) {
            return;
        }

        $latestMessageId = $this->latestMessageIdFor($this->selectedConversationId);

        if ($latestMessageId === $this->latestMessageId) {
            return;
        }

        $this->markConversationRead($this->selectedConversationId);
        $this->latestMessageId = $latestMessageId;
        unset($this->selectedConversation, $this->chatMessages, $this->typingUsers);

        $this->dispatch('message-received');
    }

    protected function syncConversations(): void
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            OjtAssignment::query()
                ->where('student_id', $user->id)
                ->get()
                ->each(function (OjtAssignment $assignment): void {
                    $this->ensureConversation($assignment, $assignment->professor_id, 'professor');
                    $this->ensureConversation($assignment, $assignment->company_supervisor_id, 'supervisor');
                });

            return;
        }

        if ($user->hasRole('professor')) {
            OjtAssignment::query()->where('professor_id', $user->id)->get()->each(fn(OjtAssignment $assignment) => $this->ensureConversation($assignment, $user->id, 'professor'));

            return;
        }

        if ($user->hasRole('company_supervisor')) {
            OjtAssignment::query()->where('company_supervisor_id', $user->id)->get()->each(fn(OjtAssignment $assignment) => $this->ensureConversation($assignment, $user->id, 'supervisor'));
        }
    }

    protected function ensureConversation(OjtAssignment $assignment, ?int $participantId, string $role): void
    {
        if (!$participantId) {
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

        return ChatConversation::query()->when($user->hasRole('student'), fn(Builder $query) => $query->where('student_id', $user->id))->when($user->hasRole('professor'), fn(Builder $query) => $query->where('participant_id', $user->id)->where('participant_role', 'professor'))->when($user->hasRole('company_supervisor'), fn(Builder $query) => $query->where('participant_id', $user->id)->where('participant_role', 'supervisor'));
    }

    protected function authorizeConversation(int $conversationId): ChatConversation
    {
        $conversation = ChatConversation::query()->findOrFail($conversationId);

        abort_unless($this->conversationQuery()->whereKey($conversationId)->exists(), 403);

        return $conversation->load(['student.studentProfile', 'participant', 'latestMessage.sender']);
    }

    protected function authorizeOwnMessage(int $messageId): ChatMessage
    {
        $message = ChatMessage::query()->with('conversation')->findOrFail($messageId);

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

    protected function clearMyTypingIndicator(int $conversationId): void
    {
        ChatTypingIndicator::query()->where('chat_conversation_id', $conversationId)->where('user_id', Auth::id())->delete();
    }

    protected function pruneExpiredTypingIndicators(): void
    {
        ChatTypingIndicator::query()->where('expires_at', '<=', now())->delete();
    }

    protected function latestMessageIdFor(int $conversationId): ?int
    {
        return ChatMessage::query()->where('chat_conversation_id', $conversationId)->latest('id')->value('id');
    }

    public function peerFor(ChatConversation $conversation): ?User
    {
        return (int) $conversation->student_id === (int) Auth::id() ? $conversation->participant : $conversation->student;
    }

    public function roleLabelFor(ChatConversation $conversation): string
    {
        return (int) $conversation->student_id === (int) Auth::id() ? ($conversation->participant_role === 'supervisor' ? 'Supervisor' : 'Professor') : 'Student';
    }

    public function roleColorFor(ChatConversation $conversation): string
    {
        return match ($this->roleLabelFor($conversation)) {
            'Professor' => 'sky',
            'Supervisor' => 'amber',
            default => 'zinc',
        };
    }

    #[Computed]
    public function conversations()
    {
        return $this->conversationQuery()
            ->with(['student.studentProfile', 'participant', 'latestMessage.sender'])
            ->withCount([
                'messages as unread_count' => fn(Builder $query) => $query->where('sender_id', '!=', Auth::id())->whereNull('read_at'),
            ])
            ->get()
            ->sortByDesc(fn(ChatConversation $conversation) => $conversation->latestMessage?->created_at ?? $conversation->updated_at)
            ->values();
    }

    #[Computed]
    public function selectedConversation(): ?ChatConversation
    {
        if (!$this->selectedConversationId) {
            return null;
        }

        return $this->authorizeConversation($this->selectedConversationId);
    }

    #[Computed]
    public function chatMessages()
    {
        if (!$this->selectedConversationId) {
            return collect();
        }

        $this->authorizeConversation($this->selectedConversationId);

        return ChatMessage::query()->with('sender')->where('chat_conversation_id', $this->selectedConversationId)->oldest()->get();
    }

    #[Computed]
    public function typingUsers()
    {
        if (!$this->selectedConversationId) {
            return collect();
        }

        $this->authorizeConversation($this->selectedConversationId);

        return ChatTypingIndicator::query()->with('user')->where('chat_conversation_id', $this->selectedConversationId)->where('user_id', '!=', Auth::id())->where('expires_at', '>', now())->latest('updated_at')->get()->pluck('user')->filter()->values();
    }
};
?>

<div class="flex h-[calc(100vh-7rem)] min-h-0 flex-col gap-6 overflow-hidden" x-data="{
    userChannel: null,
    handler: null,
    typingHandler: null,
    lastTypingAt: 0,
    userId: @js(auth()->id()),
    subscribe() {
        const connect = () => {
            if (!window.Echo) {
                window.setTimeout(connect, 150);
                return;
            }

            this.handler = (payload) => {
                window.Livewire?.dispatch('message-realtime-received', { payload });
            };

            this.typingHandler = (payload) => {
                window.Livewire?.dispatch('message-typing-received', { payload });
            };

            this.userChannel = window.Echo.private(`messages.user.${this.userId}`);
            this.userChannel.listen('.message.sent', this.handler);
            this.userChannel.listen('.typing.started', this.typingHandler);
        };

        connect();
    },
    sendTyping() {
        const now = Date.now();

        if (now - this.lastTypingAt < 1000) {
            return;
        }

        this.lastTypingAt = now;
        $wire.sendTyping();
    },
    cleanup() {
        if (this.userChannel && this.handler) {
            this.userChannel.stopListening('.message.sent', this.handler);
        }

        if (this.userChannel && this.typingHandler) {
            this.userChannel.stopListening('.typing.started', this.typingHandler);
        }

        if (window.Echo) {
            window.Echo.leave(`messages.user.${this.userId}`);
        }
    },
    destroy() {
        this.cleanup();
    },
}"
    x-init="subscribe()" x-on:livewire:navigating.window="cleanup()">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div wire:poll.visible.1500ms="refreshMessages"
        class="grid min-h-0 flex-1 overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:grid-cols-[20rem_1fr]">
        <aside class="flex min-h-0 flex-col border-b border-zinc-200 dark:border-zinc-800 lg:border-b-0 lg:border-r">
            <div class="shrink-0 border-b border-zinc-200 p-4 dark:border-zinc-800">
                <flux:heading size="lg">Conversations</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Assignment contacts only</flux:text>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                @forelse ($this->conversations as $conversation)
                    @php($peer = $this->peerFor($conversation))

                    <button type="button" wire:click="selectConversation({{ $conversation->id }})"
                        class="flex w-full items-center gap-3 rounded-lg p-3 text-left transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $selectedConversationId === $conversation->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                        <flux:avatar size="sm" :name="$peer?->name ?? 'Contact'"
                            :src="$peer?->profile_photo_path ? $peer->profilePhotoUrl() : null" />

                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $peer?->name ?? 'Contact' }}
                                </span>

                                @if ($conversation->unread_count > 0)
                                    <flux:badge color="red" size="sm" inset="top bottom">
                                        {{ $conversation->unread_count }}</flux:badge>
                                @endif
                            </span>

                            <span class="mt-1 flex items-center gap-2">
                                <flux:badge :color="$this->roleColorFor($conversation)" size="sm"
                                    inset="top bottom">
                                    {{ $this->roleLabelFor($conversation) }}
                                </flux:badge>
                                <span class="truncate text-xs text-zinc-500">
                                    {{ $conversation->latestMessage?->body ?? 'No messages yet.' }}
                                </span>
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="p-8 text-center">
                        <flux:icon name="chat-bubble-left-right" class="mx-auto size-10 text-zinc-400" />
                        <flux:heading size="md" class="mt-3">No contacts yet</flux:heading>
                        <flux:text class="mt-1 text-zinc-500">Messages appear after an OJT assignment connects you.
                        </flux:text>
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
                        <flux:avatar :name="$peer?->name ?? 'Contact'"
                            :src="$peer?->profile_photo_path ? $peer->profilePhotoUrl() : null" />

                        <div class="min-w-0">
                            <flux:heading class="truncate">{{ $peer?->name ?? 'Contact' }}</flux:heading>
                            <flux:text class="truncate text-zinc-500">
                                {{ $this->roleLabelFor($this->selectedConversation) }}
                                @if ($peer?->studentProfile?->student_id)
                                    • {{ $peer->studentProfile->student_id }}
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <flux:badge :color="$this->roleColorFor($this->selectedConversation)" inset="top bottom">
                        OJT Chat
                    </flux:badge>
                </header>

                <div class="flex-1 space-y-4 overflow-y-auto bg-zinc-50 p-4 dark:bg-zinc-950/40" x-data
                    x-on:message-sent.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                    x-on:message-received.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                    x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                    @forelse ($this->chatMessages as $message)
                        @php($mine = (int) $message->sender_id === (int) auth()->id())

                        <div class="flex gap-3 {{ $mine ? 'justify-end' : 'justify-start' }}">
                            @unless ($mine)
                                <flux:avatar size="sm" :name="$message->sender?->name ?? 'Contact'"
                                    :src="$message->sender?->profile_photo_path ? $message->sender->profilePhotoUrl() : null" />
                            @endunless

                            <div class="group max-w-[min(34rem,80%)]">
                                <div class="flex items-center gap-2 {{ $mine ? 'flex-row-reverse' : '' }}">
                                    @if ($editingMessageId === $message->id)
                                        <div
                                            class="min-w-[min(28rem,70vw)] rounded-lg border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                            <flux:textarea wire:model="editingBody" rows="auto" class="text-sm" />
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
                                            {{ $message->body }}
                                        </div>
                                    @endif

                                    @if ($mine && $editingMessageId !== $message->id)
                                        <div class="flex shrink-0 items-center gap-1">
                                            <flux:button type="button" size="sm" variant="ghost"
                                                icon="pencil-square" title="Edit message"
                                                wire:click="startEditingMessage({{ $message->id }})" />
                                            <flux:modal.trigger name="delete-message-{{ $message->id }}">
                                                <flux:button type="button" size="sm" variant="ghost"
                                                    icon="trash" />
                                            </flux:modal.trigger>
                                        </div>
                                    @endif

                                    <flux:modal name="delete-message-{{ $message->id }}" class="min-w-[22rem]">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">Delete message?</flux:heading>
                                                <flux:text class="mt-2">
                                                    You're about to delete this message.<br>
                                                    This action cannot be reversed.
                                                </flux:text>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>
                                                <flux:button wire:click="deleteMessage({{ $message->id }})"
                                                    variant="danger">Delete message
                                                </flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                </div>
                                <div class="mt-1 text-xs text-zinc-500 {{ $mine ? 'text-right' : '' }}">
                                    {{ $message->created_at->format('M d, g:i A') }}
                                    @if ($message->updated_at->gt($message->created_at))
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
                                <flux:text class="mt-1 text-zinc-500">Send the first message for this OJT assignment.
                                </flux:text>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($this->typingUsers->isNotEmpty())
                    <div
                        class="shrink-0 border-t border-zinc-200 px-4 py-2 text-xs font-medium text-zinc-500 dark:border-zinc-800">
                        {{ $this->typingUsers->count() === 1 ? $this->typingUsers->first()->name . ' is typing...' : 'Several people are typing...' }}
                    </div>
                @endif

                <form wire:submit="sendMessage" class="shrink-0 border-t border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <flux:textarea wire:model="body" rows="auto" placeholder="Type your message..."
                            class="flex-1" x-on:input.debounce.250ms="sendTyping()" />
                        <flux:button type="submit" variant="primary" icon="paper-airplane" />
                    </div>
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
</div>
