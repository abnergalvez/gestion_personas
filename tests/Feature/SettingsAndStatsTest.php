<?php

namespace Tests\Feature;

use App\Enums\ContractModality;
use App\Enums\ContractQuality;
use App\Enums\EmploymentQuality;
use App\Models\ActivityLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MedicalLeave;
use App\Models\User;
use App\Notifications\PersonnelNotice;
use App\Support\PersonnelNotifier;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsAndStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_super_administrator_saves_mail_and_notice_settings_without_logging_secrets(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('settings.edit'))->assertRedirect(route('dashboard'));

        $payload = $this->payload([
            'brevo_password' => 'Clave.Secreta.2026',
            'notify_contracts' => '1',
            'recipients' => "jefa@minvu-nuble.cl\n",
            'contract_days' => 45,
        ]);

        $this->actingAs($super)->put(route('settings.update'), $payload)
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('success');

        $this->assertSame('log', config('mail.default'));
        $this->assertSame('Clave.Secreta.2026', Settings::get('mail.brevo.password'));
        $this->assertSame(45, Settings::contractDays());
        $this->assertTrue(Settings::enabled('notify.contracts'));

        $payload['brevo_password'] = '';
        $this->actingAs($super)->put(route('settings.update'), $payload)->assertRedirect(route('settings.edit'));
        $this->assertSame('Clave.Secreta.2026', Settings::get('mail.brevo.password'));

        $log = ActivityLog::query()->where('description', 'Actualizó la configuración general.')->firstOrFail();
        $this->assertStringNotContainsString('Clave.Secreta.2026', json_encode($log->properties, JSON_THROW_ON_ERROR));

        $this->actingAs($super)->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Configuración general')
            ->assertSee('Ya hay una clave guardada');
    }

    public function test_section_lists_show_a_short_statistics_row(): void
    {
        $admin = User::factory()->create();
        $employee = Employee::query()->create([
            'rut' => '11111111-1',
            'first_name' => 'Ana',
            'paternal_last_name' => 'Pérez',
            'maternal_last_name' => 'Rojas',
            'birth_date' => '1990-04-04',
            'employment_quality' => EmploymentQuality::Permanent,
        ]);
        Contract::query()->create([
            'employee_id' => $employee->id,
            'quality' => ContractQuality::Contract,
            'modality' => ContractModality::Direct,
            'grade' => '8',
            'position' => 'Profesional',
            'starts_on' => today()->subMonth()->toDateString(),
            'ends_on' => today()->addDays(10)->toDateString(),
        ]);

        $pages = [
            'employees.index' => ['Dotación', 'Planta', 'Sin departamento'],
            'birthdays.index' => ['Esta semana', 'Próxima semana'],
            'training-courses.index' => ['Registrados', 'Vigentes', 'Por vencer'],
            'medical-leaves.index' => ['Vigentes', 'Promedio de días'],
            'family-dependents.index' => ['Cargas', 'Cumplen 18', 'Sin certificado'],
            'contracts.index' => ['Vigentes', 'Por vencer', 'Sin término'],
            'permits.index' => ['En curso', 'Horas del mes'],
            'evaluations.index' => ['Del periodo', 'En apelación', 'Bajo 4,0'],
        ];

        foreach ($pages as $route => $labels) {
            $response = $this->actingAs($admin)->get(route($route))->assertOk();

            foreach ($labels as $label) {
                $response->assertSee($label);
            }
        }
    }

    public function test_notices_follow_the_saved_switches(): void
    {
        Notification::fake();
        $employee = Employee::query()->create([
            'rut' => '22222222-2',
            'first_name' => 'Bruno',
            'paternal_last_name' => 'Soto',
            'maternal_last_name' => 'Rojas',
            'birth_date' => '1988-05-05',
            'employment_quality' => EmploymentQuality::Contract,
        ]);
        $type = LeaveType::query()->create([
            'name' => 'Enfermedad común',
            'internal_code' => 'ENF',
        ]);

        MedicalLeave::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'starts_on' => today()->toDateString(),
            'ends_on' => today()->addDays(2)->toDateString(),
            'days_count' => 3,
        ]);
        Notification::assertNothingSent();

        Settings::store([
            'mail.mailer' => 'log',
            'notify.recipients' => 'jefa@minvu-nuble.cl',
            'notify.medical_leaves' => '1',
            'notify.contracts' => '1',
            'management.contract_days' => '30',
        ]);
        MedicalLeave::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'starts_on' => today()->addDays(4)->toDateString(),
            'ends_on' => today()->addDays(6)->toDateString(),
            'days_count' => 3,
        ]);
        Notification::assertSentOnDemand(PersonnelNotice::class, fn (PersonnelNotice $notice): bool => $notice->subjectLine === 'Nueva licencia médica');

        Contract::query()->create([
            'employee_id' => $employee->id,
            'quality' => ContractQuality::Contract,
            'modality' => ContractModality::Direct,
            'grade' => '8',
            'position' => 'Analista',
            'starts_on' => today()->subDay()->toDateString(),
            'ends_on' => today()->addDays(12)->toDateString(),
        ]);

        $this->assertSame('sent', PersonnelNotifier::digest(force: true));
        Notification::assertSentOnDemand(PersonnelNotice::class, fn (PersonnelNotice $notice): bool => $notice->subjectLine === 'Avisos de personas'
            && str_contains($notice->message, 'Bruno Soto Rojas'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'mailer' => 'log',
            'from_name' => 'Personas Ñuble',
            'from_address' => 'avisos@minvu-nuble.cl',
            'recipients' => '',
            'notify_contracts' => '0',
            'notify_birthdays' => '0',
            'notify_courses' => '0',
            'notify_medical_leaves' => '0',
            'notify_permits' => '0',
            'notify_evaluations' => '0',
            'contract_days' => 60,
            'course_days' => 30,
            'low_score' => 4,
        ], $overrides);
    }
}
