<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-4">
        @php $cards = ['users' => ['Archived Accounts', 'users', 'users'], 'assignments' => ['Archived Assignments', 'assignments', 'briefcase'], 'announcements' => ['Archived Announcements', 'announcements', 'megaphone'], 'logs' => ['Archived Audit Logs', 'logs', 'clipboard-document-list']]; @endphp
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

    @php
        $records = match ($type) {
            'assignments' => $this->archivedAssignments,
            'announcements' => $this->archivedAnnouncements,
            'logs' => $this->archivedAuditLogs,
            default => $this->archivedUsers,
        };
    @endphp

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$type][0] ?? 'Archived Accounts' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search archive..." clearable class="w-full sm:w-64" />
            </div>
        </div>

        @if ($records->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="archive-box" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">Nothing archived here</flux:heading>
                    <flux:text class="mt-2">Archived records will appear here and can be restored.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$records" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                @if ($type === 'assignments')
                    <flux:table.columns>
                        <flux:table.column>Student</flux:table.column>
                        <flux:table.column>Supervisor</flux:table.column>
                        <flux:table.column>Archived</flux:table.column>
                        <flux:table.column align="end">Action</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($records as $assignment)
                            <flux:table.row :key="'archived-assignment-'.$assignment->id">
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$assignment->student?->profile_photo_url"
                                        :name="$assignment->student?->display_name ?? 'Account unavailable'" />
                                    <span class="font-medium">{{ $assignment->student?->display_name ?? 'Account unavailable' }}</span>
                                </flux:table.cell>
                                <flux:table.cell>{{ $assignment->companySupervisor?->display_name ?? 'Unassigned' }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $assignment->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAssignment({{ $assignment->id }})">Restore</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                @elseif ($type === 'announcements')
                    <flux:table.columns>
                        <flux:table.column>Announcement</flux:table.column>
                        <flux:table.column>Archived</flux:table.column>
                        <flux:table.column align="end">Action</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($records as $announcement)
                            <flux:table.row :key="'archived-announcement-'.$announcement->id">
                                <flux:table.cell>
                                    <div class="flex max-w-xl flex-col whitespace-normal">
                                        <span class="font-medium">{{ $announcement->title }}</span>
                                        <span class="line-clamp-1 text-xs text-zinc-500 dark:text-zinc-300">{{ $announcement->message }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $announcement->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAnnouncement({{ $announcement->id }})">Restore</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                @elseif ($type === 'logs')
                    <flux:table.columns>
                        <flux:table.column>Activity</flux:table.column>
                        <flux:table.column>Archived</flux:table.column>
                        <flux:table.column align="end">Action</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($records as $log)
                            <flux:table.row :key="'archived-log-'.$log->id">
                                <flux:table.cell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $log->actor_name ?: 'System' }} · {{ str($log->action)->replace('_', ' ')->title() }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $log->module }} · {{ $log->target_label }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $log->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAuditLog({{ $log->id }})">Restore</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                @else
                    <flux:table.columns>
                        <flux:table.column>Account</flux:table.column>
                        <flux:table.column>Role</flux:table.column>
                        <flux:table.column>Archived</flux:table.column>
                        <flux:table.column align="end">Action</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($records as $user)
                            <flux:table.row :key="'archived-user-'.$user->id">
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar size="md" :src="$user->profile_photo_url" :name="$user->display_name" />
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $user->display_name }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $user->email }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" inset="top bottom">{{ $user->getRoleNames()->join(', ') }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $user->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreUser({{ $user->id }})">Restore</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                @endif
            </flux:table>
        @endif
    </flux:card>
</div>
