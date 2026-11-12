<?php

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Notifications')] class extends Component {
    public int $perPage = 20;

    public function loadMore(): void
    {
        $this->perPage += 20;
        unset($this->items);
    }

    #[Computed]
    public function items(): Paginator
    {
        return Auth::user()->notifications()->latest()->simplePaginate($this->perPage, page: 1);
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

<div class="mx-auto flex w-full max-w-2xl flex-col gap-6 pb-16">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="2xl">Notifications</flux:heading>
            <flux:text class="mt-2 text-base">Everything that needed your attention, newest first.</flux:text>
        </div>

        <flux:button size="sm" wire:click="markAllRead" class="shrink-0">Mark all read</flux:button>
    </div>

    <div class="flex flex-col gap-4">
        @if ($this->items->isNotEmpty())
            <flux:card class="divide-y divide-zinc-200 overflow-hidden p-0! dark:divide-zinc-700">
                @foreach ($this->items as $notification)
                    <x-notification-item :notification="$notification" wire:key="notification-{{ $notification->id }}" />
                @endforeach
            </flux:card>
        @else
            <div class="flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="bell" class="size-12" />
                <flux:heading size="xl">No notifications yet</flux:heading>
            </div>
        @endif

        @if ($this->items->hasMorePages())
            <flux:button wire:click="loadMore" variant="subtle" class="self-center">Load more</flux:button>
        @elseif ($this->items->isNotEmpty())
            <flux:text class="py-2 text-center text-sm">You're all caught up.</flux:text>
        @endif
    </div>
</div>
