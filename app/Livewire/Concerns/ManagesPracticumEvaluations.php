<?php

namespace App\Livewire\Concerns;

use App\Models\Evaluation;
use App\Models\OjtAssignment;
use App\Support\PracticumEvaluationForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ManagesPracticumEvaluations
{
    public ?int $evaluation_assignment_id = null;

    public string $inclusive_from = '';

    public string $inclusive_to = '';

    public string $evaluation_remarks = '';

    public array $evaluation_ratings = [];

    abstract protected function assignmentQuery(): Builder;

    abstract protected function evaluatorType(): string;

    public function openEvaluationModal(int $assignmentId): void
    {
        $assignment = $this->assignmentQuery()
            ->with(['student.studentProfile', 'student.coordinator'])
            ->whereKey($assignmentId)
            ->firstOrFail();

        if (Evaluation::query()
            ->where('student_id', $assignment->student_id)
            ->where('evaluator_type', $this->evaluatorType())
            ->exists()) {
            unset($this->assignments);
            Flux::toast(variant: 'warning', heading: 'Already evaluated.', text: 'You can submit only one evaluation per student.');

            return;
        }

        $this->evaluation_assignment_id = $assignment->id;
        $this->inclusive_from = '';
        $this->inclusive_to = '';
        $this->evaluation_remarks = '';
        $this->evaluation_ratings = PracticumEvaluationForm::defaultRatings();

        $this->resetValidation();

        if ($pickerModalName = $this->evaluationPickerModalName()) {
            Flux::modal($pickerModalName)->close();
        }

        Flux::modal($this->evaluationModalName())->show();
    }

    public function saveEvaluation(): void
    {
        $this->validate($this->evaluationRules());

        $rating = PracticumEvaluationForm::total($this->evaluation_ratings);

        $alreadyEvaluated = DB::transaction(function () use ($rating): bool {
            $assignment = $this->assignmentQuery()
                ->with(['student.coordinator'])
                ->whereKey($this->evaluation_assignment_id)
                ->firstOrFail();

            // Serialize submissions for this student before checking for an existing evaluation.
            $assignment->student()->lockForUpdate()->firstOrFail();

            if (Evaluation::query()
                ->where('student_id', $assignment->student_id)
                ->where('evaluator_type', $this->evaluatorType())
                ->lockForUpdate()
                ->first()) {
                return true;
            }

            Evaluation::query()->create([
                'student_id' => $assignment->student_id,
                'ojt_assignment_id' => $assignment->id,
                'evaluator_id' => Auth::id(),
                'evaluator_type' => $this->evaluatorType(),
                'evaluation_date' => now()->toDateString(),
                'inclusive_from' => $this->inclusive_from,
                'inclusive_to' => $this->inclusive_to,
                'cooperating_program' => $this->cooperatingProgramFor($assignment),
                'ratings' => $this->evaluation_ratings,
                'rating' => $rating,
                'remarks' => trim($this->evaluation_remarks) !== '' ? trim($this->evaluation_remarks) : null,
                'status' => 'submitted',
            ]);

            return false;
        });

        if ($alreadyEvaluated) {
            $this->resetEvaluationForm();
            unset($this->assignments);
            Flux::modal($this->evaluationModalName())->close();
            Flux::toast(variant: 'warning', heading: 'Already evaluated.', text: 'You can submit only one evaluation per student.');

            return;
        }

        $this->resetEvaluationForm();
        unset($this->assignments, $this->evaluations, $this->summary);

        Flux::modal($this->evaluationModalName())->close();
        Flux::toast(variant: 'success', heading: 'Evaluation submitted.', text: 'The student can view it once the coordinator approves it.');
    }

    public function selectedEvaluationAssignment(): ?OjtAssignment
    {
        if (! $this->evaluation_assignment_id) {
            return null;
        }

        return $this->assignmentQuery()
            ->with(['student.studentProfile', 'student.coordinator'])
            ->whereKey($this->evaluation_assignment_id)
            ->first();
    }

    public function evaluationAreas(): array
    {
        return PracticumEvaluationForm::areas();
    }

    public function evaluationTotal(): float
    {
        return PracticumEvaluationForm::total($this->evaluation_ratings);
    }

    public function cooperatingProgramFor(OjtAssignment $assignment): string
    {
        return $assignment->student?->coordinator?->name ?? '';
    }

    protected function evaluationRules(): array
    {
        $rules = [
            'evaluation_assignment_id' => ['required', 'integer'],
            'inclusive_from' => ['required', 'date'],
            'inclusive_to' => ['required', 'date', 'after_or_equal:inclusive_from'],
            'evaluation_remarks' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (PracticumEvaluationForm::areas() as $areaKey => $area) {
            foreach (array_keys($area['items']) as $itemKey) {
                $rules["evaluation_ratings.{$areaKey}.{$itemKey}"] = ['required', 'integer', 'min:1', 'max:5'];
            }
        }

        return $rules;
    }

    protected function resetEvaluationForm(): void
    {
        $this->evaluation_assignment_id = null;
        $this->inclusive_from = '';
        $this->inclusive_to = '';
        $this->evaluation_remarks = '';
        $this->evaluation_ratings = [];
    }

    protected function evaluationModalName(): string
    {
        return $this->evaluatorType().'-evaluation-form';
    }

    protected function evaluationPickerModalName(): ?string
    {
        return null;
    }
}
