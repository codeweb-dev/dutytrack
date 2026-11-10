<div class="flex flex-col gap-6">
    <x-page-header :breadcrumbs="$breadcrumbs" :title="$title" :description="$description" />

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @php
            $cards = ['' => ['Total Reports', 'total', 'document-text'], 'submitted' => ['Submitted', 'submitted', 'check-circle'], 'today' => ['Today', 'today', 'clock'], 'week' => ['This Week', 'week', 'calendar-days']];
        @endphp
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
                <flux:heading size="xl">{{ $cards[$status][0] ?? 'Total Reports' }}</flux:heading>
            </div>

            <div class="flex w-full items-center gap-3 xl:w-auto">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass"
                    placeholder="Search students..." clearable class="w-full sm:w-64" />

                <x-filter-modal name="filter-supervisor-reports" heading="Filter Reports"
                    description="Filter accomplishment reports by status and date."
                    :sorts="['' => 'Default', 'student' => 'Student name', 'student_id' => 'Student ID', 'date' => 'Date', 'hours' => 'Hours']">
                    <flux:radio.group wire:model.live="status" label="Report Status" variant="pills">
                        <flux:radio value="" label="All" />
                        <flux:radio value="submitted" label="Submitted" />
                        <flux:radio value="today" label="Today" />
                        <flux:radio value="week" label="This Week" />
                    </flux:radio.group>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="date" wire:model.live="from_date" label="From Date" />
                        <flux:input type="date" wire:model.live="to_date" label="To Date" />
                    </div>
                </x-filter-modal>
            </div>
        </div>

        @if ($this->submissions->isEmpty())
            <div class="mt-6 flex flex-col items-center gap-4 py-16 sm:py-24">
                <flux:icon name="document-text" class="size-12" />
                <div class="text-center">
                    <flux:heading size="xl">No accomplishment reports found</flux:heading>
                    <flux:text class="mt-2">Try adjusting your search or filter to find what you're looking for.</flux:text>
                </div>
            </div>
        @else
            <flux:table :paginate="$this->submissions" bleed container:class="mt-6"
                pagination:class="px-[var(--flux-bleed,1.5rem)] pb-3">
                <flux:table.columns>
                    <flux:table.column>Student</flux:table.column>
                    <flux:table.column>Student ID</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Accomplishment</flux:table.column>
                    <flux:table.column>Photos</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->submissions as $log)
                        @php
                            $photos = $log->submission?->photoUrls() ?? [];
                            $accomplishment = trim((string) $log->submission?->accomplishment);
                        @endphp

                        <flux:table.row :key="$log->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar size="md" :src="$log->student?->profile_photo_url" :name="$log->student?->display_name" />

                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->student?->display_name ?? '' }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ $log->student?->email ?? '' }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-medium">
                                {{ $log->student?->studentProfile?->student_id ?? '' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span>{{ $log->attendance_date?->format('M d, Y') }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-300">{{ \App\Support\HourFormatter::format($log->hours_rendered) }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="min-w-72 whitespace-normal">
                                @if ($accomplishment !== '')
                                    <span class="line-clamp-2">{{ $accomplishment }}</span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @if (count($photos))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($photos as $url)
                                            <a href="{{ $url }}" target="_blank">
                                                <img src="{{ $url }}" alt="Accomplishment report photo"
                                                    class="size-10 rounded border border-zinc-200 object-cover dark:border-zinc-700">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($accomplishment !== '')
                                    <flux:badge size="sm" color="green" inset="top bottom">Submitted</flux:badge>
                                @else
                                    <flux:badge size="sm" color="amber" inset="top bottom">Missing</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
