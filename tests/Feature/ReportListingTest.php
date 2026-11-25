<?php

namespace Tests\Feature;

use App\Enums\EmploymentQuality;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MedicalLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ReportListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_filters_stay_hidden_and_the_excel_uses_them(): void
    {
        $admin = User::factory()->create();
        $housing = Department::query()->create(['name' => 'Vivienda']);
        $legal = Department::query()->create(['name' => 'Jurídico']);
        $this->employee('11111111-1', 'Ana', 'Pérez', $housing->id);
        $this->employee('22222222-2', 'Bruno', 'Soto', $legal->id);

        $page = $this->actingAs($admin)->get(route('employees.index'));

        $page->assertOk()
            ->assertSee('x-show="filters"', false)
            ->assertSee('x-cloak', false)
            ->assertSee('Exportar Excel')
            ->assertSee('name="per_page"', false)
            ->assertSee('sort=rut', false);

        foreach (range(1, 11) as $index) {
            $this->employee('1000000'.$index.'-'.(($index % 9) + 1), 'Persona', 'Extra'.$index, $legal->id);
        }

        $this->actingAs($admin)->get(route('employees.index', ['per_page' => 12]))
            ->assertOk()
            ->assertSee('Siguiente');

        $this->actingAs($admin)->get(route('employees.index', ['per_page' => 25]))
            ->assertOk()
            ->assertDontSee('Siguiente');

        $sorted = $this->actingAs($admin)->get(route('employees.index', ['sort' => 'rut', 'direction' => 'desc']));
        $sorted->assertOk();
        $content = $sorted->getContent();
        $this->assertLessThan(strpos($content, 'Ana'), strpos($content, 'Bruno'));

        $rows = $this->sheet($this->actingAs($admin)->get(route('employees.export', ['department_id' => $housing->id])));

        $this->assertSame('RUT', $rows[0][0]);
        $this->assertCount(2, $rows);
        $this->assertSame('11.111.111-1', $rows[1][0]);
    }

    public function test_medical_leave_excel_respects_the_date_filter(): void
    {
        $admin = User::factory()->create();
        $employee = $this->employee('33333333-3', 'Carla', 'Díaz', null);
        $type = LeaveType::query()->create([
            'name' => 'Enfermedad común',
            'internal_code' => 'ENF',
        ]);
        MedicalLeave::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'starts_on' => '2026-01-10',
            'ends_on' => '2026-01-12',
            'days_count' => 3,
        ]);
        MedicalLeave::query()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'starts_on' => '2026-03-01',
            'ends_on' => '2026-03-05',
            'days_count' => 5,
        ]);

        $page = $this->actingAs($admin)->get(route('medical-leaves.index'));
        $page->assertOk()
            ->assertSee('x-show="filters"', false)
            ->assertSee('sort=days', false)
            ->assertSee('name="per_page"', false);

        $rows = $this->sheet($this->actingAs($admin)->get(route('medical-leaves.export', [
            'starts_from' => '2026-02-01',
            'leave_type_id' => $type->id,
        ])));

        $this->assertCount(2, $rows);
        $this->assertSame('01-03-2026', $rows[1][3]);
        $this->assertSame(5, $rows[1][5]);
    }

    public function test_reports_require_authentication(): void
    {
        $this->get(route('employees.export'))->assertRedirect(route('login'));
        $this->get(route('medical-leaves.export'))->assertRedirect(route('login'));
    }

    private function employee(string $rut, string $firstName, string $lastName, ?int $departmentId): Employee
    {
        return Employee::query()->create([
            'rut' => $rut,
            'first_name' => $firstName,
            'paternal_last_name' => $lastName,
            'maternal_last_name' => 'Rojas',
            'birth_date' => '1990-01-01',
            'employment_quality' => EmploymentQuality::Contract,
            'department_id' => $departmentId,
        ]);
    }

    /**
     * @return list<list<mixed>>
     */
    private function sheet(TestResponse $response): array
    {
        $response->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        $reader = new Reader;
        $reader->open($path);
        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break;
        }

        $reader->close();

        return $rows;
    }
}
