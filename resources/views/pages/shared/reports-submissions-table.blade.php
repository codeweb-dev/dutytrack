<flux:table :paginate="$logs">
    <flux:table.columns>
        <flux:table.column>Student</flux:table.column>
        <flux:table.column>Student ID</flux:table.column>
        <flux:table.column>Date</flux:table.column>
        <flux:table.column>Hours</flux:table.column>
        <flux:table.column>Accomplishment</flux:table.column>
        <flux:table.column>Photos</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($logs as $log)
            <flux:table.row :key="$log->id">
                <flux:table.cell>{{ $log->student?->display_name ?? '' }}</flux:table.cell>
                <flux:table.cell>{{ $log->student?->studentProfile?->student_id ?? '' }}</flux:table.cell>
                <flux:table.cell>{{ $log->attendance_date?->format('M d, Y') }}</flux:table.cell>
                <flux:table.cell>{{ \App\Support\HourFormatter::format($log->hours_rendered) }}</flux:table.cell>
                <flux:table.cell>{{ \Illuminate\Support\Str::limit($log->submission?->accomplishment ?? '', 120) }}</flux:table.cell>
                <flux:table.cell>
                    @if ($log->submission && count($log->submission->photoUrls()))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($log->submission->photoUrls() as $url)
                                <a href="{{ $url }}" target="_blank">
                                    <img src="{{ $url }}" alt="Accomplishment report photo"
                                        class="size-12 rounded border border-zinc-200 object-cover dark:border-zinc-700">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <span class="text-zinc-400"></span>
                    @endif
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="py-8 text-center text-zinc-500">
                    No accomplishment reports found.
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
