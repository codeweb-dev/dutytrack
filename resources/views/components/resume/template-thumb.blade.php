{{-- Miniature sketch of a resume template's layout, used in pickers and cards. --}}
@props(['template'])

@php
    $template = $template instanceof \App\Enums\ResumeTemplate ? $template : \App\Enums\ResumeTemplate::from($template);
    $line = 'block h-[3px] rounded-full';
@endphp

<div {{ $attributes->class('relative aspect-[210/297] w-full overflow-hidden rounded-sm bg-white shadow-sm ring-1 ring-zinc-200 dark:ring-zinc-700') }} aria-hidden="true">
    @switch($template)
        @case(\App\Enums\ResumeTemplate::Earist)
            <div class="absolute inset-[7%] flex flex-col gap-[5%] overflow-hidden border-[1.5px] border-zinc-800 p-[8%]">
                <div class="flex items-start justify-between">
                    <div class="mt-[18%] w-1/2 space-y-1">
                        <span class="{{ $line }} h-[4px] w-full bg-zinc-800"></span>
                        <span class="{{ $line }} w-2/3 bg-zinc-400"></span>
                        <span class="{{ $line }} w-3/4 bg-zinc-400"></span>
                    </div>
                    <div class="aspect-square w-[30%] border border-zinc-600"></div>
                </div>
                @foreach ([2, 3, 2, 2] as $rows)
                    <div class="space-y-1 border-t-[1.5px] border-zinc-700 pt-[3%]">
                        <span class="{{ $line }} w-1/3 bg-zinc-700"></span>
                        @for ($i = 0; $i < $rows - 1; $i++)
                            <span class="{{ $line }} ml-[8%] w-2/3 bg-zinc-300"></span>
                        @endfor
                    </div>
                @endforeach
            </div>
        @break

        @case(\App\Enums\ResumeTemplate::Modern)
            <div class="flex h-[22%] items-center justify-between border-b-2 border-teal-700 bg-teal-50 px-[9%]">
                <div class="w-1/2 space-y-1">
                    <span class="{{ $line }} h-[5px] w-full bg-teal-900"></span>
                    <span class="{{ $line }} w-2/3 bg-teal-600/60"></span>
                </div>
                <div class="aspect-square w-[20%] bg-teal-200"></div>
            </div>
            <div class="space-y-[7%] p-[9%]">
                <div class="space-y-1"><span class="{{ $line }} w-1/4 bg-teal-600"></span><span class="{{ $line }} w-full bg-zinc-300"></span></div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ([1, 2] as $col)
                        <div class="space-y-1"><span class="{{ $line }} w-1/2 bg-teal-600"></span><span class="{{ $line }} w-full bg-zinc-300"></span><span class="{{ $line }} w-3/4 bg-zinc-300"></span></div>
                    @endforeach
                </div>
                @foreach ([1, 2] as $block)
                    <div class="space-y-1"><span class="{{ $line }} w-1/3 bg-teal-600"></span><span class="{{ $line }} w-full bg-zinc-300"></span><span class="{{ $line }} w-2/3 bg-zinc-300"></span></div>
                @endforeach
            </div>
        @break

        @case(\App\Enums\ResumeTemplate::Minimalist)
            <div class="flex flex-col items-center gap-1 px-[12%] pt-[12%]">
                <span class="{{ $line }} h-[4px] w-2/3 bg-zinc-700"></span>
                <span class="{{ $line }} w-1/2 bg-zinc-300"></span>
            </div>
            <div class="space-y-[8%] px-[12%] pt-[10%]">
                @foreach ([3, 2, 3, 2] as $rows)
                    <div class="space-y-1">
                        <span class="{{ $line }} w-1/4 border-b border-zinc-300 bg-zinc-500"></span>
                        @for ($i = 0; $i < $rows; $i++)
                            <span class="{{ $line }} {{ $i % 2 ? 'w-2/3' : 'w-full' }} bg-zinc-200"></span>
                        @endfor
                    </div>
                @endforeach
            </div>
        @break

        @case(\App\Enums\ResumeTemplate::Corporate)
            <div class="flex h-[20%] items-center justify-between bg-slate-800 px-[9%]">
                <div class="w-1/2 space-y-1">
                    <span class="{{ $line }} h-[5px] w-full bg-white"></span>
                    <span class="{{ $line }} w-2/3 bg-slate-400"></span>
                </div>
                <div class="aspect-square w-[20%] border-2 border-white bg-slate-500"></div>
            </div>
            <div class="space-y-[7%] p-[9%]">
                @foreach ([2, 3, 2, 2] as $rows)
                    <div class="space-y-1">
                        <span class="block h-[5px] w-full border-l-2 border-slate-800 bg-slate-100"></span>
                        @for ($i = 0; $i < $rows; $i++)
                            <span class="{{ $line }} ml-[5%] {{ $i % 2 ? 'w-2/3' : 'w-5/6' }} bg-zinc-300"></span>
                        @endfor
                    </div>
                @endforeach
            </div>
        @break
    @endswitch
</div>
