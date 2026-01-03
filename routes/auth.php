<?php

use App\Http\Controllers\Auth\SecurityKeyController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('mfa-challenge', 'pages.auth.mfa-challenge')
        ->name('mfa.challenge');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    Route::post('security-keys/authentication/options', [SecurityKeyController::class, 'authenticationOptions'])
        ->name('security-keys.authentication.options');
    Route::post('security-keys/authentication/verify', [SecurityKeyController::class, 'authenticate'])
        ->name('security-keys.authentication.verify');
});

Route::middleware('auth')->group(function () {
    Volt::route('onboarding', 'pages.auth.onboarding')
        ->name('onboarding');

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Route::post('security-keys/options', [SecurityKeyController::class, 'registrationOptions'])
        ->name('security-keys.options');
    Route::post('security-keys/register', [SecurityKeyController::class, 'register'])
        ->name('security-keys.register');
});
