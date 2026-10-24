<?php

use App\Livewire\Forms\Coordinator\StudentForm;
use App\Models\StudentRequirement;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public StudentForm $form;

    public int $currentStep = 1;

    public function save()
    {
        $this->form->store();
        $this->currentStep = 1;
        $this->dispatch('student-saved');
    }

    public function updatedFormStudentId(): void
    {
        $this->form->fillFromStudentDirectory();
    }

    public function updatedFormLastName(): void
    {
        $this->form->fillPasswordFromLastName();
    }

    public function nextStep(): void
    {
        $this->currentStep = min($this->currentStep + 1, 4);
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        $this->currentStep = min(max($step, 1), 4);
    }

    #[Computed]
    public function supervisors()
    {
        return User::query()->with('supervisorProfile')->availableToCoordinator(auth()->id())->role('supervisor')->orderBy('first_name')->get();
    }

    #[Computed]
    public function requirementSteps(): array
    {
        $definitions = collect(StudentRequirement::definitions())->groupBy('step');

        return collect(StudentRequirement::stepLabels())
            ->map(function (string $label, string $step) use ($definitions): array {
                return [
                    'key' => $step,
                    'label' => $label,
                    'icon' => match ($step) {
                        'orientation' => 'academic-cap',
                        'medical' => 'heart',
                        default => 'clipboard-document-list',
                    },
                    'items' => $definitions->get($step, collect())->values(),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function createSteps(): array
    {
        return collect($this->requirementSteps)
            ->push([
                'key' => 'student_information',
                'label' => 'Step 4: Student Information',
                'icon' => 'user',
                'items' => [],
            ])
            ->values()
            ->all();
    }
};
?>

<div>
    <flux:modal.trigger name="create-student">
        <flux:button icon="plus">Add Student</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-student" flyout variant="floating" class="md:w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Create Student</flux:heading>
                <flux:subheading>Optional requirements can be checked now or skipped until later.</flux:subheading>
            </div>

            <div class="rounded-xl bg-zinc-950 px-5 py-6 text-white">
                <div class="flex items-start">
                    @foreach ($this->createSteps as $index => $step)
                        @php
                            $stepNumber = $index + 1;
                            $isActive = $currentStep === $stepNumber;
                            $isPast = $currentStep > $stepNumber;
                        @endphp

                        <div class="flex min-w-0 flex-1 flex-col items-center text-center">
                            <button type="button" wire:click="goToStep({{ $stepNumber }})"
                                class="{{ $isActive ? 'bg-white text-zinc-950 ring-white' : ($isPast ? 'bg-forest text-white ring-forest' : 'bg-zinc-700 text-zinc-200 ring-zinc-600') }} flex h-14 w-14 items-center justify-center rounded-full ring-2 transition">
                                <flux:icon :name="$step['icon']" class="size-6" />
                            </button>

                            <div class="mt-4 text-sm font-semibold leading-snug">
                                {{ $step['label'] }}
                            </div>
                        </div>

                        @if (!$loop->last)
                            <div
                                class="{{ $currentStep > $stepNumber ? 'bg-forest' : 'bg-zinc-700' }} mt-7 h-px w-10 md:w-16">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            @if ($currentStep <= 3)
                @php
                    $step = $this->requirementSteps[$currentStep - 1];
                @endphp

                <div wire:key="create-student-requirement-step-{{ $step['key'] }}">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <flux:heading>{{ $step['label'] }}</flux:heading>
                            <flux:text class="mt-2">
                                Check only the requirements already provided. You can leave everything blank and
                                continue.
                            </flux:text>
                        </div>
                    </div>

                    <flux:checkbox.group wire:model="form.requirementChecks" variant="cards"
                        wire:key="create-student-requirement-group-{{ $step['key'] }}"
                        class="grid gap-3 md:grid-cols-2">
                        @foreach ($step['items'] as $requirement)
                            <flux:checkbox
                                wire:key="create-student-requirement-{{ $step['key'] }}-{{ $requirement['key'] }}"
                                value="{{ $requirement['key'] }}" label="{{ $requirement['label'] }}"
                                description="Mark checked only if already provided." />
                        @endforeach
                    </flux:checkbox.group>
                </div>
            @endif

            @if ($currentStep === 4)
                <flux:fieldset>
                    <flux:legend>Student Information</flux:legend>

                    <div class="space-y-6">
                        <div class="w-full">
                            <x-custom-file-upload model="form.photo" label="Upload student photo" accept="image/*" />
                        </div>

                        <flux:input wire:model.live.debounce.300ms="form.student_id" label="Student ID"
                            placeholder="2316-02373C"
                            description:trailing="Student ID can be found on the student's ID card." />

                        <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                            <flux:input wire:model="form.first_name" label="First name" placeholder="John" />
                            <flux:input wire:model="form.middle_name" label="Middle name" badge="Optional"
                                placeholder="Doe" />
                            <div class="col-span-2">
                                <flux:input wire:model.live.debounce.300ms="form.last_name" label="Last name"
                                    placeholder="Smith" />
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
                            <flux:input wire:model="form.password" type="password" label="Password"
                                placeholder="Enter password" viewable />

                            <flux:input wire:model="form.password_confirmation" type="password" label="Confirm Password"
                                placeholder="Confirm password" viewable />
                        </div>

                        <div>
                            <flux:select label="Course" wire:model="form.course" placeholder="Choose a course">
                                <flux:select.option>BSCS</flux:select.option>
                                <flux:select.option>BSIT</flux:select.option>
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
            @endif

            <div class="flex items-center justify-between gap-3">
                <flux:button type="button" wire:click="previousStep" icon="arrow-long-left"
                    :disabled="$currentStep === 1">
                    Back
                </flux:button>

                @if ($currentStep < 4)
                    <flux:button type="button" wire:click="nextStep" variant="primary"
                        icon:trailing="arrow-long-right">
                        Next
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" icon="user-plus">
                        Create Student
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
</div>
