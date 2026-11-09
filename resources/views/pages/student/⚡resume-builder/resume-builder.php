<?php

use App\Enums\ResumeTemplate;
use App\Models\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Resume Builder')] class extends Component
{
    #[Locked]
    public int $resumeId;

    public string $title = '';

    public string $template = '';

    public array $data = [];

    public ?string $savedAt = null;

    public function mount(Resume $resume): void
    {
        abort_unless((int) $resume->student_id === (int) Auth::id(), 404);

        $this->resumeId = $resume->id;
        $this->title = $resume->title;
        $this->template = $resume->template->value;
        $this->data = $resume->formData();
        $this->savedAt = $resume->updated_at?->toIso8601String();
    }

    protected function resume(): Resume
    {
        return Auth::user()->resumes()->findOrFail($this->resumeId);
    }

    protected function rules(): array
    {
        $text = fn (int $max) => ['nullable', 'string', 'max:'.$max];
        $year = ['nullable', 'integer', 'between:1950,2100'];
        $month = ['nullable', 'date_format:Y-m'];

        return [
            'title' => ['required', 'string', 'max:120'],
            'template' => ['required', Rule::enum(ResumeTemplate::class)],
            'data.first_name' => $text(60),
            'data.middle_name' => $text(60),
            'data.last_name' => $text(60),
            'data.phone' => $text(30),
            'data.email' => ['nullable', 'email', 'max:120'],
            'data.address' => $text(255),
            'data.objective' => $text(2000),
            'data.technical_skills' => ['array', 'max:60'],
            'data.technical_skills.*' => $text(150),
            'data.personal_skills' => ['array', 'max:60'],
            'data.personal_skills.*' => $text(150),
            'data.education' => ['array', 'max:10'],
            'data.education.*.level' => $text(60),
            'data.education.*.school' => $text(150),
            'data.education.*.program' => $text(150),
            'data.education.*.address' => $text(255),
            'data.education.*.start_year' => $year,
            'data.education.*.end_year' => $year,
            'data.education.*.current' => ['boolean'],
            'data.achievements' => ['array', 'max:30'],
            'data.achievements.*.title' => $text(150),
            'data.achievements.*.organization' => $text(150),
            'data.achievements.*.period' => $text(40),
            'data.achievements.*.description' => $text(500),
            'data.experience' => ['array', 'max:15'],
            'data.experience.*.title' => $text(120),
            'data.experience.*.company' => $text(150),
            'data.experience.*.type' => $text(60),
            'data.experience.*.start' => $month,
            'data.experience.*.end' => $month,
            'data.experience.*.current' => ['boolean'],
            'data.experience.*.description' => $text(1000),
            'data.show_certification' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'data.email' => 'email address',
            'data.education.*.start_year' => 'start year',
            'data.education.*.end_year' => 'end year',
            'data.experience.*.start' => 'start date',
            'data.experience.*.end' => 'end date',
        ];
    }

    /**
     * Validates and stores the current state. Invalid input stays in the editor
     * (never discarded) and the save status tells the student what to fix.
     */
    public function save(): bool
    {
        try {
            // validate() only returns keys that have rules, so injected keys are never stored.
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->dispatch('resume-save-invalid');

            return false;
        }

        $resume = $this->resume();
        $resume->update([
            'title' => trim($validated['title']),
            'template' => $validated['template'],
            'data' => $validated['data'],
        ]);

        $this->savedAt = $resume->updated_at->toIso8601String();
        $this->dispatch('resume-saved');

        return true;
    }

    public function switchTemplate(string $template): void
    {
        $this->template = ResumeTemplate::from($template)->value;
        $this->save();
    }

    public function addItem(string $field, ?int $index = null): void
    {
        $items = $this->items($field);
        $blank = in_array($field, Resume::LISTS, true) ? '' : Resume::ENTRIES[$field];

        array_splice($items, $index ?? count($items), 0, [$blank]);
        $this->data[$field] = $items;
        $this->save();
    }

    public function removeItem(string $field, int $index): void
    {
        $items = $this->items($field);

        array_splice($items, $index, 1);
        $this->data[$field] = $items;
        $this->resetErrorBag();
        $this->save();
    }

    public function moveItem(string $field, int $index, int $direction): void
    {
        $items = $this->items($field);
        $target = $index + ($direction < 0 ? -1 : 1);

        if (! isset($items[$index], $items[$target])) {
            return;
        }

        [$items[$index], $items[$target]] = [$items[$target], $items[$index]];
        $this->data[$field] = $items;
        $this->resetErrorBag();
        $this->save();
    }

    /** The repeatable list for a known field, reindexed. Rejects anything else. */
    protected function items(string $field): array
    {
        abort_unless(in_array($field, Resume::LISTS, true) || array_key_exists($field, Resume::ENTRIES), 404);

        return array_values((array) ($this->data[$field] ?? []));
    }

    public function fullName(): string
    {
        return Resume::nameFrom($this->data);
    }
};
