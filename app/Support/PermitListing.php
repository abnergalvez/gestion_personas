<?php

namespace App\Support;

use App\Models\Permit;
use App\Models\PermitType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PermitListing
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
     * @return list<list<null|string>>
     */
    public function exportRows(): array
    {
        return $this->query()->get()->map(fn (Permit $permit): array => [
            ...ReportFilters::identity($permit->employee),
            $permit->type?->name,
            $permit->starts_at?->format('d-m-Y H:i'),
            $permit->ends_at?->format('d-m-Y H:i'),
            ReportFilters::file($permit->document_path),
        ])->all();
    }

    /**
     * @return Builder<Permit>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request, 'desc');
        $sort = Listing::sort($this->request, [
            'employee' => true,
            'type' => true,
            'start' => true,
            'end' => true,
        ], 'start');

        $query = Permit::query()->with(['employee.department', 'employee.section', 'type']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($this->request->integer('permit_type_id'), fn (Builder $query, int $id) => $query->where('permit_type_id', $id))
            ->when(Listing::date($this->request, 'starts_from'), fn (Builder $query, string $date) => $query->whereDate('starts_at', '>=', $date))
            ->when(Listing::date($this->request, 'starts_until'), fn (Builder $query, string $date) => $query->whereDate('starts_at', '<=', $date))
            ->when(Listing::date($this->request, 'ends_from'), fn (Builder $query, string $date) => $query->whereDate('ends_at', '>=', $date))
            ->when(Listing::date($this->request, 'ends_until'), fn (Builder $query, string $date) => $query->whereDate('ends_at', '<=', $date));

        $ordered = match ($sort) {
            'employee' => Listing::orderByEmployee($query, $direction),
            'type' => $query->orderBy(
                PermitType::query()->select('name')->whereColumn('permit_types.id', 'permits.permit_type_id'),
                $direction,
            ),
            'end' => $query->orderBy('ends_at', $direction),
            default => $query->orderBy('starts_at', $direction),
        };

        return $ordered->orderBy('id');
    }
}
