<?php

namespace App\Support;

use App\Models\TrainingCourse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class TrainingCourseListing
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
        return $this->query()->get()->map(fn (TrainingCourse $course): array => [
            ...ReportFilters::identity($course->employee),
            $course->name,
            $course->duration,
            $course->certified_on?->format('d-m-Y'),
            $course->valid_until?->format('d-m-Y'),
            ReportFilters::file($course->document_path),
        ])->all();
    }

    /**
     * @return Builder<TrainingCourse>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request, 'desc');
        $sort = Listing::sort($this->request, [
            'name' => true,
            'employee' => true,
            'certified' => true,
            'valid' => true,
        ], 'certified');
        $search = $this->request->string('search')->trim()->value();
        $duration = $this->request->string('duration')->trim()->value();

        $query = TrainingCourse::query()->with(['employee.department', 'employee.section']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', Listing::like($search)))
            ->when($duration !== '', fn (Builder $query) => $query->where('duration', 'like', Listing::like($duration)))
            ->when(Listing::date($this->request, 'certified_from'), fn (Builder $query, string $date) => $query->whereDate('certified_on', '>=', $date))
            ->when(Listing::date($this->request, 'certified_until'), fn (Builder $query, string $date) => $query->whereDate('certified_on', '<=', $date))
            ->when(Listing::date($this->request, 'valid_from'), fn (Builder $query, string $date) => $query->whereDate('valid_until', '>=', $date))
            ->when(Listing::date($this->request, 'valid_until'), fn (Builder $query, string $date) => $query->whereDate('valid_until', '<=', $date));

        $ordered = match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'employee' => Listing::orderByEmployee($query, $direction),
            'valid' => $query->orderBy('valid_until', $direction),
            default => $query->orderBy('certified_on', $direction),
        };

        return $ordered->orderBy('id');
    }
}
