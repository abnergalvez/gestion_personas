<?php

namespace App\Support;

use App\Enums\EvaluationProcess;
use App\Models\Evaluation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class EvaluationListing
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
        return $this->query()->get()->map(fn (Evaluation $evaluation): array => [
            ...ReportFilters::identity($evaluation->employee),
            $evaluation->period_start_year,
            $evaluation->period_end_year,
            $evaluation->process?->label(),
            $evaluation->score !== null ? number_format((float) $evaluation->score, 1, ',', '.') : null,
            $evaluation->notes,
            ReportFilters::file($evaluation->document_path),
        ])->all();
    }

    /**
     * @return Builder<Evaluation>
     */
    public function query(): Builder
    {
        $direction = Listing::direction($this->request, 'desc');
        $sort = Listing::sort($this->request, [
            'employee' => true,
            'period' => true,
            'process' => true,
            'score' => true,
        ], 'period');
        $process = EvaluationProcess::tryFrom($this->request->string('process')->value());
        $notes = $this->request->string('notes')->trim()->value();

        $query = Evaluation::query()->with(['employee.department', 'employee.section']);
        ReportFilters::apply($query, $this->request);
        $query
            ->when($process, fn (Builder $query) => $query->where('process', $process))
            ->when($this->year('period_start_year'), fn (Builder $query, int $year) => $query->where('period_start_year', $year))
            ->when($this->year('period_end_year'), fn (Builder $query, int $year) => $query->where('period_end_year', $year))
            ->when($this->score('score_min'), fn (Builder $query, float $score) => $query->where('score', '>=', $score))
            ->when($this->score('score_max'), fn (Builder $query, float $score) => $query->where('score', '<=', $score))
            ->when($notes !== '', fn (Builder $query) => $query->where('notes', 'like', Listing::like($notes)));

        $ordered = match ($sort) {
            'employee' => Listing::orderByEmployee($query, $direction),
            'process' => $query->orderBy('process', $direction),
            'score' => $query->orderBy('score', $direction),
            default => $query->orderBy('period_end_year', $direction)->orderBy('period_start_year', $direction),
        };

        return $ordered->orderBy('id');
    }

    private function year(string $key): ?int
    {
        if (! $this->request->filled($key)) {
            return null;
        }

        $year = $this->request->integer($key);

        return $year >= 1980 && $year <= 2100 ? $year : null;
    }

    private function score(string $key): ?float
    {
        $value = str_replace(',', '.', $this->request->string($key)->trim()->value());

        return is_numeric($value) ? (float) $value : null;
    }
}
