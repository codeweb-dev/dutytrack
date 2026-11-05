<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="space-y-3">
        <flux:heading size="lg">Accounts</flux:heading>
        @if ($this->archivedUsers->isEmpty())
            <flux:text>No archived accounts.</flux:text>
        @else
            <flux:table :paginate="$this->archivedUsers">
                <flux:table.columns>
                    <flux:table.column>Account</flux:table.column>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column>Archived</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->archivedUsers as $user)
                        <flux:table.row :key="'archived-user-'.$user->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $user->display_name }}</div>
                                <div class="text-xs text-zinc-500">{{ $user->email }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->getRoleNames()->join(', ') }}</flux:table.cell>
                            <flux:table.cell>{{ $user->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreUser({{ $user->id }})">Restore</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>

    <section class="space-y-3">
        <flux:heading size="lg">Assignments</flux:heading>
        @if ($this->archivedAssignments->isEmpty())
            <flux:text>No archived assignments.</flux:text>
        @else
            <flux:table :paginate="$this->archivedAssignments">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Archived</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->archivedAssignments as $assignment)
                        <flux:table.row :key="'archived-assignment-'.$assignment->id">
                            <flux:table.cell>{{ $assignment->student?->display_name ?? 'Account unavailable' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->companySupervisor?->display_name ?? 'Unassigned' }}</flux:table.cell>
                            <flux:table.cell>{{ $assignment->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAssignment({{ $assignment->id }})">Restore</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>

    <section class="space-y-3">
        <flux:heading size="lg">Announcements</flux:heading>
        @if ($this->archivedAnnouncements->isEmpty())
            <flux:text>No archived announcements.</flux:text>
        @else
            <flux:table :paginate="$this->archivedAnnouncements">
                <flux:table.columns>
                    <flux:table.column>Announcement</flux:table.column>
                    <flux:table.column>Archived</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->archivedAnnouncements as $announcement)
                        <flux:table.row :key="'archived-announcement-'.$announcement->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $announcement->title }}</div>
                                <div class="max-w-xl truncate text-xs text-zinc-500">{{ $announcement->message }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $announcement->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAnnouncement({{ $announcement->id }})">Restore</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>

    <section class="space-y-3">
        <flux:heading size="lg">Audit Logs</flux:heading>
        @if ($this->archivedAuditLogs->isEmpty())
            <flux:text>No archived audit logs.</flux:text>
        @else
            <flux:table :paginate="$this->archivedAuditLogs">
                <flux:table.columns>
                    <flux:table.column>Activity</flux:table.column>
                    <flux:table.column>Archived</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->archivedAuditLogs as $log)
                        <flux:table.row :key="'archived-log-'.$log->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $log->actor_name ?: 'System' }} · {{ str($log->action)->replace('_', ' ')->title() }}</div>
                                <div class="text-xs text-zinc-500">{{ $log->module }} · {{ $log->target_label }}</div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $log->deleted_at?->format('M d, Y h:i A') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" icon="arrow-uturn-left" wire:click="restoreAuditLog({{ $log->id }})">Restore</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </section>
</div>
