<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-4">
        @php($cards = ['' => ['Total Logs', 'total', 'clipboard-document-list'], 'today' => ['Today', 'today', 'clock'], 'week' => ['This Week', 'week', 'calendar-days'], 'month' => ['This Month', 'month', 'calendar']])
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('period', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $period === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$period === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $period === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$period][0] ?? 'Total Logs' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search audit logs..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-audit-logs" heading="Filter Audit Logs"
                    description="Filter by module and action, and sort the audit trail."
                    :sorts="['created_at' => 'Date & time', 'actor_name' => 'User', 'module' => 'Module', 'action' => 'Action', 'target_label' => 'Target']">
                    <flux:radio.group wire:model.live="period" label="Period" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="today" label="Today" />
                        <flux:radio value="week" label="This Week" />
                        <flux:radio value="month" label="This Month" />
                    </flux:radio.group>

                    <flux:select wire:model.live="module" label="Module">
                        <option value="">All modules</option>
                        @foreach ($this->modules as $moduleOption)
                            <option value="{{ $moduleOption }}">{{ $moduleOption }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="action" label="Action">
                        <option value="">All actions</option>
                        @foreach ($this->actions as $actionOption)
                            <option value="{{ $actionOption }}">{{ str($actionOption)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </flux:select>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->auditLogs->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="clipboard-document-list" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No audit logs found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking
                        for.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->auditLogs" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>
                        Date & Time
                    </flux:table.column>

                    <flux:table.column>
                        User
                    </flux:table.column>

                    <flux:table.column>
                        Module
                    </flux:table.column>

                    <flux:table.column>
                        Action
                    </flux:table.column>

                    <flux:table.column>
                        Target
                    </flux:table.column>

                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->auditLogs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->created_at?->format('M d, Y') }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $log->created_at?->format('h:i A') }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->actor_name ?: 'System' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">
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
                                <flux:badge size="sm" inset="top bottom" :color="\App\Support\BadgeColor::for($log->action)">
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

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" tooltip="Actions" aria-label="Actions"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:modal.trigger :name="'view-audit-log-'.$log->id">
                                            <flux:menu.item icon="eye">
                                                View
                                            </flux:menu.item>
                                        </flux:modal.trigger>

                                        <flux:menu.separator />

                                        <flux:modal.trigger :name="'delete-audit-log-'.$log->id">
                                            <flux:menu.item icon="archive-box">Archive</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>

                                <flux:modal :name="'view-audit-log-'.$log->id" flyout variant="floating"
                                    class="md:w-2xl">
                                    <livewire:coordinator.audit-logs.view :log="$log" :key="'view-audit-log-' . $log->id" />
                                </flux:modal>

                                <flux:modal :name="'delete-audit-log-'.$log->id" class="w-[calc(100vw-2rem)] max-w-sm!">
                                    <livewire:coordinator.audit-logs.delete :log="$log" :key="'delete-audit-log-' . $log->id" />
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
