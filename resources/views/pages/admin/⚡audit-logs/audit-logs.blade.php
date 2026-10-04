<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="mt-8 space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-semibold">
                All Audit Logs
                <flux:badge color="green" inset="top bottom">{{ $this->auditLogs->total() }}</flux:badge>
            </h2>

            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    aria-label="Search audit logs..." placeholder="Search audit logs..." clearable />

                <flux:select wire:model.live="module" placeholder="All modules" class="min-w-44">
                    <option value="">All modules</option>
                    @foreach ($this->modules as $moduleOption)
                        <option value="{{ $moduleOption }}">{{ $moduleOption }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="action" placeholder="All actions" class="min-w-40">
                    <option value="">All actions</option>
                    @foreach ($this->actions as $actionOption)
                        <option value="{{ $actionOption }}">{{ str($actionOption)->replace('_', ' ')->title() }}
                        </option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if ($this->auditLogs->isEmpty())
            <flux:card class="flex flex-col items-center gap-4 py-24">
                <flux:icon name="clipboard-document-list" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No audit logs found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </flux:card>
        @else
            <flux:table :paginate="$this->auditLogs">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                        wire:click="sort('created_at')">
                        Date & Time
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'actor_name'" :direction="$sortDirection"
                        wire:click="sort('actor_name')">
                        User
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'module'" :direction="$sortDirection"
                        wire:click="sort('module')">
                        Module
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'action'" :direction="$sortDirection"
                        wire:click="sort('action')">
                        Action
                    </flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'target_label'" :direction="$sortDirection"
                        wire:click="sort('target_label')">
                        Target
                    </flux:table.column>

                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->auditLogs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->created_at?->format('M d, Y') }}</span>
                                    <span class="text-xs text-zinc-500">{{ $log->created_at?->format('h:i A') }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->actor_name ?: 'System' }}</span>
                                    <span class="text-xs text-zinc-500">
                                        {{ $log->actor_role ?: '' }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom">
                                    {{ $log->module ?: '' }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($log->action)">
                                    {{ str($log->action)->replace('_', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $log->target_label ?: '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="max-w-md truncate text-sm">
                                    {{ $log->description ?: 'No description provided.' }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:modal.trigger :name="'view-audit-log-'.$log->id">
                                            <flux:menu.item icon="eye">
                                                View
                                            </flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-audit-log-'.$log->id">
                                            <flux:menu.item variant="danger" icon="trash">Delete</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'view-audit-log-'.$log->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:admin.audit-logs.view :log="$log" :key="'view-audit-log-' . $log->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-audit-log-'.$log->id" class="min-w-88">
                                    <livewire:admin.audit-logs.delete :log="$log" :key="'delete-audit-log-' . $log->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
