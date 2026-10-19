<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

test('forgot password is unavailable from login', function () {
    expect(Features::enabled(Features::resetPasswords()))->toBeFalse();

    foreach (['password.request', 'password.email', 'password.reset', 'password.update'] as $route) {
        expect(Route::has($route))->toBeFalse();
    }

    $this->get(route('login'))->assertOk()->assertDontSee('Forgot your password?');
});

test('password reset endpoints are disabled', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'student@example.com'])->assertNotFound();
    $this->get('/reset-password/test-token')->assertNotFound();
    $this->post('/reset-password', [])->assertNotFound();
});
