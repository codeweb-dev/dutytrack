<?php

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Announcements')] #[Layout('layouts::announcement')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $type = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->announcements);
    }

    public function updatedType(): void
    {
        $this->resetPage();
        unset($this->announcements);
    }

    #[Computed]
    public function announcements(): LengthAwarePaginator
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
            ->paginate(6);
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
            default => 'megaphone',
        };
    }

    public function iconWrapperClasses(string $type): string
    {
        return match ($type) {
            'warning' => 'bg-amber-500 text-white',
            'urgent' => 'bg-red-500 text-white',
            'maintenance' => 'bg-sky-500 text-white',
            default => 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900',
        };
    }

    public function cardClasses(string $type): string
    {
        return match ($type) {
            'warning' => 'border-amber-500/20 bg-amber-500/[0.08]',
            'urgent' => 'border-red-500/20 bg-red-500/[0.08]',
            'maintenance' => 'border-sky-500/20 bg-sky-500/[0.08]',
            default => 'border-zinc-200/80 bg-white dark:border-zinc-800 dark:bg-zinc-900',
        };
    }

    public function introLabel(string $type): string
    {
        return match ($type) {
            'warning' => 'Heads-up for ongoing activity',
            'urgent' => 'Action may be needed soon',
            'maintenance' => 'Platform update and schedule',
            default => 'General platform update',
        };
    }

    public function canManageAnnouncements(): bool
    {
        return Auth::user()?->hasRole('admin') ?? false;
    }
};
?>

<div class="min-h-full">
    <section class="mx-auto flex w-full max-w-7xl flex-col gap-10 pb-16">
        <div class="flex justify-end">
            @if ($this->canManageAnnouncements())
                <flux:button :href="route('admin.announcements')" wire:navigate variant="subtle" icon="cog-6-tooth">
                    Manage Announcements
                </flux:button>
            @endif
        </div>

        <div class="flex justify-start">
            <flux:button :href="route('dashboard')" icon="arrow-left" wire:navigate>
                Go back to dashboard
            </flux:button>
        </div>

        <div class="mx-auto flex max-w-3xl flex-col items-center text-center">
            <div class="text-sm font-medium tracking-[0.28em] text-red-500 uppercase">
                The Changelog
            </div>

            <h1
                class="mt-6 text-balance text-4xl font-semibold tracking-tight text-zinc-950 sm:text-5xl dark:text-white">
                What's new around here?
            </h1>

            <p class="mt-4 max-w-2xl text-lg leading-8 text-zinc-500 dark:text-zinc-400">
                Succinct and informative updates about DutyTrack, from reminders and urgent notices to maintenance
                windows and platform improvements.
            </p>
        </div>

        <flux:separator class="mx-auto w-full max-w-7xl my-6" />

        <div class="mx-auto max-w-7xl w-full">
            <div class="flex justify-end gap-4 flex-col md:flex-row md:items-center">
                <div>
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                        aria-label="Search announcements..." placeholder="Search announcements..." clearable class="flex-1" />
                </div>

                <flux:select wire:model.live="type" class="md:w-32">
                    <option value="all">All Types</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="urgent">Urgent</option>
                    <option value="maintenance">Maintenance</option>
                </flux:select>
            </div>
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-800"></div>

        <div class="flex flex-col gap-14">
            @forelse ($this->announcements as $announcement)
                <div>
                    <article class="grid gap-6 lg:grid-cols-[200px_minmax(0,1fr)] lg:gap-10">
                        <div class="pt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $announcement->created_at?->format('F j, Y') }}
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                        {{ $announcement->title }}
                                    </h2>

                                    <flux:badge :color="$this->badgeColor($announcement->type)" inset="top bottom">
                                        {{ ucfirst($announcement->type) }}
                                    </flux:badge>
                                </div>

                                <flux:text>
                                    {{ $this->introLabel($announcement->type) }}
                                </flux:text>
                            </div>

                            <flux:card class="overflow-hidden {{ $this->cardClasses($announcement->type) }}">
                                <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                                    <div class="flex items-center gap-4 sm:w-56 sm:flex-col sm:items-start sm:gap-5">
                                        <div
                                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $this->iconWrapperClasses($announcement->type) }}">
                                            <flux:icon :name="$this->iconName($announcement->type)" class="h-6 w-6" />
                                        </div>

                                        <div class="space-y-1">
                                            <flux:heading>
                                                Posted {{ $announcement->created_at?->diffForHumans() }}
                                            </flux:heading>
                                            <flux:text>
                                                System-wide visibility
                                            </flux:text>
                                        </div>
                                    </div>

                                    <flux:card class="min-w-0 flex-1">
                                        <div class="space-y-5">
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-zinc-500 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-800">
                                                    <flux:icon name="sparkles" class="h-5 w-5" />
                                                </div>

                                                <div class="min-w-0">
                                                    <flux:text>
                                                        {{ $announcement->message }}
                                                    </flux:text>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-zinc-500 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-800">
                                                    <flux:icon name="clock" class="h-5 w-5" />
                                                </div>

                                                <div class="min-w-0">
                                                    <flux:text>
                                                        Published on
                                                        {{ $announcement->created_at?->format('M d, Y \a\t h:i A') }}.
                                                        Check this page regularly for new announcements that affect your
                                                        OJT workflow, monitoring schedule, and submissions.
                                                    </flux:text>
                                                </div>
                                            </div>
                                        </div>
                                    </flux:card>
                                </div>
                            </flux:card>
                        </div>
                    </article>

                    @unless ($loop->last)
                        <flux:separator class="mt-24" variant="subtle" />
                    @endunless
                </div>
            @empty
                <flux:card class="mx-auto flex w-full max-w-4xl flex-col items-center py-16">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-zinc-500 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-800">
                        <flux:icon name="megaphone" class="h-8 w-8" />
                    </div>

                    <flux:heading class="mt-6" size="xl">
                        No announcements found
                    </flux:heading>

                    <flux:text class="mt-3">
                        Try adjusting your search or filter, or check back later for new platform updates and official
                        notices.
                    </flux:text>
                </flux:card>
            @endforelse
        </div>

        @if ($this->announcements->hasPages())
            <div class="pt-2">
                <flux:pagination :paginator="$this->announcements" />
            </div>
        @endif
    </section>
</div>
