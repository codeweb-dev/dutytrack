@php
    $assignment = $this->selectedEvaluationAssignment();
    $student = $assignment?->student;
    $profile = $student?->studentProfile;
@endphp

<flux:modal :name="$modalName" class="!max-w-7xl !w-full !h-full">
    <form wire:submit="saveEvaluation" class="space-y-6">
        <div>
            <flux:heading size="lg">Practicum Student Evaluation Form</flux:heading>
            <flux:text class="mt-2">Rate each job factor from 1 to 5 points.</flux:text>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:input label="Name" :value="$student?->name ?? ''" readonly />
            <flux:input label="Course" :value="$profile?->course ?? ''" readonly />
            <flux:input label="Cooperating Program" :value="$assignment ? $this->cooperatingProgramFor($assignment) : ''" readonly />

            <div class="grid grid-cols-2 gap-3">
                <flux:input type="date" wire:model="inclusive_from" label="Inclusive Date From" required />
                <flux:input type="date" wire:model="inclusive_to" label="To" required />
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="grid grid-cols-[1fr_4rem_14rem] bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                <div class="px-4 py-3">Directions for Evaluation</div>
                <div class="px-4 py-3 text-center">Weight</div>
                <div class="px-4 py-3 text-center">Rating</div>
            </div>

            <div class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                Please rate the trainee according to the different areas / job factors below by indicating the
                corresponding points.
            </div>

            @foreach ($this->evaluationAreas() as $areaKey => $area)
                <div class="border-t border-zinc-200 dark:border-zinc-700">
                    <div class="bg-zinc-100 px-4 py-3 text-sm font-semibold dark:bg-zinc-800">
                        {{ $area['title'] }}
                    </div>

                    @foreach ($area['items'] as $itemKey => $label)
                        <div class="grid grid-cols-[1fr_4rem_14rem] items-center border-t border-zinc-100 text-sm dark:border-zinc-800">
                            <div class="px-4 py-3">{{ $label }} <span class="text-red-600" aria-hidden="true">*</span></div>
                            <div class="px-4 py-3 text-center">5.0</div>
                            <div class="flex items-center justify-center gap-1 px-4 py-3">
                                @for ($score = 1; $score <= 5; $score++)
                                    <label class="flex size-8 cursor-pointer items-center justify-center rounded border border-zinc-300 text-xs font-medium has-[:checked]:border-red-600 has-[:checked]:bg-red-600 has-[:checked]:text-white dark:border-zinc-600">
                                        <input type="radio"
                                            class="sr-only"
                                            wire:model="evaluation_ratings.{{ $areaKey }}.{{ $itemKey }}"
                                            value="{{ $score }}" required>
                                        {{ $score }}
                                    </label>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div class="grid grid-cols-[1fr_4rem_14rem] border-t border-zinc-200 bg-zinc-50 text-sm font-semibold dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="px-4 py-3 text-right">Subtotal</div>
                        <div class="px-4 py-3 text-center">{{ number_format($area['subtotal'], 1) }}</div>
                        <div class="px-4 py-3 text-center">
                            {{ number_format(\App\Support\PracticumEvaluationForm::areaTotal($evaluation_ratings, $areaKey), 1) }}
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="grid grid-cols-[1fr_4rem_14rem] border-t border-zinc-300 bg-zinc-100 text-sm font-bold dark:border-zinc-700 dark:bg-zinc-800">
                <div class="px-4 py-3 text-right">TOTAL RATING</div>
                <div class="px-4 py-3 text-center">100.0</div>
                <div class="px-4 py-3 text-center">{{ number_format($this->evaluationTotal(), 1) }}</div>
            </div>
        </div>

        <flux:textarea wire:model="evaluation_remarks" label="Comments / Suggestions" rows="4" badge="Optional" />

        <div class="flex justify-end gap-3">
            <flux:modal.close>
                <flux:button type="button">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" icon="check">
                Submit Evaluation
            </flux:button>
        </div>
    </form>
</flux:modal>
