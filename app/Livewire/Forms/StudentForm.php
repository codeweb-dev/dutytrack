<?php

namespace App\Livewire\Forms;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Livewire\WithFileUploads;

class StudentForm extends Form
{
    use WithFileUploads;

    #[Validate('nullable|image|max:2048')]
    public $photo = null;

    #[Validate('required|string|max:255|unique:student_profiles,student_id')]
    public $student_id = '';

    #[Validate('required|string|max:255')]
    public $first_name = '';

    #[Validate('nullable|string|max:255')]
    public $middle_name = '';

    #[Validate('required|string|max:255')]
    public $last_name = '';

    #[Validate('required|string|max:255|unique:users,email')]
    public $email = '';

    #[Validate('nullable|string|max:20')]
    public $contact_number = '';

    #[Validate('required|string|min:8|confirmed')]
    public $password = '';

    public $password_confirmation = '';

    #[Validate('required|string|max:50')]
    public $course = '';

    #[Validate('required|string|max:20')]
    public $year_level = '';

    #[Validate('nullable|string|max:50')]
    public $section = '';

    #[Validate('nullable|integer|min:0')]
    public $ojt_required_hours = 0;

    public function store(): void
    {
        $this->validate();

        DB::transaction(function () {
            $photoPath = $this->photo
                ? $this->photo->store('students/photos', 'public')
                : null;

            $user = User::create([
                'name' => trim($this->first_name . ' ' . $this->last_name),
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name ?: null,
                'last_name' => $this->last_name,
                'contact_number' => $this->contact_number ?: null,
                'email' => $this->email,
                'password' => $this->password,
                'profile_photo_path' => $photoPath,
                'status' => 'active',
            ]);

            $user->assignRole(EnumsRole::STUDENT->value);

            $user->studentProfile()->create([
                'student_id' => $this->student_id,
                'course' => $this->course,
                'year_level' => $this->year_level,
                'section' => $this->section ?: null,
                'ojt_required_hours' => $this->ojt_required_hours ?: 0,
            ]);
        });

        $this->reset();

        Flux::toast(
            variant: 'success',
            heading: 'Student created.',
            text: 'The student has been added successfully.'
        );

        Flux::modal('create-student')->close();
    }
}
