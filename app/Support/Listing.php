<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class Listing
{
    /** @var list<int> */
    public const PER_PAGE = [12, 25, 50, 100];

    public static function perPage(Request $request): int
    {
        $value = $request->integer('per_page');

        return in_array($value, self::PER_PAGE, true) ? $value : self::PER_PAGE[0];
    }

    public static function direction(Request $request, string $default = 'asc'): string
    {
        $direction = $request->string('direction')->lower()->value();

        if (in_array($direction, ['asc', 'desc'], true)) {
            return $direction;
        }

        return $default === 'desc' ? 'desc' : 'asc';
    }

    public static function sort(Request $request, array $allowed, string $default): string
    {
        $sort = $request->string('sort')->value();

        return isset($allowed[$sort]) ? $sort : $default;
    }

    public static function date(Request $request, string $key): ?string
    {
        $value = $request->string($key)->trim()->value();

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
    }

    public static function like(string $value): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($value)).'%';
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public static function orderByEmployee(Builder $query, string $direction): Builder
    {
        $table = $query->getModel()->getTable();

        return $query->orderBy(
            Employee::query()->select('paternal_last_name')->whereColumn('employees.id', $table.'.employee_id'),
            $direction,
        )->orderBy(
            Employee::query()->select('first_name')->whereColumn('employees.id', $table.'.employee_id'),
            $direction,
        );
    }
}
