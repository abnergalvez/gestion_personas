<?php

namespace App\Support;

use App\Enums\EducationLevel;
use App\Enums\FamilyRelationship;
use App\Models\FamilyDependent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class FamilyDependentListing
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
        return $this->query()->get()->map(fn (FamilyDependent $dependent): array => [
            ...ReportFilters::identity($dependent->employee),
            $dependent->full_name,
            $dependent->formatted_rut,
            $dependent->birth_date?->format('d-m-Y'),
            $dependent->relationship?->label(),
            $dependent->education_level?->label(),
            ReportFilters::file($dependent->birth_certificate_path),
            ReportFilters::file($dependent->resolution_path),
        ])->all();
    }

    /**
     * @return Builder<FamilyDependent>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request);
        $sort = Listing::sort($this->request, [
            'name' => true,
            'employee' => true,
            'relationship' => true,
            'education' => true,
        ], 'name');
        $search = $this->request->string('search')->trim()->value();
        $relationship = FamilyRelationship::tryFrom($this->request->string('relationship')->value());
        $education = EducationLevel::tryFrom($this->request->string('education_level')->value());

        $query = FamilyDependent::query()->with(['employee.department', 'employee.section']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $rut = str_replace('.', '', Rut::normalize($search));
                $query->where(function (Builder $query) use ($search, $rut): void {
                    $query->where('full_name', 'like', Listing::like($search));
                    if ($rut !== '') {
                        $query->orWhere('rut', 'like', Listing::like($rut));
                    }
                });
            })
            ->when($relationship, fn (Builder $query) => $query->where('relationship', $relationship))
            ->when($education, fn (Builder $query) => $query->where('education_level', $education))
            ->when(Listing::date($this->request, 'birth_from'), fn (Builder $query, string $date) => $query->whereDate('birth_date', '>=', $date))
            ->when(Listing::date($this->request, 'birth_until'), fn (Builder $query, string $date) => $query->whereDate('birth_date', '<=', $date));

        $ordered = match ($sort) {
            'employee' => Listing::orderByEmployee($query, $direction),
            'relationship' => $query->orderBy('relationship', $direction),
            'education' => $query->orderBy('education_level', $direction),
            default => $query->orderBy('full_name', $direction),
        };

        return $ordered->orderBy('id');
    }
}
