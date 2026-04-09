<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Students" value="393" change="+16.2%" change-color="text-green-500" />

            <x-stat-card title="Total Supervisors" value="428" change="-12.4%" change-color="text-red-500" icon="users" />

            <x-stat-card title="Total Partners" value="376" change="+12.6%" change-color="text-green-500" />

            <x-stat-card title="Average Duty Hours" value="8.5" change="+13.7%" change-color="text-green-500" />
        </div>
        
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
