<?php

namespace App\Support;

use App\Enums\EmploymentQuality;
use App\Enums\Sex;
use App\Enums\StaffLevel;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class EmployeeListing
{
    public function __construct(private Request $request) {}

    public static function from(Request $request): self
    {
        return new self($request);
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->query()->paginate(Listing::perPage($this->request))->withQueryString();
    }

    /**
     * @return list<list<null|int|string>>
     */
    public function exportRows(): array
    {
        return $this->query()->get()->map(fn (Employee $employee): array => [
            $employee->formatted_rut,
            $employee->first_name,
            $employee->paternal_last_name,
            $employee->maternal_last_name,
            $employee->position,
            $employee->sex?->label(),
            $employee->birth_date?->format('d-m-Y'),
            $employee->age,
            $employee->profession,
            $employee->staff_level?->label(),
            $employee->marital_status?->label(),
            $employee->grade,
            $employee->employment_quality?->label(),
            $employee->hired_on?->format('d-m-Y'),
            $employee->email,
            $employee->department?->name,
            $employee->section?->name,
        ])->all();
    }

    /**
     * @return Builder<Employee>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request);
        $sort = Listing::sort($this->request, [
            'name' => true,
            'rut' => true,
            'department' => true,
            'section' => true,
            'quality' => true,
        ], 'name');

        $quality = EmploymentQuality::tryFrom($this->request->string('employment_quality')->value());
        $staffLevel = StaffLevel::tryFrom($this->request->string('staff_level')->value());
        $sex = Sex::tryFrom($this->request->string('sex')->value());

        $query = Employee::query()
            ->with(['department', 'section'])
            ->search($this->request->string('search')->trim()->value())
            ->when($this->request->integer('department_id'), fn (Builder $query, int $id) => $query->where('department_id', $id))
            ->when($this->request->integer('section_id'), fn (Builder $query, int $id) => $query->where('section_id', $id))
            ->when($quality, fn (Builder $query) => $query->where('employment_quality', $quality))
            ->when($staffLevel, fn (Builder $query) => $query->where('staff_level', $staffLevel))
            ->when($sex, fn (Builder $query) => $query->where('sex', $sex));

        $ordered = match ($sort) {
            'rut' => $query->orderBy('rut', $direction),
            'department' => $query->orderBy(
                Department::query()->select('name')->whereColumn('departments.id', 'employees.department_id'),
                $direction,
            ),
            'section' => $query->orderBy(
                Section::query()->select('name')->whereColumn('sections.id', 'employees.section_id'),
                $direction,
            ),
            'quality' => $query->orderBy('employment_quality', $direction),
            default => $query->orderBy('paternal_last_name', $direction)->orderBy('first_name', $direction),
        };

        return $ordered->orderBy('id');
    }
}
