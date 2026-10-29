<?php

use App\Livewire\Forms\SupervisorForm;

test('internal supervisor type prefills the institution and external clears it', function () {
    $form = (new ReflectionClass(SupervisorForm::class))->newInstanceWithoutConstructor();

    $form->supervisor_type = 'internal';
    $form->applySupervisorType();

    expect($form->company_name)->toBe(SupervisorForm::INTERNAL_INSTITUTION)
        ->and($form->company_address)->toBe(SupervisorForm::INTERNAL_ADDRESS);

    $form->supervisor_type = 'external';
    $form->applySupervisorType();

    expect($form->company_name)->toBe('')
        ->and($form->company_address)->toBe('');
});
