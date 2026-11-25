<?php

namespace App\Support;

use App\Enums\ContractModality;
use App\Enums\ContractQuality;
use App\Models\Contract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ContractListing
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
        return $this->query()->get()->map(fn (Contract $contract): array => [
            ...ReportFilters::identity($contract->employee),
            $contract->position,
            $contract->quality?->label(),
            $contract->modality?->label(),
            $contract->grade,
            $contract->starts_on?->format('d-m-Y'),
            $contract->ends_on?->format('d-m-Y') ?? 'Sin término',
            $contract->department?->name,
            $contract->section?->name,
        ])->all();
    }

    /**
     * @return Builder<Contract>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request, 'desc');
        $sort = Listing::sort($this->request, [
            'employee' => true,
            'position' => true,
            'quality' => true,
            'modality' => true,
            'start' => true,
            'end' => true,
        ], 'start');
        $quality = ContractQuality::tryFrom($this->request->string('quality')->value());
        $modality = ContractModality::tryFrom($this->request->string('modality')->value());
        $position = $this->request->string('position')->trim()->value();
        $grade = $this->request->string('grade')->trim()->value();

        $query = Contract::query()->with(['employee.department', 'employee.section', 'department', 'section']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($quality, fn (Builder $query) => $query->where('quality', $quality))
            ->when($modality, fn (Builder $query) => $query->where('modality', $modality))
            ->when($position !== '', fn (Builder $query) => $query->where('position', 'like', Listing::like($position)))
            ->when($grade !== '', fn (Builder $query) => $query->where('grade', 'like', Listing::like($grade)))
            ->when(Listing::date($this->request, 'starts_from'), fn (Builder $query, string $date) => $query->whereDate('starts_on', '>=', $date))
            ->when(Listing::date($this->request, 'starts_until'), fn (Builder $query, string $date) => $query->whereDate('starts_on', '<=', $date))
            ->when(Listing::date($this->request, 'ends_from'), fn (Builder $query, string $date) => $query->whereDate('ends_on', '>=', $date))
            ->when(Listing::date($this->request, 'ends_until'), fn (Builder $query, string $date) => $query->whereDate('ends_on', '<=', $date))
            ->when($this->request->integer('department_id'), fn (Builder $query, int $id) => $query->where('department_id', $id))
            ->when($this->request->integer('section_id'), fn (Builder $query, int $id) => $query->where('section_id', $id))
            ->when($this->request->string('term')->value() === 'open', fn (Builder $query) => $query->whereNull('ends_on'))
            ->when($this->request->string('term')->value() === 'closed', fn (Builder $query) => $query->whereNotNull('ends_on'));

        $ordered = match ($sort) {
            'employee' => Listing::orderByEmployee($query, $direction),
            'position' => $query->orderBy('position', $direction),
            'quality' => $query->orderBy('quality', $direction),
            'modality' => $query->orderBy('modality', $direction),
            'end' => $query->orderBy('ends_on', $direction),
            default => $query->orderBy('starts_on', $direction),
        };

        return $ordered->orderBy('id');
    }
}
