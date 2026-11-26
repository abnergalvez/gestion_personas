<?php

namespace App\Support;

use App\Enums\ContractQuality;
use App\Enums\EmploymentQuality;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\FamilyDependent;
use App\Models\MedicalLeave;
use App\Models\Permit;
use App\Models\Section;
use App\Models\TrainingCourse;
use Illuminate\Support\Collection;

final class DashboardStats
{
    /**
     * @return array<string, int|float|null>
     */
    public function cards(): array
    {
        return [
            'employees' => Employee::query()->count(),
            'departments' => Department::query()->count(),
            'sections' => Section::query()->count(),
            'active_leaves' => MedicalLeave::query()
                ->whereDate('starts_on', '<=', today())
                ->whereDate('ends_on', '>=', today())
                ->count(),
            'expiring_contracts' => Contract::query()
                ->whereNotNull('ends_on')
                ->whereDate('ends_on', '>=', today())
                ->whereDate('ends_on', '<=', today()->addDays(Settings::contractDays()))
                ->count(),
            'contract_notice_days' => Settings::contractDays(),
            'permits_this_month' => Permit::query()
                ->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'dependents' => FamilyDependent::query()->count(),
            'courses' => TrainingCourse::query()->count(),
            'average_score' => Evaluation::query()->whereNotNull('score')->avg('score'),
        ];
    }

    /**
     * @return array<string, array{labels: list<string>, values: list<int>}>
     */
    public function charts(): array
    {
        return [
            'quality' => $this->countByEnum(Employee::class, 'employment_quality', EmploymentQuality::cases()),
            'departments' => $this->employeesByDepartment(),
            'leaves' => $this->leavesByMonth(),
            'contracts' => $this->countByEnum(Contract::class, 'quality', ContractQuality::cases()),
        ];
    }

    /**
     * @return Collection<int, Contract>
     */
    public function expiringContracts(): Collection
    {
        return Contract::query()
            ->with('employee')
            ->whereNotNull('ends_on')
            ->whereDate('ends_on', '>=', today())
            ->whereDate('ends_on', '<=', today()->addDays(Settings::contractDays()))
            ->orderBy('ends_on')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, MedicalLeave>
     */
    public function activeLeaves(): Collection
    {
        return MedicalLeave::query()
            ->with(['employee', 'leaveType'])
            ->whereDate('starts_on', '<=', today())
            ->whereDate('ends_on', '>=', today())
            ->orderBy('ends_on')
            ->limit(6)
            ->get();
    }

    /**
     * @param  class-string  $model
     * @param  list<\BackedEnum>  $cases
     * @return array{labels: list<string>, values: list<int>}
     */
    private function countByEnum(string $model, string $column, array $cases): array
    {
        $counts = $model::query()
            ->selectRaw($column.' as bucket, COUNT(*) as total')
            ->groupBy($column)
            ->pluck('total', 'bucket');

        return [
            'labels' => array_map(fn ($case) => $case->label(), $cases),
            'values' => array_map(fn ($case) => (int) ($counts[$case->value] ?? 0), $cases),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function employeesByDepartment(): array
    {
        $departments = Department::query()->withCount('employees')->orderBy('name')->get();
        $unassigned = Employee::query()->whereNull('department_id')->count();

        $labels = $departments->pluck('name')->all();
        $values = $departments->pluck('employees_count')->map(fn ($count) => (int) $count)->all();

        if ($unassigned > 0) {
            $labels[] = 'Sin departamento';
            $values[] = $unassigned;
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function leavesByMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $grouped = MedicalLeave::query()
            ->whereDate('starts_on', '>=', $start)
            ->get()
            ->groupBy(fn (MedicalLeave $leave) => $leave->starts_on->format('Y-m'));

        $labels = [];
        $values = [];

        for ($month = 0; $month < 6; $month++) {
            $date = $start->copy()->addMonths($month);
            $labels[] = ucfirst($date->locale('es')->isoFormat('MMM YYYY'));
            $values[] = isset($grouped[$date->format('Y-m')]) ? $grouped[$date->format('Y-m')]->count() : 0;
        }

        return compact('labels', 'values');
    }
}
