<?php

namespace App\Support;

use App\Models\Employee;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class BirthdayNotice
{
    public function __construct(
        public readonly Employee $employee,
        public readonly CarbonImmutable $occursOn,
        public readonly int $turningAge,
        public readonly string $timing,
    ) {}

    public function timingLabel(): string
    {
        return match ($this->timing) {
            'today' => 'Hoy',
            'past' => 'Ya cumplió',
            default => 'Próximo',
        };
    }

    public function tone(): string
    {
        return match ($this->timing) {
            'today' => 'green',
            'past' => 'slate',
            default => 'blue',
        };
    }

    public function whenLabel(): string
    {
        return BirthdayCalendar::dayName($this->occursOn).' '.$this->occursOn->format('d-m');
    }

    public function ageLabel(): string
    {
        $verb = $this->timing === 'past' ? 'cumplió' : 'cumple';

        return $verb.' '.$this->turningAge.' '.($this->turningAge === 1 ? 'año' : 'años');
    }

    public static function timingFor(CarbonInterface $occursOn, CarbonInterface $today): string
    {
        $day = CarbonImmutable::parse($occursOn)->startOfDay();
        $reference = CarbonImmutable::parse($today)->startOfDay();

        if ($day->equalTo($reference)) {
            return 'today';
        }

        return $day->lessThan($reference) ? 'past' : 'upcoming';
    }
}
