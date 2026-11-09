<?php

use App\Livewire\Concerns\ManagesResumeImage;
use Livewire\Component;

new class extends Component
{
    use ManagesResumeImage;

    protected string $type = 'photo';

    protected function uploadRules(): array
    {
        return ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120', 'dimensions:min_width=200,min_height=200'];
    }
};
?>

@php($url = $this->imageUrl())

<div x-data="photoCropper" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
    <div class="flex items-center gap-4">
        <div class="size-24 shrink-0 overflow-hidden rounded-md border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
            @if ($url)
                <img src="{{ $url }}" alt="Your 2x2 ID photo" class="size-full object-cover">
            @else
                <div class="flex size-full flex-col items-center justify-center gap-1 text-xs text-zinc-400">
                    <flux:icon name="user" class="size-6" />
                    2x2
                </div>
            @endif
        </div>

        <div class="min-w-0 space-y-2">
            <div>
                <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100">2x2 ID Photo</div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400">JPG or PNG up to 5 MB. You can crop and position it before saving.</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button size="sm" icon="arrow-up-tray" x-on:click="$refs.file.click()">
                    {{ $url ? 'Replace' : 'Upload photo' }}
                </flux:button>
                @if ($url)
                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="removeImage" wire:confirm="Remove your ID photo from this resume?">
                        Remove
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    <input x-ref="file" type="file" accept="image/png,image/jpeg" class="sr-only" tabindex="-1" aria-label="Choose an ID photo"
        x-on:change.stop="pick($event)" x-on:input.stop>

    <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-red-500" role="alert"></p>
    @error('upload')
        <p class="mt-2 text-sm text-red-500" role="alert">{{ $message }}</p>
    @enderror

    <div x-show="src" x-cloak x-transition.opacity class="mt-4 space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Crop &amp; position</div>

        <div x-ref="frame" tabindex="0" role="application"
            aria-label="Photo crop area. Drag, or use the arrow keys to reposition and plus or minus to zoom."
            class="relative mx-auto size-56 cursor-grab touch-none overflow-hidden rounded-md bg-zinc-200 outline-none focus-visible:ring-2 focus-visible:ring-red-500 active:cursor-grabbing dark:bg-zinc-800"
            x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="end($event)" x-on:pointercancel="end($event)"
            x-on:keydown="nudge($event)" x-on:wheel.prevent="zoomBy(-$event.deltaY / 400)">
            <img :src="src" alt="" draggable="false" class="pointer-events-none absolute top-1/2 left-1/2 max-w-none select-none" :style="imageStyle">
            {{-- Rule-of-thirds guides help centre the face. --}}
            <div class="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3">
                @for ($i = 0; $i < 9; $i++)
                    <div class="border border-white/25"></div>
                @endfor
            </div>
        </div>

        <label class="mx-auto flex max-w-56 items-center gap-2 text-zinc-500">
            <flux:icon name="magnifying-glass-minus" class="size-4" />
            <span class="sr-only">Zoom</span>
            <input type="range" min="1" max="4" step="0.01" x-model.number="zoom" x-on:input.stop="clamp()" x-on:change.stop class="flex-1 accent-red-500">
            <flux:icon name="magnifying-glass-plus" class="size-4" />
        </label>

        <div class="flex justify-end gap-2">
            <flux:button size="sm" variant="ghost" x-on:click="cancel()">Cancel</flux:button>
            <flux:button size="sm" variant="primary" icon="check" x-on:click="apply()" x-bind:disabled="uploading">
                <span x-text="uploading ? 'Saving…' : 'Save photo'">Save photo</span>
            </flux:button>
        </div>
    </div>
</div>
