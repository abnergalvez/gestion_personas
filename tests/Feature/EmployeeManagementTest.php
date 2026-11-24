<?php

namespace Tests\Feature;

use App\Enums\EmploymentQuality;
use App\Enums\MaritalStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_register_an_employee_with_a_valid_rut(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'rut' => '12.345.678-5',
            'first_name' => 'Ana',
            'paternal_last_name' => 'Pérez',
            'maternal_last_name' => 'Soto',
            'birth_date' => '1990-05-20',
            'profession' => 'Arquitecta',
            'marital_status' => MaritalStatus::Single->value,
            'grade' => '10',
            'employment_quality' => EmploymentQuality::Contract->value,
        ]);

        $employee = Employee::query()->first();

        $response->assertRedirect(route('employees.show', $employee));
        $this->assertSame('12345678-5', $employee->rut);
        $this->assertSame('Ana Pérez Soto', $employee->full_name);
    }

    public function test_an_invalid_rut_is_rejected(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->from(route('employees.create'))->post(route('employees.store'), [
            'rut' => '12.345.678-9',
            'first_name' => 'Ana',
            'paternal_last_name' => 'Pérez',
            'maternal_last_name' => 'Soto',
            'birth_date' => '1990-05-20',
            'marital_status' => MaritalStatus::Single->value,
            'employment_quality' => EmploymentQuality::Fee->value,
        ])->assertRedirect(route('employees.create'))->assertSessionHasErrors('rut');

        $this->assertDatabaseCount('employees', 0);
    }

    public function test_private_files_require_authentication(): void
    {
        $this->get(route('files.show', ['path' => 'employees/photos/example.jpg']))->assertRedirect(route('login'));
    }
}
