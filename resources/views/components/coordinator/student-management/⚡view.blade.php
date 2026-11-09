<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public User $student;

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        $this->student = $student->load('studentProfile');
    }
};
?>

<div class="space-y-4">
    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <flux:avatar size="lg" :src="$student->profile_photo_url" :name="$student->display_name" />
        <div class="min-w-0 flex-1">
            <h3 class="truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $student->display_name }}</h3>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Personal Information</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">First Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->first_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Middle Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->middle_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->last_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Extension Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->extension_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student->contact_number ? '+63 ' . $student->contact_number : 'No contact number' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Academic Information</h4>
            <dl class="mt-4 grid gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Student ID</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->student_id ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Course</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->course ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">OJT Required Hours</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ \App\Support\HourFormatter::format($student->studentProfile?->ojt_required_hours ?? 0) }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Address &amp; Guardian</h4>
            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Guardian Name</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->guardian_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Guardian Contact Number</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student->studentProfile?->guardian_contact_number ? '+63 ' . $student->studentProfile->guardian_contact_number : 'No contact number' }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Guardian Address</dt>
                    <dd class="mt-1 break-words text-base leading-6 font-medium text-zinc-900 dark:text-white">{{ $student->studentProfile?->guardian_address ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-900/50 sm:col-span-2">
            <h4 class="text-base font-semibold text-zinc-900 dark:text-white">Account Metadata</h4>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Created</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student->created_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                    <dd class="mt-1 text-base leading-6 font-medium text-zinc-900 dark:text-white">
                        {{ $student->updated_at?->format('M d, Y h:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
