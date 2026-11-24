<?php

namespace Tests\Feature;

use App\Enums\EmploymentQuality;
use App\Enums\StaffLevel;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use Database\Seeders\EmployeeRosterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRosterSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_roster_joins_names_and_matches_existing_units(): void
    {
        $this->travelTo('2026-11-24');

        $housing = Department::query()->create([
            'name' => 'Departamento de Planes y Programas de Vivienda y Equipamiento',
        ]);
        $urban = Department::query()->create([
            'name' => 'Departamento de Desarrollo Urbano e Infraestructura',
        ]);
        $coordination = Section::query()->create([
            'name' => 'Sección Coordinación Provincial y Comunal',
        ]);

        $this->seed(EmployeeRosterSeeder::class);

        $mariana = Employee::query()->where('rut', '17856267-3')->first();
        $yesica = Employee::query()->where('rut', '16535301-3')->first();
        $ricardo = Employee::query()->where('rut', '13957371-4')->first();
        $isadora = Employee::query()->where('rut', '16768840-3')->first();
        $carolina = Employee::query()->where('rut', '17459257-8')->first();
        $francisco = Employee::query()->where('rut', '16152208-2')->first();

        $this->assertNotNull($mariana);
        $this->assertSame('Mariana De los Angeles', $mariana->first_name);
        $this->assertSame($coordination->id, $mariana->section_id);
        $this->assertNull($mariana->department_id);
        $this->assertSame(35, $mariana->age);
        $this->assertNull($mariana->sex);

        $this->assertNotNull($yesica);
        $this->assertSame('Yesica Alejandra', $yesica->first_name);
        $this->assertSame($housing->id, $yesica->department_id);
        $this->assertNull($yesica->section_id);
        $this->assertSame('yangermayera@minvu.cl', $yesica->email);
        $this->assertSame('2022-10-24', $yesica->hired_on->toDateString());
        $this->assertSame(StaffLevel::Professional, $yesica->staff_level);
        $this->assertSame(EmploymentQuality::Contract, $yesica->employment_quality);

        $this->assertNotNull($ricardo);
        $this->assertSame($urban->id, $ricardo->department_id);

        $this->assertNotNull($isadora);
        $this->assertSame('Isadora Esperanza', $isadora->first_name);
        $this->assertSame($housing->id, $isadora->department_id);

        $this->assertNotNull($carolina);
        $this->assertSame('Carolina', $carolina->first_name);
        $this->assertSame(EmploymentQuality::Permanent, $carolina->employment_quality);
        $this->assertSame(StaffLevel::Directive, $carolina->staff_level);
        $this->assertNull($carolina->department_id);
        $this->assertNull($carolina->section_id);

        $this->assertNotNull($francisco);
        $this->assertSame(StaffLevel::Professional, $francisco->staff_level);

        $total = Employee::query()->count();
        $this->seed(EmployeeRosterSeeder::class);
        $this->assertSame($total, Employee::query()->count());
        $this->assertSame($coordination->id, $mariana->fresh()->section_id);
    }
}
