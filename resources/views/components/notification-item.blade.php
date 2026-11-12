@props(['notification'])

<button type="button" wire:click="open('{{ $notification->id }}')"
    {{ $attributes->class('flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-600/50') }}>
    <flux:icon :name="$notification->data['icon'] ?? 'bell'" @class(['mt-0.5 size-5 shrink-0', 'text-zinc-400' => $notification->read_at, 'text-red-500' => ! $notification->read_at]) />
    <div class="min-w-0 flex-1">
        <flux:heading class="truncate {{ $notification->read_at ? 'font-normal!' : '' }}">{{ $notification->data['title'] ?? '' }}</flux:heading>
        @if (! empty($notification->data['body']))
            <flux:text size="sm" class="line-clamp-2">{{ $notification->data['body'] }}</flux:text>
        @endif
        <flux:text size="sm" class="mt-1 text-zinc-400">{{ $notification->created_at->diffForHumans() }}</flux:text>
    </div>
    @unless ($notification->read_at)
        <span class="mt-2 size-2 shrink-0 rounded-full bg-red-500"></span>
    @endunless
</button>
