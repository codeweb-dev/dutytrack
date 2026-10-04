<?php

use App\Models\Announcement;
use Livewire\Component;

new class extends Component {
    public string $search = '';
    public string $type = 'all';

    public function announcements()
    {
        return Announcement::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', $search)->orWhere('message', 'like', $search)->orWhere('type', 'like', $search);
                });
            })
            ->when($this->type !== 'all', function ($query) {
                $query->where('type', $this->type);
            })
            ->latest()
            ->take(6)
            ->get();
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

    public function cardClasses(string $type): string
    {
        return match ($type) {
            'warning' => 'border-amber-500/40 bg-amber-500/10',
            'urgent' => 'border-red-500/40 bg-red-500/10',
            'maintenance' => 'border-sky-500/40 bg-sky-500/10',
            default => 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-900/70',
        };
    }

    public function iconColor(string $type): string
    {
        return match ($type) {
            'warning' => 'text-amber-500',
            'urgent' => 'text-red-500',
            'maintenance' => 'text-sky-500',
            default => 'text-zinc-400',
        };
    }
};
?>

<div class="mx-auto w-full max-w-4xl space-y-5">
    <div class="space-y-3">
        <div>
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                System Announcements
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Stay updated with the latest notices, reminders, and important system-wide announcements.
            </p>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                aria-label="Search announcements..." placeholder="Search announcements..." clearable class="flex-1" />

            <flux:select wire:model.live="type" class="md:w-52">
                <option value="all">All Types</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="urgent">Urgent</option>
                <option value="maintenance">Maintenance</option>
            </flux:select>
        </div>
    </div>

    <div class="space-y-4 mt-6">
        @forelse ($this->announcements() as $announcement)
            <div class="rounded-3xl border p-5 shadow-sm transition {{ $this->cardClasses($announcement->type) }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 {{ $this->iconColor($announcement->type) }}">
                                <flux:icon name="megaphone" class="h-5 w-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold leading-tight text-zinc-900 dark:text-white">
                                        {{ $announcement->title }}
                                    </h3>

                                    <flux:badge :color="$this->badgeColor($announcement->type)" size="sm"
                                        inset="top bottom">
                                        {{ ucfirst($announcement->type) }}
                                    </flux:badge>
                                </div>

                                <p class="mt-2 line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                                    {{ $announcement->message }}
                                </p>

                                <div class="mt-3 text-xs text-zinc-400">
                                    Posted {{ $announcement->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="rounded-3xl border border-dashed border-zinc-300 bg-zinc-50/70 px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900/60">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-200 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon name="megaphone" class="h-6 w-6" />
                </div>

                <h3 class="mt-4 text-base font-semibold text-zinc-900 dark:text-white">
                    No announcements found
                </h3>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Try changing your search or filter to see more results. <br />
                    Or check back later for new announcements!
                </p>
            </div>
        @endforelse
    </div>
</div>
