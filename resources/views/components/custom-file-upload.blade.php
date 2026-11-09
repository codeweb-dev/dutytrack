@props([
    'model' => 'photo',
    'label' => 'Upload file',
    'accept' => 'image/*',
    'capture' => null,
    'preview' => true,
    'existing' => null,
    'required' => false,
])

@php
    $file = data_get($this, $model);
@endphp

<div x-data="{
    isDropping: false,
    isUploading: false,
    progress: 0,
}" x-on:dragover.prevent="isDropping = true" x-on:dragleave.prevent="isDropping = false"
    x-on:drop.prevent="isDropping = false" x-on:livewire-upload-start="isUploading = true; progress = 0"
    x-on:livewire-upload-finish="isUploading = false; progress = 100" x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress" class="w-full">
    <label for="{{ str_replace('.', '_', $model) }}"
        class="relative flex min-h-56 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-white p-6 text-center transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/60"
        :class="isDropping ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/20' : ''">
        <input id="{{ str_replace('.', '_', $model) }}" type="file" accept="{{ $accept }}"
            @if ($capture) capture="{{ $capture }}" @endif wire:model="{{ $model }}" class="hidden" />

        @if ($preview && $file)
            <div class="flex flex-col items-center gap-4">
                @if (str_starts_with($file->getMimeType(), 'image/'))
                    <img src="{{ $file->temporaryUrl() }}" alt="Preview"
                        class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow dark:ring-zinc-800" />
                @else
                    <div
                        class="flex h-24 w-24 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm0 0v6h6" />
                        </svg>
                    </div>
                @endif

                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                        {{ $file->getClientOriginalName() }}
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Click to replace @unless ($required)<flux:badge size="sm">Optional</flux:badge>@endunless
                    </p>
                </div>
            </div>
        @elseif ($preview && $existing)
            <div class="flex flex-col items-center gap-4">
                <img src="{{ $existing }}" alt="Current image"
                    class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow dark:ring-zinc-800" />

                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                        Current photo
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Click to replace @unless ($required)<flux:badge size="sm">Optional</flux:badge>@endunless
                    </p>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center gap-4">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-full border border-zinc-300 bg-zinc-100 text-zinc-500 transition dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 16a1 1 0 0 1-1-1V9.41l-1.3 1.3a1 1 0 1 1-1.4-1.42l3-3a1 1 0 0 1 1.4 0l3 3a1 1 0 1 1-1.4 1.42L13 9.41V15a1 1 0 0 1-1 1Z" />
                        <path
                            d="M5 20a3 3 0 0 1-3-3v-1a1 1 0 1 1 2 0v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1a1 1 0 1 1 2 0v1a3 3 0 0 1-3 3Z" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $label }}</p>
                        @unless ($required)
                            <flux:badge size="sm">Optional</flux:badge>
                        @endunless
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        Drag and drop here, or click to browse
                    </p>
                </div>
            </div>
        @endif
    </label>

    <div x-show="isUploading" class="mt-3">
        <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-black transition-all dark:bg-white" :style="`width: ${progress}%`"></div>
        </div>
        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400" x-text="`Uploading... ${progress}%`"></p>
    </div>

    @error($model)
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
