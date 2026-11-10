<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <flux:card>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <flux:heading size="xl">Daily Reports</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <x-filter-modal name="filter-student-reports" heading="Filter Accomplishment Report"
                    description="Filter by date range and sort your entries. The download uses the same date range."
                    :sorts="['' => 'Default', 'date' => 'Date', 'hours' => 'Hours']">
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="date" wire:model.live="from_date" label="From Date" />
                        <flux:input type="date" wire:model.live="to_date" label="To Date" />
                    </div>
                </x-filter-modal>

                <flux:button icon="arrow-down-tray" variant="primary" :href="$this->downloadUrl()" target="_blank">
                    Download Docs
                </flux:button>
            </div>
        </div>

        @if ($this->completedAttendances->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="document-text" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No reports found</flux:heading>
                    <flux:text class="mt-2">Completed attendance days show up here. Try adjusting the date range if you're filtering.
                    </flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->completedAttendances" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Morning</flux:table.column>
                    <flux:table.column>Afternoon</flux:table.column>
                    <flux:table.column>Hours</flux:table.column>
                    <flux:table.column>Accomplishment</flux:table.column>
                    <flux:table.column>Photos</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->completedAttendances as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $log->attendance_date?->format('M d, Y') }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $log->morning_time_in?->format('h:i A') }} -
                                {{ $log->morning_time_out?->format('h:i A') }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $log->afternoon_time_in?->format('h:i A') }} -
                                {{ $log->afternoon_time_out?->format('h:i A') }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ \App\Support\HourFormatter::format($log->hours_rendered) }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($log->submission?->accomplishment)
                                    {{ \Illuminate\Support\Str::limit($log->submission->accomplishment, 80) }}
                                @else
                                    <flux:badge size="sm" color="amber" inset="top bottom">Not submitted</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm"
                                    color="{{ count($log->submission?->photo_paths ?? []) ? 'lime' : 'zinc' }}"
                                    inset="top bottom">
                                    {{ count($log->submission?->photo_paths ?? []) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom" />

                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square"
                                            wire:click="openSubmissionModal({{ $log->id }})">
                                            {{ $log->submission ? 'Edit Report' : 'Submit Report' }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <flux:modal name="student-report-submission" class="md:w-2xl">
        <form wire:submit="saveSubmission" class="space-y-6">
            <div>
                <flux:heading size="lg">Daily Accomplishment Report</flux:heading>
                <flux:text class="mt-2">Add the accomplishment and photos for this completed attendance day.
                </flux:text>
            </div>

            <flux:textarea wire:model="accomplishment" label="Accomplishment" rows="5" required
                placeholder="Describe the tasks or activities completed for this day." />

            <flux:textarea wire:model="skills_learned" label="Skills Learned" rows="3" required
                placeholder="What skills or knowledge did you gain this day?" />

            @php
                $selectedLog = $editingAttendanceId
                    ? $this->completedAttendances->getCollection()->firstWhere('id', $editingAttendanceId)
                    : null;
            @endphp

            {{-- Saved photos already satisfy the requirement, so only force an upload when there are none. --}}
            <flux:input wire:model="new_photos" type="file" label="Photos" :required="! count($selectedLog?->submission?->photoUrls() ?? [])"
                multiple accept="image/*" />

            @if ($editingAttendanceId)
                @if ($selectedLog?->submission && count($selectedLog->submission->photoUrls()))
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($selectedLog->submission->photoUrls() as $url)
                            <img src="{{ $url }}" alt="Accomplishment report photo"
                                class="aspect-video w-full rounded border border-zinc-200 object-cover dark:border-zinc-700">
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">Save Report</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
