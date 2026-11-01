<x-layouts::app.plain :title="$requirement->label">
    <flux:main class="mx-auto w-full max-w-7xl px-4 py-6 lg:px-8">
        <div class="mb-4">
            <flux:heading size="xl">{{ $requirement->label }}</flux:heading>
            <flux:text class="mt-1">
                {{ $student->display_name }}{{ $student->studentProfile?->student_id ? ' · '.$student->studentProfile->student_id : '' }}
            </flux:text>
        </div>

        <flux:card class="overflow-auto p-4">
            @if ($extension === 'pdf')
                <iframe src="{{ $fileUrl }}" title="{{ $requirement->label }} preview"
                    class="h-[calc(100vh-13rem)] w-full border-0 bg-white"></iframe>
            @elseif (in_array($extension, ['jpg', 'jpeg', 'png']))
                <div class="flex min-h-[60vh] items-center justify-center">
                    <img src="{{ $fileUrl }}" alt="{{ $requirement->label }}"
                        class="max-h-[calc(100vh-13rem)] max-w-full object-contain">
                </div>
            @elseif ($extension === 'docx')
                <div data-docx-preview data-file-url="{{ $fileUrl }}" aria-live="polite" aria-busy="true"
                    class="min-h-[60vh] overflow-x-auto text-zinc-700 dark:text-zinc-200">
                    <p role="status">Loading document preview…</p>
                </div>
            @else
                {{-- shortcut: legacy .doc needs a server-side converter, add one if previews are required. --}}
                <div class="py-16 text-center">
                    <flux:heading size="lg">Preview unavailable</flux:heading>
                    <flux:text class="mt-2">This legacy Word file can’t be displayed in the browser. Ask the student to upload a PDF or DOCX file.</flux:text>
                </div>
            @endif
        </flux:card>
    </flux:main>
</x-layouts::app.plain>
