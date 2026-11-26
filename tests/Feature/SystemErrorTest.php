<?php

namespace Tests\Feature;

use App\Enums\ErrorReview;
use App\Enums\ErrorSeverity;
use App\Models\SystemError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class SystemErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_grave_error_stays_in_the_bell_until_it_is_reviewed(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->create();
        Route::middleware('web')->get('/_test/system-failure', function (): void {
            throw new RuntimeException('Fallo de prueba del sistema');
        });
        Route::middleware('web')->get('/_test/older-failure', function (): void {
            throw new RuntimeException('Fallo anterior del sistema');
        });

        $this->actingAs($super)->get('/_test/older-failure')->assertServerError();
        $this->actingAs($super)->get('/_test/system-failure')->assertServerError();

        $this->assertDatabaseHas('system_errors', [
            'message' => 'Fallo de prueba del sistema',
            'severity' => ErrorSeverity::Grave->value,
            'status_code' => 500,
            'user_id' => $super->id,
        ]);

        $latest = SystemError::query()->where('message', 'Fallo de prueba del sistema')->firstOrFail();
        $this->assertSame('tests/Feature/SystemErrorTest.php', $latest->file);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Fallo de prueba del sistema')
            ->assertDontSee('Errores del sistema');

        $this->actingAs($admin)->get(route('system-errors.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($super)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Fallo de prueba del sistema')
            ->assertSee('2 errores graves sin revisar')
            ->assertDontSee('Fallo anterior del sistema');

        $this->actingAs($super)->get(route('system-errors.show', $latest))
            ->assertOk()
            ->assertSee('tests/Feature/SystemErrorTest.php:'.$latest->line)
            ->assertSee('Rastro');

        $this->actingAs($super)->post(route('system-errors.review', $latest), [
            'review' => ErrorReview::Resolved->value,
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($super)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Fallo anterior del sistema')
            ->assertDontSee('Fallo de prueba del sistema');
    }

    public function test_expected_client_errors_do_not_raise_the_bell(): void
    {
        $super = User::factory()->superAdmin()->create();
        Route::middleware('web')->get('/_test/bad-request', function (): void {
            abort(400, 'Solicitud incorrecta de prueba');
        });

        $this->actingAs($super)->get('/_test/bad-request')->assertStatus(400);
        $this->actingAs($super)->get('/no-existe-en-el-sistema')->assertNotFound();

        $this->assertDatabaseHas('system_errors', [
            'message' => 'Solicitud incorrecta de prueba',
            'severity' => ErrorSeverity::Notice->value,
            'status_code' => 400,
        ]);
        $this->assertSame(1, SystemError::query()->count());

        $this->actingAs($super)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No hay errores graves pendientes.')
            ->assertDontSee('Solicitud incorrecta de prueba');

        $this->actingAs($super)->get(route('system-errors.index', ['severity' => ErrorSeverity::Notice->value]))
            ->assertOk()
            ->assertSee('Solicitud incorrecta de prueba');
    }

    public function test_marking_an_error_as_seen_dismisses_it(): void
    {
        $super = User::factory()->superAdmin()->create();
        $error = SystemError::query()->create([
            'severity' => ErrorSeverity::Grave,
            'status_code' => 500,
            'exception' => RuntimeException::class,
            'message' => 'Error pendiente de lectura',
        ]);

        $this->actingAs($super)->post(route('system-errors.review', $error), [
            'review' => ErrorReview::Seen->value,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNotNull($error->refresh()->reviewed_at);
        $this->assertSame(ErrorReview::Seen, $error->review);
        $this->assertSame($super->id, $error->reviewed_by);

        $this->actingAs($super)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No hay errores graves pendientes.')
            ->assertDontSee('Error pendiente de lectura');
    }
}
