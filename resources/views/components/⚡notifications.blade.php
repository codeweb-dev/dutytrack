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
        return Auth::user()->notifications()->latest()->limit(5)->get();
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
            <flux:button icon="bell" variant="subtle" aria-label="Notifications" tooltip="Notifications" />
            @if ($this->unreadCount)
                <span class="pointer-events-none absolute -top-0.5 -right-0.5 flex min-w-4 h-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </div>

        <flux:menu class="w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl! p-0! shadow-xl! shadow-zinc-900/10 dark:shadow-black/40">
            <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-600">
                <flux:heading>{{ __('Notifications') }}</flux:heading>
                @if ($this->unreadCount)
                    <flux:button size="xs" variant="ghost" wire:click="markAllRead">{{ __('Mark all read') }}</flux:button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse ($this->items as $notification)
                    <x-notification-item :notification="$notification" wire:key="notification-{{ $notification->id }}" />
                @empty
                    <div class="px-4 py-8 text-center">
                        <flux:text>{{ __('No notifications yet.') }}</flux:text>
                    </div>
                @endforelse
            </div>

            <a href="{{ route('notifications') }}" wire:navigate
                class="block border-t border-zinc-200 px-4 py-3 text-center text-sm font-medium hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-600/50">
                {{ __('See all') }}
            </a>
        </flux:menu>
    </flux:dropdown>
</div>
