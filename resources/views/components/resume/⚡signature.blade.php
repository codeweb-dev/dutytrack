<?php

use App\Livewire\Concerns\ManagesResumeImage;
use Livewire\Component;

new class extends Component
{
    use ManagesResumeImage;

    protected string $type = 'signature';

    protected function uploadRules(): array
    {
        return ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048', 'dimensions:max_width=4000,max_height=4000'];
    }
};
?>

@php
    $url = $this->imageUrl();
    $checker = 'background-image: repeating-conic-gradient(rgb(0 0 0 / 0.06) 0 25%, transparent 0 50%); background-size: 14px 14px;';
@endphp

<div x-data="signaturePad" class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Signature</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400">Shown above your complete name. Stored privately and used only on this resume.</div>
        </div>
    </div>

    @if ($url)
        <div class="flex items-center gap-3 rounded-md border border-zinc-200 bg-white p-3 dark:border-zinc-700">
            <div class="flex h-14 w-40 items-center justify-center rounded" style="{{ $checker }}">
                <img src="{{ $url }}" alt="Your saved signature" class="max-h-12 max-w-36 object-contain">
            </div>
            <span class="text-sm text-zinc-600">Saved signature</span>
        </div>
    @endif

    <div role="tablist" aria-label="Signature option" class="grid grid-cols-3 gap-1 rounded-lg bg-zinc-100 p-1 text-sm dark:bg-zinc-800">
        @foreach (['draw' => 'Draw', 'upload' => 'Upload', 'none' => 'No signature yet'] as $value => $label)
            <button type="button" role="tab" x-on:click="mode = '{{ $value }}'" :aria-selected="mode === '{{ $value }}'"
                :class="mode === '{{ $value }}' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                class="rounded-md px-2 py-1.5 font-medium transition">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Draw --}}
    <div x-show="mode === 'draw'" role="tabpanel" class="space-y-2">
        <div class="relative overflow-hidden rounded-md border border-zinc-300 bg-white dark:border-zinc-600">
            <canvas x-ref="canvas" class="block aspect-[3/1] w-full touch-none cursor-crosshair"
                aria-label="Signature drawing area. Sign with your mouse, finger, or stylus."
                x-on:pointerdown="start($event)" x-on:pointermove="draw($event)" x-on:pointerup="stop($event)" x-on:pointercancel="stop($event)"></canvas>
            <div class="pointer-events-none absolute inset-x-6 bottom-[22%] border-b border-dashed border-zinc-300"></div>
            <div x-show="!strokes.length" class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-zinc-400">Sign here</div>
        </div>
        <div class="flex flex-wrap justify-end gap-2">
            <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" x-on:click="undo()" x-bind:disabled="!strokes.length">Undo</flux:button>
            <flux:button size="sm" variant="ghost" icon="x-mark" x-on:click="clear()" x-bind:disabled="!strokes.length">Clear</flux:button>
            <flux:button size="sm" variant="primary" icon="check" x-on:click="saveDrawing()" x-bind:disabled="!strokes.length || uploading">Save signature</flux:button>
        </div>
    </div>

    {{-- Upload --}}
    <div x-show="mode === 'upload'" x-cloak role="tabpanel" class="space-y-2">
        <input x-ref="file" type="file" accept="image/png,image/jpeg" class="sr-only" tabindex="-1" aria-label="Choose a signature image"
            x-on:change.stop="pickFile($event)" x-on:input.stop>
        <button type="button" x-on:click="$refs.file.click()"
            class="flex min-h-28 w-full flex-col items-center justify-center gap-2 rounded-md border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-800"
            style="{{ $checker }}">
            <template x-if="preview"><img :src="preview" alt="Signature preview" class="max-h-20 max-w-full object-contain"></template>
            <template x-if="!preview">
                <span class="flex flex-col items-center gap-1">
                    <flux:icon name="arrow-up-tray" class="size-5" />
                    PNG or JPG up to 2 MB. Transparent PNGs look best.
                </span>
            </template>
        </button>
        <div class="flex justify-end gap-2" x-show="preview">
            <flux:button size="sm" variant="ghost" x-on:click="resetFile()">Cancel</flux:button>
            <flux:button size="sm" variant="primary" icon="check" x-on:click="saveFile()" x-bind:disabled="uploading">Save signature</flux:button>
        </div>
    </div>

    {{-- None --}}
    <div x-show="mode === 'none'" x-cloak role="tabpanel" class="rounded-md bg-zinc-50 p-3 text-sm text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
        The signature line stays blank so you can sign the printed copy. Your complete name still appears below it.
        @if ($url)
            <div class="mt-3">
                <flux:button size="sm" icon="trash" wire:click="removeImage" wire:confirm="Remove your saved signature from this resume?">
                    Remove saved signature
                </flux:button>
            </div>
        @endif
    </div>

    <p x-show="error" x-text="error" x-cloak class="text-sm text-red-500" role="alert"></p>
    @error('upload')
        <p class="text-sm text-red-500" role="alert">{{ $message }}</p>
    @enderror
</div>
