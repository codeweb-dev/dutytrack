<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 grid-cols-2 xl:grid-cols-5">
        @php $cards = ['' => ['Total Records', 'total', 'clipboard-document-list'], 'present' => ['Present', 'present', 'check-circle'], 'late' => ['Late', 'late', 'clock'], 'absent' => ['Absent', 'absent', 'x-circle'], 'incomplete' => ['Incomplete', 'incomplete', 'exclamation-circle']]; @endphp
        @foreach ($cards as $value => [$label, $key, $icon])
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="group text-left"
                aria-pressed="{{ $status === $value ? 'true' : 'false' }}">
                <flux:card size="xs" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 group-aria-pressed:border-zinc-800 group-aria-pressed:outline-1 group-aria-pressed:outline-zinc-800 dark:group-aria-pressed:border-white dark:group-aria-pressed:outline-white">
                    <flux:heading class="flex items-center gap-2">{{ $label }}
                        <flux:icon :name="$icon" :variant="$status === $value ? 'solid' : 'outline'"
                            class="ml-auto size-5 {{ $status === $value ? 'text-zinc-800 dark:text-white' : 'text-zinc-400' }}" />
                    </flux:heading>
                    <flux:heading size="2xl" class="mt-2">{{ $this->summary[$key] }}</flux:heading>
                </flux:card>
            </button>
        @endforeach
    </div>

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Records' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search student..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-coordinator-attendance" heading="Filter Attendance Records"
                    description="Filter and sort logs by OJT type, status, supervisor, and date range." width="md:w-2xl"
                    :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'date' => 'Date', 'hours' => 'Hours', 'status' => 'Status']">
                    <flux:radio.group wire:model.live="ojt_type" label="OJT Type" variant="cards" :indicator="false"
                        class="max-sm:flex-col">
                        <flux:radio value="" icon="squares-2x2" label="All Types" description="All OJT types" />
                        <flux:radio value="internal" icon="academic-cap" label="Internal" description="Internal OJT" />
                        <flux:radio value="external" icon="building-office" label="External" description="External OJT" />
                    </flux:radio.group>

                    <flux:radio.group wire:model.live="status" label="Status" variant="pills">
                        <flux:radio value="" label="All Status" />
                        <flux:radio value="present" label="Present" />
                        <flux:radio value="late" label="Late" />
                        <flux:radio value="absent" label="Absent" />
                        <flux:radio value="incomplete" label="Incomplete" />
                    </flux:radio.group>

                    <flux:select wire:model.live="supervisor_id" placeholder="Supervisor" label="Supervisor">
                        <option value="">All Supervisors</option>
                        @foreach ($this->supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}">
                                {{ $supervisor->display_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                        <flux:input type="date" wire:model.live="from_date" label="From Date" />
                        <flux:input type="date" wire:model.live="to_date" label="To Date" />
                    </div>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->attendanceLogs->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="clipboard-document-list" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No attendance logs found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find student attendance.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->attendanceLogs" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>OJT Type</flux:table.column>
                    <flux:table.column>Supervisor</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Morning In</flux:table.column>
                    <flux:table.column>Morning Out</flux:table.column>
                    <flux:table.column>Afternoon In</flux:table.column>
                    <flux:table.column>Afternoon Out</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->attendanceLogs as $log)
                        @php
                            $assignment = $log->assignment;
                            $supervisor =
                                $assignment?->companySupervisor;
                        @endphp

                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$log->student?->profile_photo_url"
                                    :name="$log->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">
                                        {{ $log->student?->display_name ?? '' }}
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">
                                        {{ $log->student?->email }}
                                    </span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <x-ojt-type-badge :type="$assignment?->ojt_type" inset="top bottom" />
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $supervisor?->display_name ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->attendance_date?->format('M d, Y') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayMorningTimeIn()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayMorningTimeOut()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayAfternoonTimeIn()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $log->displayAfternoonTimeOut()?->format('h:i A') ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ \App\Support\HourFormatter::format($log->hours_rendered) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($log->timeInMapUrl())
                                    <flux:button size="sm" variant="ghost" icon="map" :href="$log->timeInMapUrl()"
                                        target="_blank" />
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$this->badgeColor($log->status)" inset="top bottom">
                                    {{ ucfirst($log->status ?? 'recorded') }}
                                </flux:badge>
                                @if ($log->isLate() && $log->status !== 'late')
                                    <flux:badge size="sm" color="amber" inset="top bottom">Late</flux:badge>
                                @endif
                            </flux:table.cell>

                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
