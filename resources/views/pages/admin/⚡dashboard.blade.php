<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <x-stat-card title="Total Students" value="393" change="+16.2%" change-color="text-green-500" />

    <x-stat-card title="Total Supervisors" value="428" change="-12.4%" change-color="text-red-500" icon="users" />

    <x-stat-card title="Total Partners" value="376" change="+12.6%" change-color="text-green-500" />

    <x-stat-card title="Average Duty Hours" value="8.5" change="+13.7%" change-color="text-green-500" />
</div>
