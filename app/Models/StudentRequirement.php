<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StudentRequirement extends Model
{
    protected $fillable = [
        'student_id',
        'key',
        'label',
        'step',
        'sort_order',
        'template_path',
        'is_completed',
        'submitted_at',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function definitions(): array
    {
        return [
            [
                'key' => 'orientation',
                'label' => 'OJT Orientation',
                'step' => 'orientation',
                'step_label' => 'Step 1: Orientation',
                'sort_order' => 10,
                'template_path' => null,
            ],
            [
                'key' => 'student_health_record',
                'label' => 'Student Health Record',
                'step' => 'medical',
                'step_label' => 'Step 2: Medical',
                'sort_order' => 20,
                'template_path' => 'requirements/STUDENT-HEALTH-RECORD.docx',
            ],
            [
                'key' => 'recommendation_for_ojt',
                'label' => 'Recommendation for OJT Individual',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 30,
                'template_path' => 'requirements/Recommendation-for-OJT-individual.docx',
            ],
            [
                'key' => 'application_for_ojt_individual',
                'label' => 'Application for OJT Individual',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 40,
                'template_path' => 'requirements/Application-for-OJT-individual.doc',
            ],
            [
                'key' => 'acceptance_letter',
                'label' => 'Acceptance Letter',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 50,
                'template_path' => 'requirements/Acceptance-Letter.doc',
            ],
            [
                'key' => 'application_for_ojt_training',
                'label' => 'Application for OJT Training Template',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 60,
                'template_path' => 'requirements/Application-for-OJT-Training-Template.docx',
            ],
            [
                'key' => 'ojt_rules_and_regulations',
                'label' => 'OJT Rules and Regulations',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 70,
                'template_path' => 'requirements/OJT-Rules-and-Regulations.docx',
            ],
            [
                'key' => 'evaluation_form_for_practicum',
                'label' => 'Evaluation Form for Practicum',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 80,
                'template_path' => 'requirements/Evaluation-Form-for-Practicum.doc',
            ],
            [
                'key' => 'parents_waiver',
                'label' => 'Parents Waiver',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 90,
                'template_path' => 'requirements/Parents-Waiver.docx',
            ],
            [
                'key' => 'accomplishment_report_template',
                'label' => 'Accomplishment Report Template',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 100,
                'template_path' => 'requirements/Accomplishment-Report-Template.docx',
            ],
            [
                'key' => 'narrative_report',
                'label' => 'Narrative Report',
                'step' => 'forms',
                'step_label' => 'Step 3: Forms',
                'sort_order' => 110,
                'template_path' => 'requirements/Narrative-Report.doc',
            ],
        ];
    }

    public static function stepLabels(): array
    {
        return collect(self::definitions())
            ->mapWithKeys(fn (array $definition): array => [$definition['step'] => $definition['step_label']])
            ->all();
    }

    public static function ensureForStudent(User|int $student): Collection
    {
        $studentId = $student instanceof User ? $student->id : $student;

        foreach (self::definitions() as $definition) {
            self::query()->updateOrCreate(
                [
                    'student_id' => $studentId,
                    'key' => $definition['key'],
                ],
                [
                    'label' => $definition['label'],
                    'step' => $definition['step'],
                    'sort_order' => $definition['sort_order'],
                    'template_path' => $definition['template_path'],
                ],
            );
        }

        return self::query()
            ->where('student_id', $studentId)
            ->orderBy('sort_order')
            ->get();
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
