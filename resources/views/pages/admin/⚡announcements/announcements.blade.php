<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                All Announcements
                <flux:badge color="red" inset="top bottom">{{ $this->announcements->total() }}</flux:badge>
            </h2>

            <div class="flex items-center gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search announcements..." clearable />

                <livewire:admin.announcements.create />
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 mb-12">
            @forelse ($this->announcements as $announcement)
                <flux:card class="space-y-4 hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm text-zinc-400">
                                {{ $announcement->created_at?->format('M d, Y') }}
                            </div>

                            <div class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ $announcement->title }}
                            </div>
                        </div>

                        <flux:badge :color="$this->badgeColor($announcement->type)" inset="top bottom">
                            {{ ucfirst($announcement->type) }}
                        </flux:badge>
                    </div>

                    <div class="text-sm text-zinc-600 dark:text-zinc-300 line-clamp-4">
                        {{ $announcement->message }}
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs text-zinc-400">
                            {{ $announcement->created_at?->diffForHumans() }}
                        </flux:text>

                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                            <flux:menu>
                                <flux:modal.trigger :name="'view-announcement-'.$announcement->id">
                                    <flux:menu.item icon="eye">View</flux:menu.item>
                                </flux:modal.trigger>

                                <flux:modal.trigger :name="'edit-announcement-'.$announcement->id">
                                    <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                </flux:modal.trigger>

                                <flux:menu.separator />

                                <flux:modal.trigger :name="'delete-announcement-'.$announcement->id">
                                    <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                </flux:modal.trigger>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    <flux:modal :name="'view-announcement-'.$announcement->id" flyout variant="floating"
                        class="md:w-2xl">
                        <livewire:admin.announcements.view :announcement="$announcement" :key="'view-announcement-' . $announcement->id" />
                    </flux:modal>

                    <flux:modal :name="'edit-announcement-'.$announcement->id" flyout variant="floating"
                        class="md:w-2xl">
                        <livewire:admin.announcements.edit :announcement="$announcement" :key="'edit-announcement-' . $announcement->id" />
                    </flux:modal>

                    <flux:modal :name="'delete-announcement-'.$announcement->id" class="md:w-xl">
                        <livewire:admin.announcements.delete :announcement="$announcement" :key="'delete-announcement-' . $announcement->id" />
                    </flux:modal>

                </flux:card>
            @empty
                <div class="col-span-full">
                    <flux:card class="flex flex-col items-center justify-center py-12 text-center space-y-3">
                        <flux:icon name="megaphone" class="h-10 w-10 text-zinc-400" />

                        <flux:heading size="md">No announcements yet</flux:heading>

                        <flux:text>
                            Create your first announcement to inform users.
                        </flux:text>
                    </flux:card>
                </div>
            @endforelse
        </div>

        <div>
            <flux:pagination :paginator="$this->announcements" />
        </div>
    </div>
</div>
