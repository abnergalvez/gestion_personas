<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class UserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_operates_the_system_except_the_catalogs(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->get(route('leave-types.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->get(route('employees.index'))
            ->assertOk()
            ->assertDontSee('Mantenedores');

        $this->actingAs($admin)
            ->post(route('departments.store'), ['name' => 'Fiscalización'])
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => ActivityAction::Denied->value,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => ActivityAction::Created->value,
            'description' => 'Registró un departamento.',
        ]);
    }

    public function test_the_super_administrator_manages_users_and_cannot_remove_the_last_super_admin(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Mantenedores')
            ->assertSee('Registro de acciones');

        $this->actingAs($super)->post(route('users.store'), [
            'name' => 'Ana Pérez',
            'email' => 'ana@minvu-nuble.cl',
            'role' => 'admin',
            'password' => 'Ana.Nuble.2026',
            'password_confirmation' => 'Ana.Nuble.2026',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'ana@minvu-nuble.cl',
            'role' => 'admin',
        ]);

        $this->actingAs($super)
            ->put(route('users.update', $super), [
                'name' => $super->name,
                'email' => $super->email,
                'role' => 'admin',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('super_admin', $super->fresh()->role->value);

        $this->actingAs($super)
            ->delete(route('users.destroy', $super))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($super);
    }

    public function test_access_attempts_are_recorded_without_the_password(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'Minvu.Nuble.2026',
        ]);

        $this->from(route('login'))->post('/login', [
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'ClaveMala99',
        ])->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => 'admin@minvu-nuble.cl',
            'password' => 'Minvu.Nuble.2026',
        ])->assertRedirect(route('dashboard'));

        $rejected = ActivityLog::query()->where('action', ActivityAction::LoginFailed)->first();

        $this->assertNotNull($rejected);
        $this->assertSame('admin@minvu-nuble.cl', $rejected->user_email);
        $this->assertStringNotContainsString('ClaveMala99', json_encode($rejected->toArray(), JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('Minvu.Nuble.2026', json_encode(ActivityLog::query()->get()->toArray(), JSON_UNESCAPED_UNICODE));
        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::Login->value,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::Logout->value,
            'user_id' => $admin->id,
        ]);

        $this->get(route('employees.index'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::AccessDenied->value,
        ]);
    }

    public function test_repeated_failed_access_is_recorded_as_blocked(): void
    {
        User::factory()->create([
            'email' => 'bloqueo@minvu-nuble.cl',
            'password' => 'Minvu.Nuble.2026',
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/login', [
                'email' => 'bloqueo@minvu-nuble.cl',
                'password' => 'ClaveMala99',
            ]);
        }

        $this->assertDatabaseHas('activity_logs', [
            'user_email' => 'bloqueo@minvu-nuble.cl',
            'description' => 'Intento de acceso bloqueado por demasiados intentos.',
        ]);
        $this->assertStringNotContainsString(
            'ClaveMala99',
            json_encode(ActivityLog::query()->get()->toArray(), JSON_UNESCAPED_UNICODE)
        );
    }

    public function test_the_user_form_explains_why_the_password_was_rejected(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->followingRedirects()
            ->actingAs($super)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Ana Pérez',
                'email' => 'ana@minvu-nuble.cl',
                'role' => 'admin',
                'password' => 'corta',
                'password_confirmation' => 'distinta',
            ])
            ->assertOk()
            ->assertSee('No se pudo guardar. Corrija lo siguiente:')
            ->assertSee('La contraseña debe contener mayúsculas y minúsculas.')
            ->assertSee('La confirmación no coincide con contraseña.');
    }

    public function test_the_activity_log_accepts_session_errors_stored_as_an_array(): void
    {
        $user = User::factory()->superAdmin()->create();
        $session = $this->app['session']->driver();
        $session->start();

        $request = Request::create('/users', 'POST');
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $user);
        $request->session()->put('errors', [
            'email' => ['El correo ya está registrado.'],
        ]);

        $route = new Route(['POST'], '/users', fn () => null);
        $route->name('users.store');
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        ActivityLogger::fromRequest($request, redirect('/users'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => ActivityAction::Failed->value,
            'description' => 'No se pudo registrar un usuario.',
        ]);
    }
}
