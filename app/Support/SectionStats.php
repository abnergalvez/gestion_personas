<?php

namespace App\Support;

use App\Enums\EmploymentQuality;
use App\Enums\EvaluationProcess;
use App\Enums\FamilyRelationship;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\FamilyDependent;
use App\Models\MedicalLeave;
use App\Models\Permit;
use App\Models\TrainingCourse;
use Illuminate\Database\Eloquent\Builder;

final class SectionStats
{
    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function employees(): array
    {
        return [
            self::item('Dotación', Employee::query()->count()),
            self::item('Contrata', Employee::query()->where('employment_quality', EmploymentQuality::Contract)->count()),
            self::item('Planta', Employee::query()->where('employment_quality', EmploymentQuality::Permanent)->count()),
            self::item('Sin departamento', Employee::query()->whereNull('department_id')->count()),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function birthdays(): array
    {
        $week = BirthdayCalendar::current();
        $today = now();

        return [
            self::item('Esta semana', $week->entries()->count()),
            self::item('Hoy', Employee::query()->whereMonth('birth_date', $today->month)->whereDay('birth_date', $today->day)->count()),
            self::item('Este mes', Employee::query()->whereMonth('birth_date', $today->month)->count()),
            self::item('Próxima semana', $week->following()->entries()->count()),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function courses(): array
    {
        $until = today()->addDays(Settings::courseDays());

        return [
            self::item('Registrados', TrainingCourse::query()->count()),
            self::item('Vigentes', TrainingCourse::query()->where(fn (Builder $query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))->count()),
            self::item('Por vencer', TrainingCourse::query()->whereNotNull('valid_until')->whereDate('valid_until', '>=', today())->whereDate('valid_until', '<=', $until)->count()),
            self::item('Vencidos', TrainingCourse::query()->whereNotNull('valid_until')->whereDate('valid_until', '<', today())->count()),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function medicalLeaves(): array
    {
        $month = MedicalLeave::query()
            ->whereDate('starts_on', '<=', today()->endOfMonth())
            ->whereDate('ends_on', '>=', today()->startOfMonth());
        $average = MedicalLeave::query()->avg('days_count');

        return [
            self::item('Vigentes', MedicalLeave::query()->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())->count()),
            self::item('Del mes', (clone $month)->count()),
            self::item('Días del mes', (int) (clone $month)->sum('days_count')),
            self::item('Promedio de días', $average === null ? '—' : number_format((float) $average, 1, ',', '.')),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function dependents(): array
    {
        return [
            self::item('Cargas', FamilyDependent::query()->count()),
            self::item('Hijos', FamilyDependent::query()->where('relationship', FamilyRelationship::Child)->count()),
            self::item('Cumplen 18', FamilyDependent::query()->where('relationship', FamilyRelationship::Child)->whereYear('birth_date', now()->year - 18)->count()),
            self::item('Sin certificado', FamilyDependent::query()->whereNull('birth_certificate_path')->count()),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function contracts(): array
    {
        $until = today()->addDays(Settings::contractDays());
        $current = Contract::query()
            ->whereDate('starts_on', '<=', today())
            ->where(fn (Builder $query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()));

        return [
            self::item('Vigentes', (clone $current)->count()),
            self::item('Por vencer', Contract::query()->whereNotNull('ends_on')->whereDate('ends_on', '>=', today())->whereDate('ends_on', '<=', $until)->count()),
            self::item('Vencidos', Contract::query()->whereNotNull('ends_on')->whereDate('ends_on', '<', today())->count()),
            self::item('Sin término', (clone $current)->whereNull('ends_on')->count()),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function permits(): array
    {
        $month = [now()->startOfMonth(), now()->endOfMonth()];
        $minutes = Permit::query()
            ->whereBetween('starts_at', $month)
            ->get(['starts_at', 'ends_at'])
            ->sum(fn (Permit $permit): float => abs($permit->starts_at->diffInMinutes($permit->ends_at)));

        return [
            self::item('Del mes', Permit::query()->whereBetween('starts_at', $month)->count()),
            self::item('En curso', Permit::query()->where('starts_at', '<=', now())->where('ends_at', '>=', now())->count()),
            self::item('Próximos', Permit::query()->where('starts_at', '>', now())->where('starts_at', '<=', now()->addDays(30))->count()),
            self::item('Horas del mes', number_format($minutes / 60, 1, ',', '.')),
        ];
    }

    /**
     * @return list<array{label: string, value: int|string}>
     */
    public static function evaluations(): array
    {
        $year = (int) now()->year;
        $period = Evaluation::query()
            ->where('period_start_year', '<=', $year)
            ->where('period_end_year', '>=', $year);
        $average = (clone $period)->whereNotNull('score')->avg('score');
        $threshold = Settings::lowScore();

        return [
            self::item('Del periodo', (clone $period)->count()),
            self::item('Promedio', $average === null ? '—' : number_format((float) $average, 1, ',', '.')),
            self::item('En apelación', (clone $period)->where('process', EvaluationProcess::Appeal)->count()),
            self::item('Bajo '.number_format($threshold, 1, ',', '.'), (clone $period)->whereNotNull('score')->where('score', '<', $threshold)->count()),
        ];
    }

    private static function item(string $label, int|string $value): array
    {
        return ['label' => $label, 'value' => $value];
    }
}
