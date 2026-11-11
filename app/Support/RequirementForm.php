<?php

namespace App\Support;

use App\Actions\Fortify\CreateNewUser;
use App\Models\StudentRequirement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Fill-out versions of the requirement templates. Students answer a short
 * form (prefilled from their profile and placement), and the answers are
 * rendered into the EARIST document as a PDF submitted to the coordinator.
 *
 * Field defaults: type "text", required. Fields with the same name share one
 * prefill value across forms.
 */
class RequirementForm
{
    public const DOCUMENTS = [
        'Comprehensive Resume (original)', 'Good Moral (original)', 'Certificate of Registration (photocopy)',
        'Evaluation of Grades (photocopy)', 'Official Receipt (photocopy)', 'PSA/NSO Birth Certificate (photocopy)',
        'Parent’s Certification and Waiver (original)', 'Memorandum of Agreement (original)',
        'OJT Rules and Regulations (original)', 'Student Health Record', 'Transcript of Records for Transferee',
        'Form 137 (photocopy)', 'Endorsement of Thesis',
    ];

    public const FORMS = [
        'student_health_record' => [
            'note' => 'The health questions are answered by the medical officer when they sign the printed form.',
            'fields' => [
                'name' => ['label' => 'Name of student'],
                'student_number' => ['label' => 'Student number'],
                'program' => ['label' => 'Course / major'],
                'coordinator_name' => ['label' => 'Return form to (coordinator)'],
            ],
        ],
        'recommendation_for_ojt' => [
            'note' => 'Your coordinator’s letter recommending you to the company.',
            'fields' => [
                'date' => ['label' => 'Date', 'type' => 'date'],
                'supervisor_name' => ['label' => 'Addressed to'],
                'supervisor_position' => ['label' => 'Designation'],
                'company_name' => ['label' => 'Office / company'],
                'company_address' => ['label' => 'Company address', 'wide' => true],
                'name' => ['label' => 'Student name'],
                'program' => ['label' => 'Program'],
                'hours' => ['label' => 'Required OJT hours', 'type' => 'number'],
                'practitioner' => ['label' => 'Future practitioner field', 'placeholder' => 'Computer Science'],
                'coordinator_name' => ['label' => 'Practicum coordinator'],
            ],
        ],
        'application_for_ojt_individual' => [
            'note' => 'Your application letter to the company.',
            'fields' => [
                'date' => ['label' => 'Date', 'type' => 'date'],
                'supervisor_name' => ['label' => 'Addressed to'],
                'supervisor_position' => ['label' => 'Position'],
                'company_name' => ['label' => 'Company'],
                'company_address' => ['label' => 'Company address', 'wide' => true],
                'program' => ['label' => 'Program'],
                'hours' => ['label' => 'Required OJT hours', 'type' => 'number'],
                'setting' => ['label' => 'Training setting', 'placeholder' => 'IT / office'],
                'practitioner' => ['label' => 'Future practitioner field', 'placeholder' => 'Computer Science'],
                'name' => ['label' => 'Your name'],
            ],
        ],
        'acceptance_letter' => [
            'note' => 'The company’s letter accepting you. Your immediate supervisor signs the printed copy.',
            'fields' => [
                'date' => ['label' => 'Date', 'type' => 'date'],
                'letter_date' => ['label' => 'Date of the school’s letter', 'type' => 'date'],
                'coordinator_name' => ['label' => 'OJT coordinator'],
                'hours' => ['label' => 'Required OJT hours', 'type' => 'number'],
                'name' => ['label' => 'Name of trainee'],
                'program' => ['label' => 'Course'],
                'company_name' => ['label' => 'Cooperating company'],
                'company_address' => ['label' => 'Company address'],
                'company_head' => ['label' => 'Head of the company'],
                'department' => ['label' => 'Department assigned'],
                'supervisor_name' => ['label' => 'Immediate supervisor'],
                'office_telephone' => ['label' => 'Office telephone', 'required' => false],
                'job_description' => ['label' => 'Trainee’s job description', 'type' => 'textarea', 'wide' => true],
                'training_schedule' => ['label' => 'Training schedule', 'placeholder' => 'Mon–Fri, 8:00 AM – 5:00 PM'],
                'incentives' => ['label' => 'Incentives', 'required' => false],
            ],
        ],
        'application_for_ojt_training' => [
            'note' => 'The official OJT application form. Attach your passport-size photo to the printed copy.',
            'fields' => [
                'date' => ['label' => 'Date of application', 'type' => 'date'],
                'student_number' => ['label' => 'Student number'],
                'surname' => ['label' => 'Surname'],
                'first_name' => ['label' => 'First name'],
                'middle_name' => ['label' => 'Middle name', 'required' => false],
                'gender' => ['label' => 'Gender', 'type' => 'select', 'options' => ['Female', 'Male']],
                'birthdate' => ['label' => 'Birthdate', 'type' => 'date'],
                'program' => ['label' => 'Course'],
                'major' => ['label' => 'Major', 'required' => false],
                'semester' => ['label' => 'Semester', 'type' => 'select', 'options' => ['First', 'Second', 'Summer']],
                'school_year' => ['label' => 'School year', 'placeholder' => '2026-2027'],
                'contact_number' => ['label' => 'Contact number'],
                'email' => ['label' => 'Email address', 'type' => 'email'],
                'address' => ['label' => 'Address', 'wide' => true],
                'company_name' => ['label' => 'Company / office / industry'],
                'company_address' => ['label' => 'Company address'],
                'company_contact' => ['label' => 'Company contact number', 'required' => false],
                'company_email' => ['label' => 'Company email', 'type' => 'email', 'required' => false],
                'supervisor_name' => ['label' => 'Contact person'],
                'supervisor_position' => ['label' => 'Position'],
                'hours' => ['label' => 'Required OJT hours', 'type' => 'number'],
                'start_date' => ['label' => 'Start of OJT', 'type' => 'date'],
                'end_date' => ['label' => 'End of OJT', 'type' => 'date', 'required' => false],
                'documents' => ['label' => 'Documents submitted', 'type' => 'checkboxes', 'options' => self::DOCUMENTS, 'required' => false, 'wide' => true],
            ],
        ],
        'ojt_rules_and_regulations' => [
            'note' => 'Confirms you and your parent/guardian have read the OJT rules. Both sign the printed copy.',
            'fields' => [
                'name' => ['label' => 'Your name'],
                'program' => ['label' => 'Program'],
                'date' => ['label' => 'Date signed', 'type' => 'date'],
                'guardian_name' => ['label' => 'Parent / guardian'],
                'guardian_address' => ['label' => 'Parent / guardian address', 'wide' => true],
                'guardian_contact_number' => ['label' => 'Parent / guardian contact no.'],
                'guardian_email' => ['label' => 'Parent / guardian email', 'type' => 'email', 'required' => false],
            ],
        ],
        'parents_waiver' => [
            'note' => 'Your parent/guardian’s consent for your OJT. Both of you sign the printed copy.',
            'fields' => [
                'name' => ['label' => 'Your name'],
                'year_level' => ['label' => 'Year level', 'type' => 'select', 'options' => ['first year', 'second year', 'third year', 'fourth year']],
                'program' => ['label' => 'Program'],
                'company_name' => ['label' => 'Designated office'],
                'address' => ['label' => 'Your address', 'wide' => true],
                'contact_number' => ['label' => 'Your contact no.'],
                'date' => ['label' => 'Date signed', 'type' => 'date'],
                'guardian_name' => ['label' => 'Parent / guardian'],
                'guardian_contact_number' => ['label' => 'Parent / guardian contact no.'],
                'guardian_address' => ['label' => 'Parent / guardian address', 'wide' => true],
            ],
        ],
    ];

    public static function has(string $key): bool
    {
        return isset(self::FORMS[$key]);
    }

    public static function note(string $key): string
    {
        return self::FORMS[$key]['note'];
    }

    public static function fields(string $key): array
    {
        return array_map(
            fn (array $field) => $field + ['type' => 'text', 'required' => true, 'wide' => false, 'options' => [], 'placeholder' => null],
            self::FORMS[$key]['fields'],
        );
    }

    /** Saved answers win over profile data, so reopening a form shows what was submitted. */
    public static function values(string $key, User $student, array $saved = []): array
    {
        $prefill = self::prefill($student);

        return collect(self::fields($key))
            ->map(fn (array $field, string $name) => $saved[$name] ?? $prefill[$name] ?? ($field['type'] === 'checkboxes' ? [] : ''))
            ->all();
    }

    public static function rules(string $key): array
    {
        return collect(self::fields($key))
            ->mapWithKeys(fn (array $field, string $name) => match ($field['type']) {
                'checkboxes' => ["form.$name" => ['array'], "form.$name.*" => [Rule::in($field['options'])]],
                default => ["form.$name" => [
                    $field['required'] ? 'required' : 'nullable',
                    ...match ($field['type']) {
                        'date' => ['date'],
                        'email' => ['email', 'max:255'],
                        'number' => ['integer', 'min:1', 'max:5000'],
                        'select' => [Rule::in($field['options'])],
                        'textarea' => ['string', 'max:2000'],
                        default => ['string', 'max:255'],
                    },
                ]],
            })
            ->all();
    }

    public static function attributes(string $key): array
    {
        return collect(self::fields($key))->mapWithKeys(fn (array $field, string $name) => ["form.$name" => strtolower($field['label'])])->all();
    }

    public static function render(StudentRequirement $requirement, array $data): string
    {
        return Pdf::loadHTML(self::html($requirement, $data))->setPaper('a4')->output();
    }

    /** The document as HTML; the narrative report reuses it for its appendices. */
    public static function html(StudentRequirement $requirement, array $data): string
    {
        // Previews render unvalidated input, so coerce every value to what the views expect.
        foreach (self::fields($requirement->key) as $name => $field) {
            $value = $data[$name] ?? null;

            $data[$name] = match (true) {
                $field['type'] === 'checkboxes' => array_values(array_filter((array) $value, 'is_string')),
                ! is_scalar($value) => '',
                $field['type'] === 'date' && filled($value) => rescue(fn () => Carbon::parse($value)->format('F j, Y'), (string) $value, false),
                default => (string) $value,
            };
        }

        return view('exports.requirements.'.$requirement->key, [
            'title' => $requirement->label,
            'd' => $data,
            // Escaped value, or a blank line to write on when empty.
            'v' => fn (string $name, int $blank = 24) => filled($data[$name] ?? null) ? e($data[$name]) : str_repeat('_', $blank),
            'logo' => 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('certificate/earist-logo.png'))),
        ])->render();
    }

    public static function prefill(User $student): array
    {
        $student->loadMissing(['studentProfile', 'coordinator', 'ojtAssignment.companySupervisor.supervisorProfile']);

        $profile = $student->studentProfile;
        $supervisor = $student->ojtAssignment?->companySupervisor;
        $program = CreateNewUser::COURSES[$profile?->course]['program'] ?? null;
        $now = now();
        $schoolYear = $now->month >= 8 ? $now->year.'-'.($now->year + 1) : ($now->year - 1).'-'.$now->year;

        return array_filter([
            'date' => $now->toDateString(),
            'name' => $student->name,
            'surname' => $student->last_name,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'student_number' => $profile?->student_id,
            'program' => $program ? 'Bachelor of Science in '.$program : $profile?->course,
            'practitioner' => $program,
            'hours' => $profile?->ojt_required_hours,
            'birthdate' => $profile?->birthdate ? Carbon::parse($profile->birthdate)->toDateString() : null,
            'contact_number' => $student->contact_number,
            'email' => $student->email,
            'address' => $profile?->address,
            'guardian_name' => $profile?->guardian_name,
            'guardian_address' => $profile?->guardian_address,
            'guardian_contact_number' => $profile?->guardian_contact_number,
            'year_level' => 'fourth year',
            'school_year' => $schoolYear,
            'company_name' => $supervisor?->supervisorProfile?->company_name,
            'company_address' => $supervisor?->supervisorProfile?->company_address,
            'company_contact' => $supervisor?->contact_number,
            'company_email' => $supervisor?->email,
            'supervisor_name' => $supervisor?->name,
            'supervisor_position' => $supervisor?->supervisorProfile?->position_department,
            'coordinator_name' => $student->coordinator?->name,
        ], fn ($value) => filled($value));
    }
}
