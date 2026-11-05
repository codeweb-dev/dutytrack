<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <section class="space-y-4">
        <div class="flex w-full flex-wrap items-center justify-end gap-3">
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

        <flux:table :paginate="$this->completedAttendances">
            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Morning</flux:table.column>
                <flux:table.column>Afternoon</flux:table.column>
                <flux:table.column>Hours</flux:table.column>
                <flux:table.column>Accomplishment</flux:table.column>
                <flux:table.column>Photos</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->completedAttendances as $log)
                    <flux:table.row :key="$log->id">
                        <flux:table.cell>{{ $log->attendance_date?->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $log->morning_time_in?->format('h:i A') }} -
                            {{ $log->morning_time_out?->format('h:i A') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $log->afternoon_time_in?->format('h:i A') }} -
                            {{ $log->afternoon_time_out?->format('h:i A') }}
                        </flux:table.cell>
                        <flux:table.cell>{{ \App\Support\HourFormatter::format($log->hours_rendered) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ \Illuminate\Support\Str::limit($log->submission?->accomplishment ?? '', 80) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm"
                                color="{{ count($log->submission?->photo_paths ?? []) ? 'lime' : 'zinc' }}"
                                inset="top bottom">
                                {{ count($log->submission?->photo_paths ?? []) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" icon="pencil-square"
                                wire:click="openSubmissionModal({{ $log->id }})">
                                Submit
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-8 text-center text-zinc-500">
                            No completed attendance records found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </section>

    <flux:modal name="student-report-submission" class="md:w-2xl">
        <form wire:submit="saveSubmission" class="space-y-6">
            <div>
                <flux:heading size="lg">Daily Submission</flux:heading>
                <flux:text class="mt-2">Add the accomplishment and photos for this completed attendance day.
                </flux:text>
            </div>

            <flux:textarea wire:model="accomplishment" label="Accomplishment" rows="5" badge="Optional"
                placeholder="Describe the tasks or activities completed for this day." />

            <flux:input wire:model="new_photos" type="file" label="Photos" badge="Optional" multiple accept="image/*" />

            @if ($editingAttendanceId)
                @php
                    $selectedLog = $this->completedAttendances->getCollection()->firstWhere('id', $editingAttendanceId);
                @endphp

                @if ($selectedLog?->submission && count($selectedLog->submission->photoUrls()))
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($selectedLog->submission->photoUrls() as $url)
                            <img src="{{ $url }}" alt="Submission photo"
                                class="aspect-video w-full rounded border border-zinc-200 object-cover dark:border-zinc-700">
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">Save Submission</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
