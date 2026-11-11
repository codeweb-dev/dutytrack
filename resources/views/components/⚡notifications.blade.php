<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function items()
    {
        return Auth::user()->notifications()->latest()->limit(15)->get();
    }

    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($url = $notification->data['url'] ?? null) {
            return $this->redirect($url, navigate: true);
        }
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }
};
?>

<div wire:poll.3s>
    <flux:dropdown position="bottom" align="end">
        <div class="relative">
            <flux:button icon="bell" variant="subtle" aria-label="Notifications" />
            @if ($this->unreadCount)
                <span class="pointer-events-none absolute -top-0.5 -right-0.5 flex min-w-4 h-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </div>

        <flux:menu class="w-80 max-w-[calc(100vw-2rem)] p-0!">
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <flux:heading>{{ __('Notifications') }}</flux:heading>
                @if ($this->unreadCount)
                    <flux:button size="xs" variant="ghost" wire:click="markAllRead">{{ __('Mark all read') }}</flux:button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse ($this->items as $notification)
                    <button type="button" wire:key="notification-{{ $notification->id }}" wire:click="open('{{ $notification->id }}')"
                        class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/50 {{ $notification->read_at ? '' : 'bg-zinc-50 dark:bg-zinc-700/30' }}">
                        <flux:icon :name="$notification->data['icon'] ?? 'bell'" class="mt-0.5 size-5 shrink-0 text-zinc-400" />
                        <div class="min-w-0 flex-1">
                            <flux:heading class="truncate {{ $notification->read_at ? 'font-normal!' : '' }}">{{ $notification->data['title'] ?? '' }}</flux:heading>
                            @if (! empty($notification->data['body']))
                                <flux:text size="sm" class="line-clamp-2">{{ $notification->data['body'] }}</flux:text>
                            @endif
                            <flux:text size="sm" class="mt-1 text-zinc-400">{{ $notification->created_at->diffForHumans() }}</flux:text>
                        </div>
                        @unless ($notification->read_at)
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-blue-500"></span>
                        @endunless
                    </button>
                @empty
                    <div class="px-4 py-8 text-center">
                        <flux:text>{{ __('No notifications yet.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
