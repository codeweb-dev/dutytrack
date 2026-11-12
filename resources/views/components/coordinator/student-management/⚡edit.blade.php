<?php

use App\Livewire\Forms\Coordinator\StudentForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public StudentForm $form;

    public User $student;

    public function mount(User $student): void
    {
        abort_unless((int) $student->coordinator_id === (int) Auth::id(), 403);

        $this->student = $student;
        $this->form->setStudent($student);
    }

    public function save(): void
    {
        $this->form->update();
        $this->dispatch('student-saved');
    }

};
?>

<form wire:submit="save" class="flex flex-col gap-8">
    <div>
        <flux:heading size="lg">Edit Student</flux:heading>
        <flux:subheading>Update the student details below.</flux:subheading>
    </div>

    <!-- Profile Photo -->
    <div x-data="{ preview: @js($form->existing_photo_url), tooBig: false }" class="flex flex-col items-center gap-3">
        <label for="photo-{{ $student->id }}" class="group relative cursor-pointer">
            <div
                class="flex size-24 items-center justify-center overflow-hidden rounded-full bg-zinc-100 ring-4 ring-white transition group-hover:ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-900 dark:group-hover:ring-zinc-700">
                <template x-if="preview">
                    <img :src="preview" alt="" class="size-full object-cover" />
                </template>
                <flux:icon.user x-show="!preview" class="size-10 text-zinc-400" />
            </div>
            <span
                class="absolute bottom-0 right-0 flex size-8 items-center justify-center rounded-full bg-zinc-900 text-white shadow ring-2 ring-white dark:bg-white dark:text-zinc-900 dark:ring-zinc-900">
                <flux:icon.camera variant="micro" />
            </span>
        </label>
        <input id="photo-{{ $student->id }}" type="file" accept="image/*" class="sr-only" wire:model="form.photo"
            @required(! $form->existing_photo_url)
            x-on:change="
                const file = $event.target.files[0];
                tooBig = !!file && file.size > 2 * 1024 * 1024;
                if (tooBig) $event.target.value = '';
                if (file && !tooBig) preview = URL.createObjectURL(file);
            " />
        <flux:text class="text-xs">Upload a profile photo (max 2MB)</flux:text>
        <flux:text x-show="tooBig" x-cloak class="text-xs !text-red-500">That photo is larger than 2MB. Please choose a smaller one.</flux:text>
        <flux:error name="form.photo" />
    </div>

    <flux:input wire:model="form.student_id" label="Student ID" placeholder="2316-02373C" required
        description="Found on the student's school ID card." />

    <!-- Personal Information -->
    <section class="flex flex-col gap-5">
        <div class="flex items-center gap-3">
            <flux:heading>Personal information</flux:heading>
            <flux:separator class="flex-1" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:input class="sm:order-1" wire:model="form.first_name" label="First name" placeholder="Juan" required />
            <flux:input class="sm:order-3" wire:model="form.middle_name" label="Middle name" badge="Optional" placeholder="Santos" />
            <flux:input class="sm:order-2" wire:model="form.last_name" label="Last name" placeholder="Dela Cruz" required
                x-on:input="const pw = $el.value.replace(/\s+/g, '').toUpperCase().slice(0, 12); $wire.$set('form.password', pw, false); $wire.$set('form.password_confirmation', pw, false)" />
            <flux:select class="sm:order-4" wire:model="form.extension_name" label="Extension name" badge="Optional">
                <option value="">None</option>
                @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $extension)
                    <option value="{{ $extension }}">{{ $extension }}</option>
                @endforeach
            </flux:select>

            <flux:field class="sm:order-5">
                <flux:label>Contact number</flux:label>
                <flux:input.group>
                    <flux:input.group.prefix>+63</flux:input.group.prefix>
                    <flux:input wire:model="form.contact_number" type="tel" placeholder="9123456789" required />
                </flux:input.group>
                <flux:error name="form.contact_number" />
            </flux:field>

            <flux:input class="sm:order-6" wire:model="form.email" type="email" label="Email address"
                placeholder="email@example.com" required />

            <div class="sm:col-span-2 sm:order-7">
                <flux:input wire:model="form.address" label="Address" placeholder="Street, Barangay, City" required />
            </div>
        </div>
    </section>

    <!-- Academic Information -->
    <section class="flex flex-col gap-5">
        <div class="flex items-center gap-3">
            <flux:heading>Academic information</flux:heading>
            <flux:separator class="flex-1" />
        </div>

        <flux:input wire:model="form.birthdate" type="date" label="Birthdate" required />
    </section>

    <!-- Guardian Information -->
    <section class="flex flex-col gap-5">
        <div class="flex items-center gap-3">
            <flux:heading>Guardian information</flux:heading>
            <flux:separator class="flex-1" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:input wire:model="form.guardian_name" label="Guardian name" placeholder="Maria Dela Cruz" required />

            <flux:field>
                <flux:label>Guardian contact number</flux:label>
                <flux:input.group>
                    <flux:input.group.prefix>+63</flux:input.group.prefix>
                    <flux:input wire:model="form.guardian_contact_number" type="tel" placeholder="9123456789" required />
                </flux:input.group>
                <flux:error name="form.guardian_contact_number" />
            </flux:field>

            <div class="sm:col-span-2">
                <flux:input wire:model="form.guardian_address" label="Guardian address" placeholder="Street, Barangay, City" required />
            </div>
        </div>
    </section>

    <!-- Account Security -->
    <section class="flex flex-col gap-5">
        <div class="flex items-center gap-3">
            <flux:heading>Account security</flux:heading>
            <flux:separator class="flex-1" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:input wire:model="form.password" type="password" label="New password" badge="Optional"
                placeholder="Leave blank to keep current" viewable />
            <flux:input wire:model="form.password_confirmation" type="password" label="Confirm new password" badge="Optional"
                placeholder="Confirm new password" viewable />
        </div>
    </section>

    <flux:button type="submit" variant="primary" class="w-full">Save Changes</flux:button>
</form>
