<?php

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Announcements')] class extends Component {
    public string $search = '';

    public string $type = '';

    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->reset('perPage');
        unset($this->announcements);
    }

    public function updatedType(): void
    {
        $this->reset('perPage');
        unset($this->announcements);
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
        unset($this->announcements);
    }

    #[Computed]
    public function announcements(): Paginator
    {
        return Announcement::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', $search)->orWhere('message', 'like', $search)->orWhere('type', 'like', $search);
                });
            })
            ->when($this->type !== '', function ($query) {
                $query->where('type', $this->type);
            })
            ->latest()
            ->simplePaginate($this->perPage, page: 1);
    }

    public function badgeColor(string $type): string
    {
        return match ($type) {
            'warning' => 'amber',
            'urgent' => 'red',
            'maintenance' => 'sky',
            default => 'zinc',
        };
    }

    public function iconName(string $type): string
    {
        return match ($type) {
            'warning' => 'exclamation-triangle',
            'urgent' => 'bolt',
            'maintenance' => 'wrench-screwdriver',
            default => 'information-circle',
        };
    }

    public function canManageAnnouncements(): bool
    {
        return Auth::user()?->hasRole('ojt_coordinator') ?? false;
    }
};
?>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-6 pb-16">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <flux:heading size="2xl">Announcements</flux:heading>
            <flux:text class="mt-2 text-base">Latest news, reminders, and notices for everyone on DutyTrack.</flux:text>
        </div>

        @if ($this->canManageAnnouncements())
            <flux:button :href="route('coordinator.announcements')" wire:navigate size="sm" icon="cog-6-tooth"
                class="shrink-0 self-start">
                Manage Announcements
            </flux:button>
        @endif
    </div>

    <div class="flex flex-col gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            placeholder="Search announcements..." clearable />

        <div class="-mx-1 overflow-x-auto px-1">
            <flux:radio.group wire:model.live="type" variant="pills">
                <flux:radio value="" label="All" />
                <flux:radio value="info" label="Info" />
                <flux:radio value="warning" label="Warning" />
                <flux:radio value="urgent" label="Urgent" />
                <flux:radio value="maintenance" label="Maintenance" />
            </flux:radio.group>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($this->announcements as $announcement)
            <flux:card wire:key="announcement-{{ $announcement->id }}" class="flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <flux:avatar :src="asset('logo.png')" name="DutyTrack" />

                    <div class="min-w-0 flex-1">
                        <flux:heading>DutyTrack</flux:heading>
                        <flux:text class="text-xs" title="{{ $announcement->created_at?->format('M d, Y \a\t h:i A') }}">
                            {{ $announcement->created_at?->diffForHumans() }} · {{ $announcement->created_at?->format('M d, Y') }}
                        </flux:text>
                    </div>

                    <flux:badge size="sm" :color="$this->badgeColor($announcement->type)"
                        :icon="$this->iconName($announcement->type)">
                        {{ ucfirst($announcement->type) }}
                    </flux:badge>
                </div>

                <div>
                    <flux:heading size="lg">{{ $announcement->title }}</flux:heading>
                    <flux:text class="mt-2 whitespace-pre-line text-base">{{ $announcement->message }}</flux:text>
                </div>
            </flux:card>
        @empty
            <div class="flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="megaphone" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No announcements found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter, or check back later.</flux:text>
                </div>
            </div>
        @endforelse

        @if ($this->announcements->hasMorePages())
            <flux:button wire:click="loadMore" variant="subtle" class="self-center">Load more</flux:button>
        @elseif ($this->announcements->isNotEmpty())
            <flux:text class="py-2 text-center text-sm">You're all caught up.</flux:text>
        @endif
    </div>
</div>
