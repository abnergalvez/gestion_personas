<?php

namespace Tests\Feature;

use App\Enums\EducationLevel;
use App\Enums\EmploymentQuality;
use App\Enums\FamilyRelationship;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FamilyDependent;
use App\Models\TrainingCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class SectionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_reports_use_the_employee_department_and_download_the_filtered_rows(): void
    {
        $admin = User::factory()->create();
        $housing = Department::query()->create(['name' => 'Vivienda']);
        $legal = Department::query()->create(['name' => 'Jurídico']);
        $ana = $this->employee('11111111-1', 'Ana', 'Pérez', $housing->id);
        $bruno = $this->employee('22222222-2', 'Bruno', 'Soto', $legal->id);
        TrainingCourse::query()->create([
            'employee_id' => $ana->id,
            'name' => 'Curso de vivienda',
            'duration' => '8 horas',
            'certified_on' => '2026-01-10',
        ]);
        TrainingCourse::query()->create([
            'employee_id' => $bruno->id,
            'name' => 'Curso jurídico',
            'duration' => '4 horas',
            'certified_on' => '2026-02-10',
        ]);

        $this->actingAs($admin)->get(route('training-courses.index', [
            'employee_department_id' => $housing->id,
        ]))->assertOk()
            ->assertSee('Curso de vivienda')
            ->assertDontSee('Curso jurídico')
            ->assertSee('Departamento del funcionario')
            ->assertSee('Nombre del curso')
            ->assertSee('x-show="filters"', false)
            ->assertSee('name="per_page"', false)
            ->assertSee('sort=name', false);

        $rows = $this->sheet($this->actingAs($admin)->get(route('training-courses.export', [
            'employee_department_id' => $housing->id,
        ])));

        $this->assertSame('Curso', $rows[0][9]);
        $this->assertSame('Curso de vivienda', $rows[1][9]);
        $this->assertCount(2, $rows);
    }

    public function test_each_section_list_can_filter_sort_and_export(): void
    {
        $admin = User::factory()->create();
        $department = Department::query()->create(['name' => 'Vivienda']);
        $employee = $this->employee('33333333-3', 'Carla', 'Díaz', $department->id);
        FamilyDependent::query()->create([
            'employee_id' => $employee->id,
            'full_name' => 'Hijo Díaz',
            'rut' => '44444444-4',
            'birth_date' => '2015-05-05',
            'education_level' => EducationLevel::Basic,
            'relationship' => FamilyRelationship::Child,
        ]);

        $pages = [
            'family-dependents.index',
            'contracts.index',
            'permits.index',
            'evaluations.index',
            'medical-leaves.index',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get(route($page))
                ->assertOk()
                ->assertSee('Exportar Excel')
                ->assertSee('Departamento del funcionario')
                ->assertSee('name="per_page"', false)
                ->assertSee('sort=', false);
        }

        $this->actingAs($admin)->get(route('family-dependents.index', [
            'relationship' => FamilyRelationship::Child->value,
            'employee_department_id' => $department->id,
        ]))->assertOk()->assertSee('Hijo Díaz');

        $rows = $this->sheet($this->actingAs($admin)->get(route('family-dependents.export', [
            'relationship' => FamilyRelationship::Child->value,
        ])));
        $this->assertSame('Hijo Díaz', $rows[1][9]);
    }

    public function test_section_exports_require_authentication(): void
    {
        foreach ([
            'training-courses.export',
            'family-dependents.export',
            'contracts.export',
            'permits.export',
            'evaluations.export',
        ] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    private function employee(string $rut, string $firstName, string $lastName, int $departmentId): Employee
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
        $reader = new Reader;
        $reader->open($response->baseResponse->getFile()->getPathname());
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
