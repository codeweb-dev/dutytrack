<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-5">
        @php($cards = ['' => ['Total Announcements', 'total', 'megaphone'], 'info' => ['Info', 'info', 'information-circle'], 'warning' => ['Warning', 'warning', 'exclamation-triangle'], 'urgent' => ['Urgent', 'urgent', 'bell-alert'], 'maintenance' => ['Maintenance', 'maintenance', 'wrench-screwdriver']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('type', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $type === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$type === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $type === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$type][0] ?? 'Total Announcements' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search announcements..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-announcements" heading="Filter Announcements"
                    description="Filter by type and sort announcements."
                    :sorts="['created_at' => 'Date posted', 'title' => 'Title', 'type' => 'Type']">
                    <flux:radio.group wire:model.live="type" label="Type" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="info" label="Info" />
                        <flux:radio value="warning" label="Warning" />
                        <flux:radio value="urgent" label="Urgent" />
                        <flux:radio value="maintenance" label="Maintenance" />
                    </flux:radio.group>
                </x-filter-modal>

                <livewire:coordinator.announcements.create />
            </div>
        </div>

        @if ($this->announcements->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="megaphone" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No announcements found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter, or create a new announcement.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->announcements" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Announcement</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Date Posted</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->announcements as $announcement)
                        <flux:table.row :key="$announcement->id">
                            <flux:table.cell>
                                <div class="flex max-w-xl flex-col whitespace-normal">
                                    <span class="font-medium">{{ $announcement->title }}</span>
                                    <span class="line-clamp-1 text-xs text-zinc-500 dark:text-zinc-300">{{ $announcement->message }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($announcement->type)" inset="top bottom">
                                    {{ ucfirst($announcement->type) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $announcement->created_at?->format('M d, Y') }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $announcement->created_at?->diffForHumans() }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:modal.trigger :name="'view-announcement-'.$announcement->id">
                                            <flux:menu.item icon="eye">View</flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:modal.trigger :name="'edit-announcement-'.$announcement->id">
                                            <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-announcement-'.$announcement->id">
                                            <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'view-announcement-'.$announcement->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.announcements.view :announcement="$announcement" :key="'view-announcement-' . $announcement->id" />
                                </flux:modal>

                                <flux:modal :name="'edit-announcement-'.$announcement->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.announcements.edit :announcement="$announcement" :key="'edit-announcement-' . $announcement->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-announcement-'.$announcement->id" class="md:w-xl">
                                    <livewire:coordinator.announcements.delete :announcement="$announcement" :key="'delete-announcement-' . $announcement->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
