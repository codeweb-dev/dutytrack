<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public User $supervisor;

    public function mount(User $supervisor): void
    {
        $this->supervisor = $supervisor;
        $actor = Auth::user();

        abort_unless(
            $actor && ($actor->hasRole('ojt_coordinator') && (int) $supervisor->coordinator_id === (int) $actor->id),
            403
        );
    }
};
?>

<div class="space-y-4">
    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <flux:avatar size="lg" :src="$supervisor->profile_photo_url" :name="$supervisor->display_name" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $supervisor->display_name }}</h3>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Supervisor Information</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->email }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Position and Department</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->position_department ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Extension Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->extension_name ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->contact_number ? '+63 ' . $supervisor->contact_number : 'No contact number' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Company / Institution</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Type</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        <x-ojt-type-badge :type="$supervisor->supervisorProfile?->supervisor_type" />
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                        {{ $supervisor->supervisorProfile?->supervisor_type === 'internal' ? 'Institution' : 'Company' }}
                    </dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->company_name ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->supervisorProfile?->company_address ?: '—' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Account Metadata</h4>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->created_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $supervisor->updated_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
