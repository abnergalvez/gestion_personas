<?php

namespace App\Support;

use App\Models\Employee;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class BirthdayCalendar
{
    /**
     * @var array<int, string>
     */
    public const MONTHS = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function __construct(
        public readonly CarbonImmutable $startsOn,
        public readonly CarbonImmutable $endsOn,
    ) {}

    public static function current(?CarbonInterface $moment = null): self
    {
        $now = self::moment($moment);
        $friday = $now->startOfDay();

        if ($now->dayOfWeek !== CarbonImmutable::FRIDAY) {
            $friday = $friday->next(CarbonImmutable::FRIDAY);
        } elseif ($now->hour >= 12) {
            $friday = $friday->addWeek();
        }

        return new self($friday->subDays(6), $friday);
    }

    /**
     * @return list<self>
     */
    public static function weeksTouching(int $year, int $month): array
    {
        $first = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'America/Santiago');
        $last = $first->endOfMonth()->startOfDay();
        $saturday = $first->subDays(($first->dayOfWeek + 1) % 7);
        $weeks = [];

        while ($saturday->lessThanOrEqualTo($last)) {
            $weeks[] = new self($saturday, $saturday->addDays(6));
            $saturday = $saturday->addWeek();
        }

        return $weeks;
    }

    /**
     * @return array{
     *     year: int,
     *     month: int,
     *     months: array<int, string>,
     *     years: array<int, string>,
     *     weekOptions: array<string, string>,
     *     selectedWeek: ?string,
     *     groups: list<array{week: self, entries: Collection<int, BirthdayNotice>}>
     * }
     */
    public static function search(Request $request): array
    {
        $today = self::moment();
        $year = self::bounded($request, 'year', (int) $today->year, 1980, 2100);
        $month = self::bounded($request, 'month', (int) $today->month, 1, 12);
        $weeks = self::weeksTouching($year, $month);
        $requested = $request->string('week')->toString();
        $selected = collect($weeks)->first(
            fn (self $week): bool => $week->startsOn->toDateString() === $requested,
        );
        $shown = $selected instanceof self ? [$selected] : $weeks;
        $years = [];

        foreach (range((int) $today->year - 1, (int) $today->year + 1) as $value) {
            $years[$value] = (string) $value;
        }

        if (! isset($years[$year])) {
            $years[$year] = (string) $year;
            ksort($years);
        }

        return [
            'year' => $year,
            'month' => $month,
            'months' => self::MONTHS,
            'years' => $years,
            'weekOptions' => collect($weeks)->mapWithKeys(
                fn (self $week): array => [$week->startsOn->toDateString() => $week->optionLabel()],
            )->all(),
            'selectedWeek' => $selected?->startsOn->toDateString(),
            'groups' => array_map(fn (self $week): array => [
                'week' => $week,
                'entries' => $week->entries($today),
            ], $shown),
        ];
    }

    /**
     * @return Collection<int, BirthdayNotice>
     */
    public function entries(?CarbonInterface $moment = null): Collection
    {
        $today = self::moment($moment)->startOfDay();
        $pairs = $this->monthDays();

        return Employee::query()
            ->with('department')
            ->whereNotNull('birth_date')
            ->where(function (Builder $query) use ($pairs): void {
                foreach ($pairs as [$month, $day]) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->whereMonth('birth_date', $month)
                        ->whereDay('birth_date', $day));
                }
            })
            ->get()
            ->map(function (Employee $employee) use ($today): ?BirthdayNotice {
                $occursOn = $this->occursOn($employee->birth_date);

                if ($occursOn === null) {
                    return null;
                }

                return new BirthdayNotice(
                    $employee,
                    $occursOn,
                    $occursOn->year - $employee->birth_date->year,
                    BirthdayNotice::timingFor($occursOn, $today),
                );
            })
            ->filter()
            ->sortBy(fn (BirthdayNotice $notice): string => $notice->occursOn->format('m-d').' '.$notice->employee->full_name)
            ->values();
    }

    public function rangeLabel(): string
    {
        return sprintf(
            'sábado %s al viernes %s',
            $this->startsOn->format('d-m-Y'),
            $this->endsOn->format('d-m-Y'),
        );
    }

    public function optionLabel(): string
    {
        $start = $this->startsOn->format($this->startsOn->year === $this->endsOn->year ? 'd-m' : 'd-m-Y');
        $end = $this->endsOn->format($this->startsOn->year === $this->endsOn->year ? 'd-m' : 'd-m-Y');

        return 'Sábado '.$start.' al viernes '.$end;
    }

    public function opensOn(): CarbonImmutable
    {
        return $this->startsOn->subDay()->setTime(12, 0);
    }

    public function closesOn(): CarbonImmutable
    {
        return $this->endsOn->setTime(12, 0);
    }

    public static function dayName(CarbonInterface $date): string
    {
        return ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'][$date->dayOfWeek];
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    private function monthDays(): array
    {
        $days = [];

        for ($date = $this->startsOn; $date->lessThanOrEqualTo($this->endsOn); $date = $date->addDay()) {
            $days[] = [$date->month, $date->day];

            if ($date->month === 2 && $date->day === 28 && ! $date->isLeapYear()) {
                $days[] = [2, 29];
            }
        }

        return $days;
    }

    private function occursOn(CarbonInterface $birthDate): ?CarbonImmutable
    {
        for ($date = $this->startsOn; $date->lessThanOrEqualTo($this->endsOn); $date = $date->addDay()) {
            if ($date->month === $birthDate->month && $date->day === $birthDate->day) {
                return $date;
            }

            if ($birthDate->month === 2 && $birthDate->day === 29 && $date->month === 2 && $date->day === 28 && ! $date->isLeapYear()) {
                return $date;
            }
        }

        return null;
    }

    private static function moment(?CarbonInterface $moment = null): CarbonImmutable
    {
        return CarbonImmutable::parse($moment ?? now())->timezone('America/Santiago');
    }

    private static function bounded(Request $request, string $field, int $fallback, int $min, int $max): int
    {
        if (! $request->filled($field)) {
            return $fallback;
        }

        $value = $request->integer($field);

        return $value >= $min && $value <= $max ? $value : $fallback;
    }
}
