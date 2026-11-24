<?php

namespace Database\Seeders;

use App\Enums\ContractModality;
use App\Enums\ContractQuality;
use App\Enums\EducationLevel;
use App\Enums\EmploymentQuality;
use App\Enums\EvaluationProcess;
use App\Enums\FamilyRelationship;
use App\Enums\MaritalStatus;
use App\Models\Contract;
use App\Models\ContractDocumentType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\FamilyDependent;
use App\Models\LeaveType;
use App\Models\MedicalLeave;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\Section;
use App\Models\TrainingCourse;
use App\Support\Rut;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $departments = $this->departments();
        $sections = $this->sections($departments);
        $employees = $this->employees($departments, $sections);
        $this->assignRoles($departments, $sections, $employees);

        $leaveTypes = $this->leaveTypes();
        $permitTypes = $this->permitTypes();
        $this->documentTypes();
        $this->courses($employees);
        $this->leaves($employees, $leaveTypes);
        $this->family($employees);
        $this->contracts($employees, $departments, $sections);
        $this->permits($employees, $permitTypes);
        $this->evaluations($employees);
    }

    /**
     * @return array<string, Department>
     */
    private function departments(): array
    {
        $names = [
            'administracion' => 'Departamento de Administración y Finanzas',
            'juridico' => 'Departamento Jurídico',
            'planificacion' => 'Departamento de Planificación y Desarrollo Urbano',
            'tecnico' => 'Departamento Técnico de Vivienda',
            'fiscalizacion' => 'Departamento de Fiscalización e Inspección',
        ];

        $departments = [];

        foreach ($names as $key => $name) {
            $departments[$key] = Department::query()->create(['name' => $name]);
        }

        return $departments;
    }

    /**
     * @param  array<string, Department>  $departments
     * @return array<string, Section>
     */
    private function sections(array $departments): array
    {
        $rows = [
            'personal' => ['Sección de Personal', 'administracion'],
            'presupuesto' => ['Sección de Presupuesto', 'administracion'],
            'proyectos' => ['Sección de Proyectos Habitacionales', 'tecnico'],
            'atencion' => ['Sección de Atención de Público', 'planificacion'],
            'regularizacion' => ['Sección de Regularización', 'fiscalizacion'],
            'convenios' => ['Sección de Convenios', 'planificacion'],
        ];

        $sections = [];

        foreach ($rows as $key => [$name, $department]) {
            $sections[$key] = Section::query()->create([
                'name' => $name,
                'department_id' => $departments[$department]->id,
            ]);
        }

        return $sections;
    }

    /**
     * @param  array<string, Department>  $departments
     * @param  array<string, Section>  $sections
     * @return array<string, Employee>
     */
    private function employees(array $departments, array $sections): array
    {
        $rows = [
            ['maria', 'María José', 'González', 'Muñoz', '1978-04-12', 'Administradora pública', MaritalStatus::Married, '6', EmploymentQuality::Contract, 'administracion', 'personal'],
            ['carlos', 'Carlos Andrés', 'Soto', 'Vidal', '1981-09-03', 'Ingeniero comercial', MaritalStatus::Married, '8', EmploymentQuality::Contract, 'administracion', 'presupuesto'],
            ['andrea', 'Andrea Paz', 'Rojas', 'Herrera', '1988-01-22', 'Asistente social', MaritalStatus::Single, '10', EmploymentQuality::Contract, 'administracion', 'personal'],
            ['felipe', 'Felipe Ignacio', 'Vargas', 'Soto', '1985-11-30', 'Contador auditor', MaritalStatus::CivilUnion, '9', EmploymentQuality::Contract, 'administracion', 'presupuesto'],
            ['javiera', 'Javiera Antonia', 'Muñoz', 'Lagos', '1975-06-18', 'Abogada', MaritalStatus::Married, '5', EmploymentQuality::Contract, 'juridico', null],
            ['rodrigo', 'Rodrigo Alonso', 'Pérez', 'Díaz', '1983-02-14', 'Abogado', MaritalStatus::Divorced, '8', EmploymentQuality::Contract, 'juridico', null],
            ['camila', 'Camila Beatriz', 'Fuentes', 'Araya', '1986-07-09', 'Arquitecta', MaritalStatus::Married, '7', EmploymentQuality::Contract, 'planificacion', 'atencion'],
            ['diego', 'Diego Alejandro', 'Contreras', 'Silva', '1990-12-01', 'Ingeniero civil', MaritalStatus::Single, '9', EmploymentQuality::Contract, 'planificacion', 'convenios'],
            ['paula', 'Paula Andrea', 'Hernández', 'Toro', '1982-03-27', 'Constructora civil', MaritalStatus::Married, '7', EmploymentQuality::Contract, 'tecnico', 'proyectos'],
            ['matias', 'Matías Benjamín', 'López', 'Cáceres', '1992-08-16', 'Ingeniero constructor', MaritalStatus::Single, '10', EmploymentQuality::Contract, 'tecnico', 'proyectos'],
            ['francisca', 'Francisca Ignacia', 'Ramírez', 'Pino', '1979-05-05', 'Abogada', MaritalStatus::Separated, '8', EmploymentQuality::Contract, 'fiscalizacion', 'regularizacion'],
            ['sebastian', 'Sebastián Alonso', 'Castro', 'Núñez', '1991-10-21', 'Ingeniero civil', MaritalStatus::Single, '11', EmploymentQuality::Contract, 'fiscalizacion', 'regularizacion'],
            ['valentina', 'Valentina Paz', 'Morales', 'Espinoza', '1994-02-08', 'Socióloga', MaritalStatus::Single, '14', EmploymentQuality::Fee, 'planificacion', 'atencion'],
            ['nicolas', 'Nicolás Alejandro', 'Figueroa', 'Bravo', '1987-09-19', 'Constructor civil', MaritalStatus::Married, '12', EmploymentQuality::Fee, 'tecnico', null],
        ];

        $employees = [];
        $body = 15432010;

        foreach ($rows as [$key, $first, $paternal, $maternal, $birth, $profession, $marital, $grade, $quality, $department, $section]) {
            $employees[$key] = Employee::query()->create([
                'rut' => $this->rut($body++),
                'first_name' => $first,
                'paternal_last_name' => $paternal,
                'maternal_last_name' => $maternal,
                'birth_date' => $birth,
                'profession' => $profession,
                'marital_status' => $marital,
                'grade' => $grade,
                'employment_quality' => $quality,
                'department_id' => $departments[$department]->id,
                'section_id' => $section ? $sections[$section]->id : null,
            ]);
        }

        return $employees;
    }

    /**
     * @param  array<string, Department>  $departments
     * @param  array<string, Section>  $sections
     * @param  array<string, Employee>  $employees
     */
    private function assignRoles(array $departments, array $sections, array $employees): void
    {
        $this->lead($departments['administracion'], $employees['maria'], $employees['carlos'], [$employees['andrea'], $employees['felipe']]);
        $this->lead($departments['juridico'], $employees['javiera'], $employees['rodrigo'], []);
        $this->lead($departments['planificacion'], $employees['camila'], $employees['diego'], [$employees['valentina']]);
        $this->lead($departments['tecnico'], $employees['paula'], $employees['matias'], [$employees['nicolas']]);
        $this->lead($departments['fiscalizacion'], $employees['francisca'], $employees['sebastian'], []);

        $this->lead($sections['personal'], $employees['maria'], $employees['andrea'], []);
        $this->lead($sections['presupuesto'], $employees['felipe'], $employees['carlos'], []);
        $this->lead($sections['proyectos'], $employees['paula'], $employees['matias'], []);
        $this->lead($sections['atencion'], $employees['camila'], $employees['diego'], [$employees['valentina']]);
        $this->lead($sections['regularizacion'], $employees['francisca'], $employees['sebastian'], []);
        $this->lead($sections['convenios'], $employees['diego'], $employees['camila'], []);
    }

    /**
     * @param  list<Employee>  $analysts
     */
    private function lead(Department|Section $unit, Employee $head, Employee $deputy, array $analysts): void
    {
        $unit->update([
            'head_employee_id' => $head->id,
            'deputy_employee_id' => $deputy->id,
        ]);

        $unit->analysts()->sync(array_map(fn (Employee $employee) => $employee->id, $analysts));
    }

    /**
     * @return array<string, LeaveType>
     */
    private function leaveTypes(): array
    {
        $rows = [
            'enfermedad' => ['Enfermedad común', 'ENF', 'Licencia por enfermedad o accidente común.'],
            'trabajo' => ['Accidente del trabajo', 'AT', 'Licencia por accidente del trabajo o enfermedad profesional.'],
            'maternal' => ['Licencia maternal', 'MAT', 'Permiso y reposo maternal.'],
            'hijo' => ['Enfermedad grave de hijo menor', 'HIJO', 'Cuidado de hijo o hija menor de un año.'],
            'preventiva' => ['Medicina preventiva', 'PREV', 'Licencia de medicina preventiva.'],
        ];

        $types = [];

        foreach ($rows as $key => [$name, $code, $description]) {
            $types[$key] = LeaveType::query()->create([
                'name' => $name,
                'internal_code' => $code,
                'description' => $description,
            ]);
        }

        return $types;
    }

    /**
     * @return array<string, PermitType>
     */
    private function permitTypes(): array
    {
        $rows = [
            'administrativo' => ['Permiso administrativo', 'Permiso con goce de remuneraciones dentro del cupo anual.'],
            'sin_goce' => ['Permiso sin goce de remuneraciones', 'Ausencia autorizada sin pago de remuneraciones.'],
            'fallecimiento' => ['Permiso por fallecimiento', 'Permiso por fallecimiento de un familiar.'],
            'compensatorio' => ['Permiso compensatorio', 'Descanso compensatorio por trabajos extraordinarios.'],
        ];

        $types = [];

        foreach ($rows as $key => [$name, $description]) {
            $types[$key] = PermitType::query()->create([
                'name' => $name,
                'description' => $description,
            ]);
        }

        return $types;
    }

    private function documentTypes(): void
    {
        $rows = [
            ['Contrato firmado', 'Ejemplar firmado del contrato o convenio.'],
            ['Resolución de nombramiento', 'Resolución que formaliza el nombramiento o la contratación.'],
            ['Anexo de contrato', 'Modificación de grado, jornada, unidad o vigencia.'],
        ];

        foreach ($rows as [$name, $description]) {
            ContractDocumentType::query()->create([
                'name' => $name,
                'description' => $description,
            ]);
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    private function courses(array $employees): void
    {
        $rows = [
            ['maria', 'Gestión de personas en el sector público', '24 horas', '2024-11-15', '2027-11-15'],
            ['carlos', 'Control de gestión presupuestaria', '16 horas', '2025-06-20', '2028-06-20'],
            ['andrea', 'Ley de protección de datos personales', '8 horas', '2025-09-02', '2026-09-02'],
            ['javiera', 'Contratación pública y probidad', '20 horas', '2023-08-10', '2026-08-10'],
            ['camila', 'Instrumentos de planificación urbana', '32 horas', '2025-03-18', '2028-03-18'],
            ['paula', 'Estándares de vivienda social', '12 horas', '2024-05-07', null],
            ['francisca', 'Procedimiento de fiscalización', '16 horas', '2022-10-11', '2025-10-11'],
            ['valentina', 'Atención de público y lenguaje claro', '8 horas', '2026-01-14', '2028-01-14'],
        ];

        foreach ($rows as [$key, $name, $duration, $certified, $valid]) {
            TrainingCourse::query()->create([
                'employee_id' => $employees[$key]->id,
                'name' => $name,
                'duration' => $duration,
                'certified_on' => $certified,
                'valid_until' => $valid,
            ]);
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, LeaveType>  $leaveTypes
     */
    private function leaves(array $employees, array $leaveTypes): void
    {
        MedicalLeave::query()->create([
            'employee_id' => $employees['camila']->id,
            'leave_type_id' => $leaveTypes['enfermedad']->id,
            'starts_on' => today()->subDays(2),
            'ends_on' => today()->addDays(12),
            'days_count' => 15,
        ]);

        MedicalLeave::query()->create([
            'employee_id' => $employees['andrea']->id,
            'leave_type_id' => $leaveTypes['hijo']->id,
            'starts_on' => today()->subMonths(2),
            'ends_on' => today()->subMonths(2)->addDays(6),
            'days_count' => 7,
        ]);

        MedicalLeave::query()->create([
            'employee_id' => $employees['matias']->id,
            'leave_type_id' => $leaveTypes['trabajo']->id,
            'starts_on' => today()->subDays(40),
            'ends_on' => today()->subDays(26),
            'days_count' => 15,
        ]);

        MedicalLeave::query()->create([
            'employee_id' => $employees['felipe']->id,
            'leave_type_id' => $leaveTypes['preventiva']->id,
            'starts_on' => today()->subMonths(5)->startOfMonth(),
            'ends_on' => today()->subMonths(5)->startOfMonth()->addDays(2),
            'days_count' => 3,
        ]);

        MedicalLeave::query()->create([
            'employee_id' => $employees['paula']->id,
            'leave_type_id' => $leaveTypes['enfermedad']->id,
            'starts_on' => today()->subMonths(4),
            'ends_on' => today()->subMonths(4)->addDays(4),
            'days_count' => 5,
        ]);
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    private function family(array $employees): void
    {
        $body = 20111020;
        $rows = [
            ['maria', 'Pedro González Muñoz', 'spouse', EducationLevel::Higher, '1976-02-11'],
            ['maria', 'Amanda González Rojas', 'child', EducationLevel::Secondary, '2008-07-19'],
            ['carlos', 'Elena Vidal Soto', 'spouse', EducationLevel::Technical, '1983-11-02'],
            ['javiera', 'Tomás Muñoz Lagos', 'child', EducationLevel::Basic, '2014-04-30'],
            ['camila', 'Martín Fuentes Araya', 'child', EducationLevel::Preschool, '2021-09-12'],
            ['paula', 'Rosa Toro Hernández', 'parent', EducationLevel::Basic, '1956-01-25'],
            ['francisca', 'Elena Ramírez Pino', 'child', EducationLevel::Secondary, '2006-12-03'],
            ['nicolas', 'Carla Bravo Figueroa', 'spouse', EducationLevel::Higher, '1989-06-14'],
        ];

        foreach ($rows as [$key, $name, $relationship, $education, $birth]) {
            FamilyDependent::query()->create([
                'employee_id' => $employees[$key]->id,
                'full_name' => $name,
                'rut' => $this->rut($body++),
                'birth_date' => $birth,
                'education_level' => $education,
                'relationship' => FamilyRelationship::from($relationship),
            ]);
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, Department>  $departments
     * @param  array<string, Section>  $sections
     */
    private function contracts(array $employees, array $departments, array $sections): void
    {
        Contract::query()->create([
            'employee_id' => $employees['maria']->id,
            'quality' => ContractQuality::Contract,
            'modality' => ContractModality::Contest,
            'grade' => '8',
            'position' => 'Profesional de apoyo',
            'starts_on' => '2018-03-01',
            'ends_on' => '2023-02-28',
            'department_id' => $departments['administracion']->id,
            'section_id' => $sections['personal']->id,
        ]);

        $current = [
            ['maria', ContractQuality::Contract, ContractModality::Contest, '6', 'Jefa de departamento', '2023-03-01', null, 'administracion', 'personal'],
            ['carlos', ContractQuality::Contract, ContractModality::Contest, '8', 'Profesional', '2022-01-01', null, 'administracion', 'presupuesto'],
            ['andrea', ContractQuality::Contract, ContractModality::Direct, '10', 'Asistente social', '2024-04-01', today()->addDays(30)->toDateString(), 'administracion', 'personal'],
            ['felipe', ContractQuality::Contract, ContractModality::Contest, '9', 'Encargado de presupuesto', '2021-05-01', null, 'administracion', 'presupuesto'],
            ['javiera', ContractQuality::Permanent, ContractModality::Contest, '5', 'Jefa jurídica', '2016-08-01', null, 'juridico', null],
            ['rodrigo', ContractQuality::Contract, ContractModality::Direct, '8', 'Abogado', '2020-09-01', null, 'juridico', null],
            ['camila', ContractQuality::Contract, ContractModality::Contest, '7', 'Jefa de planificación', '2019-01-15', null, 'planificacion', 'atencion'],
            ['diego', ContractQuality::Contract, ContractModality::Contest, '9', 'Profesional de convenios', '2022-06-01', null, 'planificacion', 'convenios'],
            ['paula', ContractQuality::Contract, ContractModality::Contest, '7', 'Jefa técnica', '2017-11-01', null, 'tecnico', 'proyectos'],
            ['matias', ContractQuality::Contract, ContractModality::Direct, '10', 'Inspector técnico', '2023-07-01', null, 'tecnico', 'proyectos'],
            ['francisca', ContractQuality::Contract, ContractModality::Contest, '8', 'Jefa de fiscalización', '2018-04-01', null, 'fiscalizacion', 'regularizacion'],
            ['sebastian', ContractQuality::Substitution, ContractModality::Direct, '11', 'Profesional de reemplazo', '2026-01-02', today()->addDays(25)->toDateString(), 'fiscalizacion', 'regularizacion'],
            ['valentina', ContractQuality::Fee, ContractModality::Direct, '14', 'Profesional a honorarios', '2026-03-01', today()->addDays(20)->toDateString(), 'planificacion', 'atencion'],
            ['nicolas', ContractQuality::Fee, ContractModality::Direct, '12', 'Inspector a honorarios', '2026-02-01', today()->addDays(45)->toDateString(), 'tecnico', null],
        ];

        foreach ($current as [$key, $quality, $modality, $grade, $position, $start, $end, $department, $section]) {
            Contract::query()->create([
                'employee_id' => $employees[$key]->id,
                'quality' => $quality,
                'modality' => $modality,
                'grade' => $grade,
                'position' => $position,
                'starts_on' => $start,
                'ends_on' => $end,
                'department_id' => $departments[$department]->id,
                'section_id' => $section ? $sections[$section]->id : null,
            ]);
        }
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, PermitType>  $permitTypes
     */
    private function permits(array $employees, array $permitTypes): void
    {
        Permit::query()->create([
            'employee_id' => $employees['diego']->id,
            'permit_type_id' => $permitTypes['administrativo']->id,
            'starts_at' => now()->startOfMonth()->addDays(2)->setTime(9, 0),
            'ends_at' => now()->startOfMonth()->addDays(2)->setTime(13, 0),
        ]);

        Permit::query()->create([
            'employee_id' => $employees['rodrigo']->id,
            'permit_type_id' => $permitTypes['compensatorio']->id,
            'starts_at' => now()->subDays(10)->setTime(8, 30),
            'ends_at' => now()->subDays(10)->setTime(17, 30),
        ]);

        Permit::query()->create([
            'employee_id' => $employees['francisca']->id,
            'permit_type_id' => $permitTypes['fallecimiento']->id,
            'starts_at' => now()->subMonths(1)->setTime(8, 0),
            'ends_at' => now()->subMonths(1)->addDays(3)->setTime(18, 0),
        ]);

        Permit::query()->create([
            'employee_id' => $employees['nicolas']->id,
            'permit_type_id' => $permitTypes['sin_goce']->id,
            'starts_at' => now()->addDays(12)->setTime(8, 0),
            'ends_at' => now()->addDays(14)->setTime(18, 0),
        ]);
    }

    /**
     * @param  array<string, Employee>  $employees
     */
    private function evaluations(array $employees): void
    {
        $rows = [
            ['maria', 2024, 2025, EvaluationProcess::Final, 6.8, 'Cumple los objetivos de gestión del departamento.'],
            ['javiera', 2024, 2025, EvaluationProcess::Final, 6.5, 'Desempeño destacado en informes jurídicos.'],
            ['camila', 2025, 2026, EvaluationProcess::InEvaluation, 6.2, 'Proceso en curso. Metas de planificación urbana en revisión.'],
            ['paula', 2025, 2026, EvaluationProcess::PreQualification, 5.9, 'Precalificación sujeta a informe de proyectos.'],
            ['andrea', 2024, 2025, EvaluationProcess::Appeal, 4.8, 'Apelación presentada por la funcionaria.'],
            ['sebastian', 2026, 2026, EvaluationProcess::Initial, null, 'Apertura del periodo de reemplazo.'],
        ];

        foreach ($rows as [$key, $start, $end, $process, $score, $notes]) {
            Evaluation::query()->create([
                'employee_id' => $employees[$key]->id,
                'period_start_year' => $start,
                'period_end_year' => $end,
                'process' => $process,
                'score' => $score,
                'notes' => $notes,
            ]);
        }
    }

    private function rut(int $body): string
    {
        return $body.'-'.Rut::verifier($body);
    }
}
