<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        // Password must meet requirements: min 12 chars, mixed case, numbers, symbols
        // Using unique password to pass uncompromised() check
        $password = 'Xq7$kLm9#Np2@Vz!';

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', $password)
            ->set('password_confirmation', $password)
            ->call('register');

        $component->assertHasNoErrors();
        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
