<?php

use App\Enums\ResumeTemplate;
use App\Enums\Role as AppRole;
use App\Models\Resume;
use App\Models\User;
use App\Support\ResumeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function createResumeStudent(array $attributes = []): User
{
    foreach (AppRole::cases() as $role) {
        PermissionRole::findOrCreate($role->value, 'web');
    }

    return User::factory()->student()->create($attributes);
}

function createResume(User $student, array $data = [], array $attributes = []): Resume
{
    return $student->resumes()->create(array_merge([
        'title' => 'My OJT Resume',
        'template' => ResumeTemplate::Earist,
        'data' => array_replace(Resume::emptyData(), $data),
    ], $attributes));
}

beforeEach(fn () => Storage::fake('local'));

test('students can open the resume builder from the sidebar', function () {
    $this->actingAs(createResumeStudent())
        ->get(route('student.resumes'))
        ->assertOk()
        ->assertSee('Resume Builder')
        ->assertSee(route('student.resumes'))
        ->assertSee('Build your first resume');
});

test('non-students cannot use the resume builder', function () {
    $supervisor = createResumeStudent();
    $supervisor->syncRoles([AppRole::SUPERVISOR->value]);

    $this->actingAs($supervisor)->get(route('student.resumes'))->assertForbidden();
});

test('creating a resume prefills it from the student profile', function () {
    $student = createResumeStudent([
        'first_name' => 'Juan',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'contact_number' => '09123456789',
    ]);

    $this->actingAs($student);

    Livewire::test('pages::student.resumes')->call('create')->assertRedirect();

    $resume = $student->resumes()->sole();

    expect($resume->template)->toBe(ResumeTemplate::Earist)
        ->and($resume->data['first_name'])->toBe('Juan')
        ->and($resume->data['phone'])->toBe('09123456789')
        ->and($resume->data['email'])->toBe($student->email)
        ->and($resume->fullName())->toBe('Juan Santos Dela Cruz');

    $this->get(route('student.resumes.edit', $resume))->assertOk()->assertSee('Live PDF Preview');
});

test('saving stores validated structured data and drops unknown keys', function () {
    $student = createResumeStudent();
    $resume = createResume($student);

    $this->actingAs($student);

    Livewire::test('pages::student.resume-builder', ['resume' => $resume])
        ->set('title', 'Internship Resume')
        ->set('data.first_name', 'Maria')
        ->set('data.last_name', 'Clara')
        ->set('data.objective', "Line one\nLine two")
        ->set('data.technical_skills', ['Networking', 'Excel'])
        ->set('data.injected', 'should not be stored')
        ->call('save')
        ->assertReturned(true)
        ->assertDispatched('resume-saved')
        ->assertHasNoErrors();

    $resume->refresh();

    expect($resume->title)->toBe('Internship Resume')
        ->and($resume->data['technical_skills'])->toBe(['Networking', 'Excel'])
        ->and($resume->data['objective'])->toBe("Line one\nLine two")
        ->and($resume->data)->not->toHaveKey('injected')
        ->and($resume->fileName())->toBe('Resume_Maria_Clara.pdf');
});

test('invalid input is reported and not saved, but stays in the editor', function () {
    $student = createResumeStudent();
    $resume = createResume($student, ['email' => 'old@example.com']);

    $this->actingAs($student);

    Livewire::test('pages::student.resume-builder', ['resume' => $resume])
        ->set('data.email', 'not-an-email')
        ->set('data.education.0.start_year', '20')
        ->set('data.experience', [array_merge(Resume::ENTRIES['experience'], ['start' => '2024-13'])])
        ->call('save')
        ->assertReturned(false)
        ->assertDispatched('resume-save-invalid')
        ->assertHasErrors(['data.email', 'data.education.0.start_year', 'data.experience.0.start'])
        ->assertSet('data.email', 'not-an-email');

    expect($resume->refresh()->data['email'])->toBe('old@example.com');
});

test('repeatable fields can be added, reordered, and removed', function () {
    $student = createResumeStudent();
    $resume = createResume($student, ['technical_skills' => ['A', 'B', 'C']]);

    $this->actingAs($student);

    $component = Livewire::test('pages::student.resume-builder', ['resume' => $resume])
        ->call('moveItem', 'technical_skills', 2, -1)
        ->assertSet('data.technical_skills', ['A', 'C', 'B'])
        ->call('removeItem', 'technical_skills', 0)
        ->assertSet('data.technical_skills', ['C', 'B'])
        ->call('addItem', 'technical_skills', 1)
        ->assertSet('data.technical_skills', ['C', '', 'B'])
        ->call('moveItem', 'technical_skills', 0, -1)
        ->assertSet('data.technical_skills', ['C', '', 'B'])
        ->call('addItem', 'experience')
        ->call('addItem', 'achievements');

    expect($component->get('data.experience'))->toBe([Resume::ENTRIES['experience']])
        ->and($resume->refresh()->data['technical_skills'])->toBe(['C', '', 'B'])
        ->and($resume->data['achievements'])->toHaveCount(1);

    $component->call('addItem', 'title')->assertNotFound();
    expect($resume->refresh()->title)->toBe('My OJT Resume');
});

test('switching templates keeps every field', function () {
    $student = createResumeStudent();
    $resume = createResume($student, ['first_name' => 'Juan', 'personal_skills' => ['Teamwork']]);

    $this->actingAs($student);

    $component = Livewire::test('pages::student.resume-builder', ['resume' => $resume]);

    foreach (ResumeTemplate::cases() as $template) {
        $component->call('switchTemplate', $template->value)->assertSet('template', $template->value);

        $resume->refresh();
        expect($resume->template)->toBe($template)
            ->and($resume->data['first_name'])->toBe('Juan')
            ->and($resume->data['personal_skills'])->toBe(['Teamwork']);
    }
});

test('every template renders a downloadable PDF named after the student', function () {
    $student = createResumeStudent();
    $resume = createResume($student, [
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'technical_skills' => array_map(fn ($i) => "Skill {$i}", range(1, 80)),
    ]);

    $this->actingAs($student);

    foreach (ResumeTemplate::cases() as $template) {
        $resume->update(['template' => $template]);

        $response = $this->get(route('student.resumes.download', $resume))->assertOk();

        expect($response->headers->get('Content-Type'))->toBe('application/pdf')
            ->and($response->headers->get('Content-Disposition'))->toContain('attachment; filename="Resume_Juan_Dela_Cruz.pdf"')
            ->and($response->getContent())->toStartWith('%PDF');
    }

    $this->get(route('student.resumes.preview', $resume))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename="Resume_Juan_Dela_Cruz.pdf"');
});

test('empty entries and optional sections are left out of the document', function () {
    $student = createResumeStudent();
    $resume = createResume($student, [
        'technical_skills' => ['  Excel ', '', '   '],
        'education' => [
            array_merge(Resume::ENTRIES['education'], ['program' => 'BSIT', 'start_year' => 2023, 'end_year' => '2027', 'current' => true]),
            Resume::ENTRIES['education'],
        ],
        'experience' => [Resume::ENTRIES['experience']],
        'show_certification' => false,
    ]);

    $view = ResumeDocument::viewData($resume);

    expect($view['technicalSkills'])->toBe(['Excel'])
        ->and($view['education'])->toHaveCount(1)
        ->and($view['education'][0]['period'])->toBe('Graduating, 2027')
        ->and($view['experience'])->toBe([])
        ->and($view['certification'])->toBeNull();

    $html = view(ResumeTemplate::Earist->view(), $view)->render();

    expect($html)->not->toContain('Working Experience')
        ->and($html)->not->toContain(Resume::CERTIFICATION);
});

test('students can upload, replace, and remove their photo and signature', function () {
    $student = createResumeStudent();
    $resume = createResume($student);

    $this->actingAs($student);

    $photo = Livewire::test('resume.photo', ['resumeId' => $resume->id])
        ->set('upload', UploadedFile::fake()->image('photo.jpg', 600, 600))
        ->call('saveUpload')
        ->assertReturned(true)
        ->assertDispatched('resume-saved');

    $first = $resume->refresh()->photo_path;
    Storage::disk('local')->assertExists($first);

    $photo->set('upload', UploadedFile::fake()->image('new.png', 400, 400))->call('saveUpload');
    $second = $resume->refresh()->photo_path;

    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($second);
    $this->get(route('student.resumes.image', [$resume, 'photo']))->assertOk();

    $photo->call('removeImage');
    Storage::disk('local')->assertMissing($second);
    expect($resume->refresh()->photo_path)->toBeNull();

    Livewire::test('resume.signature', ['resumeId' => $resume->id])
        ->set('upload', UploadedFile::fake()->image('signature.png', 500, 150))
        ->call('saveUpload')
        ->assertReturned(true);

    expect($resume->refresh()->signature_path)->not->toBeNull();
    expect(ResumeDocument::viewData($resume)['signature']['ratio'])->toEqualWithDelta(500 / 150, 0.01);
});

test('invalid uploads are rejected', function () {
    $student = createResumeStudent();
    $resume = createResume($student);

    $this->actingAs($student);

    Livewire::test('resume.photo', ['resumeId' => $resume->id])
        ->set('upload', UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'))
        ->call('saveUpload')
        ->assertReturned(false)
        ->assertHasErrors('upload');

    Livewire::test('resume.photo', ['resumeId' => $resume->id])
        ->set('upload', UploadedFile::fake()->image('tiny.jpg', 50, 50))
        ->call('saveUpload')
        ->assertHasErrors('upload');

    Livewire::test('resume.signature', ['resumeId' => $resume->id])
        ->set('upload', UploadedFile::fake()->image('huge.png', 600, 200)->size(3000))
        ->call('saveUpload')
        ->assertHasErrors('upload');

    expect($resume->refresh()->photo_path)->toBeNull()->and($resume->signature_path)->toBeNull();
});

test('duplicating copies data and images; deleting removes files', function () {
    $student = createResumeStudent();
    $resume = createResume($student, ['first_name' => 'Juan']);
    Storage::disk('local')->put('resumes/'.$student->id.'/photo.jpg', 'x');
    $resume->update(['photo_path' => 'resumes/'.$student->id.'/photo.jpg']);

    $this->actingAs($student);

    $list = Livewire::test('pages::student.resumes')->call('duplicate', $resume->id);

    $copy = $student->resumes()->whereKeyNot($resume->id)->sole();

    expect($copy->title)->toBe('My OJT Resume (Copy)')
        ->and($copy->data['first_name'])->toBe('Juan')
        ->and($copy->photo_path)->not->toBe($resume->photo_path);
    Storage::disk('local')->assertExists($copy->photo_path);

    $list->call('confirmDelete', $copy->id)->call('delete');

    expect(Resume::find($copy->id))->toBeNull();
    Storage::disk('local')->assertMissing($copy->photo_path);
    Storage::disk('local')->assertExists($resume->photo_path);
});

test('students cannot view or modify another student\'s resume', function () {
    $owner = createResumeStudent();
    $intruder = createResumeStudent();
    $resume = createResume($owner);
    Storage::disk('local')->put('resumes/'.$owner->id.'/sig.png', 'x');
    $resume->update(['signature_path' => 'resumes/'.$owner->id.'/sig.png']);

    $this->actingAs($intruder);

    $this->get(route('student.resumes.edit', $resume))->assertNotFound();
    $this->get(route('student.resumes.preview', $resume))->assertNotFound();
    $this->get(route('student.resumes.download', $resume))->assertNotFound();
    $this->get(route('student.resumes.image', [$resume, 'signature']))->assertNotFound();
    $this->get(route('student.resumes'))->assertDontSee($resume->title.'</a>', false);

    Livewire::test('pages::student.resumes')->call('duplicate', $resume->id)->assertNotFound();
    Livewire::test('pages::student.resumes')->call('confirmDelete', $resume->id)->assertNotFound();
    Livewire::test('resume.photo', ['resumeId' => $resume->id])->assertNotFound();

    expect(Resume::find($resume->id))->not->toBeNull();
});

test('students cannot create or duplicate beyond the resume limit', function () {
    $student = createResumeStudent();
    $resume = createResume($student);

    foreach (range(2, Resume::MAX_PER_STUDENT) as $i) {
        createResume($student);
    }

    $this->actingAs($student);

    Livewire::test('pages::student.resumes')
        ->call('create')->assertNoRedirect()
        ->call('duplicate', $resume->id);

    expect($student->resumes()->count())->toBe(Resume::MAX_PER_STUDENT);
});
