{{-- Card for one entry of a repeatable resume section (education, achievements, experience). --}}
@props(['field', 'index', 'count', 'title', 'label'])

<div {{ $attributes->class('rounded-lg border border-zinc-200 bg-zinc-50/60 p-3 dark:border-zinc-700 dark:bg-zinc-800/40') }}>
    <div class="mb-3 flex items-center justify-between gap-2">
        <div class="min-w-0 truncate text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $title }}</div>
        <x-resume.item-controls :field="$field" :index="$index" :count="$count" :label="$label"
            confirm="Remove this {{ $label }}? This can't be undone." />
    </div>

    <div class="grid items-start gap-3 @xs:grid-cols-2">
        {{ $slot }}
    </div>
</div>
