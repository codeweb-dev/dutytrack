<?php

use App\Livewire\Forms\Coordinator\StudentForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public StudentForm $form;

    public User $student;

    #[Computed]
    public function supervisors()
    {
        return User::query()->with('supervisorProfile')->role('supervisor')->orderBy('first_name')->get();
    }

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        $this->student = $student;
        $this->form->setStudent($student);
    }

    public function loadStudent(): void
    {
        $this->student = $this->student->fresh(['studentProfile']);
        abort_unless((int) $this->student->coordinator_id === (int) Auth::id(), 403);
        $this->form->setStudent($this->student);
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('student-saved');
    }

};
?>

<form wire:submit="save" class="space-y-6">
    <flux:heading size="lg">Edit Student</flux:heading>
    <flux:subheading>Update the student details below.</flux:subheading>

    <flux:fieldset>
        <flux:legend>Student Information</flux:legend>

        <div class="space-y-6">
            <div class="w-full">
                <x-custom-file-upload model="form.photo" label="Upload student photo" accept="image/*" :existing="$form->existing_photo_url" />
            </div>

            <flux:input wire:model="form.student_id" label="Student ID" placeholder="2316-02373C"
                description:trailing="Student ID can be found on the student's ID card." />

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.first_name" label="First name" placeholder="John" />
                <flux:input wire:model="form.middle_name" label="Middle name" badge="Optional" placeholder="Doe" />
                <div class="col-span-2">
                    <flux:input wire:model="form.last_name" label="Last name" placeholder="Smith" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.email" type="email" label="Email"
                    placeholder="john.smith@example.com" />

                <flux:field>
                    <flux:label>Contact Number</flux:label>

                    <flux:input.group>
                        <flux:input.group.prefix>+63</flux:input.group.prefix>
                        <flux:input wire:model="form.contact_number" placeholder="123-456-7890" />
                    </flux:input.group>

                    <flux:error name="form.contact_number" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                <flux:input wire:model="form.password" type="password" label="New Password"
                    placeholder="Leave blank to keep current password" viewable />

                <flux:input wire:model="form.password_confirmation" type="password" label="Confirm New Password"
                    placeholder="Confirm new password" viewable />
            </div>

            <div>
                <flux:select wire:model="form.course" label="Course">
                    <option value="">Select course</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSIT">BSIT</option>
                </flux:select>
            </div>

            <div>
                <flux:select wire:model="form.company_supervisor_id" label="Assigned Supervisor" required>
                    <option value="">Select supervisor</option>
                    @foreach ($this->supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">
                            {{ $supervisor->name }}{{ $supervisor->supervisorProfile?->company_name ? ' - '.$supervisor->supervisorProfile->company_name : '' }}
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="form.company_supervisor_id" />
            </div>

            <flux:field>
                <flux:label>OJT Required Hours</flux:label>

                <div
                    class="rounded-2xl border border-zinc-200 bg-zinc-50 px-6 py-8 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col items-center">
                        <flux:input type="number" min="0" step="1" wire:model="form.ojt_required_hours"
                            class="w-40 text-center text-4xl font-bold" />

                        <div class="mt-2 text-sm uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            Hours Required
                        </div>
                    </div>
                </div>

                <flux:error name="form.ojt_required_hours" />
            </flux:field>
        </div>
    </flux:fieldset>

    <div class="w-full">
        <flux:button type="submit" variant="primary" class="w-full">Save Changes</flux:button>
    </div>
</form>
