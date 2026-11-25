<?php

namespace App\Support;

use App\Enums\EmploymentQuality;
use App\Enums\Sex;
use App\Enums\StaffLevel;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class ReportFilters
{
    /** @var list<string> */
    public const FIELDS = [
        'employee_id',
        'employee_search',
        'employee_department_id',
        'employee_section_id',
        'employment_quality',
        'staff_level',
        'sex',
    ];

    /** @var list<string> */
    public const IDENTITY = [
        'Funcionario',
        'RUT',
        'Cargo',
        'Departamento',
        'Sección',
        'Calidad',
        'Estamento',
        'Sexo',
        'Correo',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function viewData(): array
    {
        return [
            'employees' => Employee::options(),
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'sections' => Section::query()->orderBy('name')->pluck('name', 'id'),
            'qualities' => EmploymentQuality::options(),
            'staffLevels' => StaffLevel::options(),
            'sexes' => Sex::options(),
        ];
    }

    /**
     * @param  Builder<Model>  $query
     */
    public static function apply(Builder $query, Request $request): void
    {
        $table = $query->getModel()->getTable();
        $query->when(
            $request->integer('employee_id'),
            fn (Builder $query, int $id) => $query->where($table.'.employee_id', $id),
        );

        $search = $request->string('employee_search')->trim()->value();
        $departmentId = $request->integer('employee_department_id');
        $sectionId = $request->integer('employee_section_id');
        $quality = EmploymentQuality::tryFrom($request->string('employment_quality')->value());
        $staffLevel = StaffLevel::tryFrom($request->string('staff_level')->value());
        $sex = Sex::tryFrom($request->string('sex')->value());

        if ($search === '' && ! $departmentId && ! $sectionId && ! $quality && ! $staffLevel && ! $sex) {
            return;
        }

        $query->whereHas('employee', function (Builder $employee) use ($search, $departmentId, $sectionId, $quality, $staffLevel, $sex): void {
            $employee
                ->when($search !== '', fn (Builder $employee) => $employee->search($search))
                ->when($departmentId, fn (Builder $employee) => $employee->where('department_id', $departmentId))
                ->when($sectionId, fn (Builder $employee) => $employee->where('section_id', $sectionId))
                ->when($quality, fn (Builder $employee) => $employee->where('employment_quality', $quality))
                ->when($staffLevel, fn (Builder $employee) => $employee->where('staff_level', $staffLevel))
                ->when($sex, fn (Builder $employee) => $employee->where('sex', $sex));
        });
    }

    /**
     * @return list<null|string>
     */
    public static function identity(?Employee $employee): array
    {
        return [
            $employee?->full_name,
            $employee?->formatted_rut,
            $employee?->position,
            $employee?->department?->name,
            $employee?->section?->name,
            $employee?->employment_quality?->label(),
            $employee?->staff_level?->label(),
            $employee?->sex?->label(),
            $employee?->email,
        ];
    }

    public static function file(?string $path): string
    {
        return filled($path) ? 'Sí' : 'No';
    }
}
