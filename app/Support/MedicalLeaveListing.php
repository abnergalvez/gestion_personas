<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\MedicalLeave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class MedicalLeaveListing
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
        return $this->query()->get()->map(fn (MedicalLeave $leave): array => [
            $leave->employee?->full_name,
            $leave->employee?->formatted_rut,
            $leave->leaveType?->name,
            $leave->starts_on?->format('d-m-Y'),
            $leave->ends_on?->format('d-m-Y'),
            $leave->days_count,
            $leave->employee?->position,
            $leave->employee?->department?->name,
            $leave->employee?->section?->name,
            $leave->employee?->employment_quality?->label(),
            $leave->employee?->staff_level?->label(),
            $leave->employee?->sex?->label(),
            $leave->employee?->email,
            ReportFilters::file($leave->document_path),
            ReportFilters::file($leave->medical_resolution_path),
            ReportFilters::file($leave->internal_resolution_path),
        ])->all();
    }

    /**
     * @return Builder<MedicalLeave>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request, 'desc');
        $sort = Listing::sort($this->request, [
            'employee' => true,
            'type' => true,
            'period' => true,
            'days' => true,
        ], 'period');

        $query = MedicalLeave::query()->with(['employee.department', 'employee.section', 'leaveType']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($this->request->integer('leave_type_id'), fn (Builder $query, int $id) => $query->where('leave_type_id', $id))
            ->when(Listing::date($this->request, 'starts_from'), fn (Builder $query, string $date) => $query->whereDate('starts_on', '>=', $date))
            ->when(Listing::date($this->request, 'starts_until'), fn (Builder $query, string $date) => $query->whereDate('starts_on', '<=', $date))
            ->when(Listing::date($this->request, 'ends_from'), fn (Builder $query, string $date) => $query->whereDate('ends_on', '>=', $date))
            ->when(Listing::date($this->request, 'ends_until'), fn (Builder $query, string $date) => $query->whereDate('ends_on', '<=', $date))
            ->when($this->request->filled('days_min'), fn (Builder $query) => $query->where('days_count', '>=', $this->request->integer('days_min')))
            ->when($this->request->filled('days_max'), fn (Builder $query) => $query->where('days_count', '<=', $this->request->integer('days_max')));

        $ordered = match ($sort) {
            'employee' => $query->orderBy(
                Employee::query()->select('paternal_last_name')->whereColumn('employees.id', 'medical_leaves.employee_id'),
                $direction,
            )->orderBy(
                Employee::query()->select('first_name')->whereColumn('employees.id', 'medical_leaves.employee_id'),
                $direction,
            ),
            'type' => $query->orderBy(
                LeaveType::query()->select('name')->whereColumn('leave_types.id', 'medical_leaves.leave_type_id'),
                $direction,
            ),
            'days' => $query->orderBy('days_count', $direction),
            default => $query->orderBy('starts_on', $direction),
        };

        return $ordered->orderBy('id');
    }
}
