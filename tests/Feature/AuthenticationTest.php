<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk();
    }

    public function test_an_administrator_can_sign_in_and_reach_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'Minvu.Nuble.2026',
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'Minvu.Nuble.2026',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('dashboard'))->assertOk()->assertSee('Funcionarios');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'Minvu.Nuble.2026',
        ]);

        $this->from(route('login'))->post(route('login'), [
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'incorrecta',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
