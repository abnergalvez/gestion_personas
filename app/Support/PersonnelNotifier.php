<?php

namespace App\Support;

use App\Models\Contract;
use App\Models\Evaluation;
use App\Models\MedicalLeave;
use App\Models\Permit;
use App\Models\TrainingCourse;
use Throwable;

final class PersonnelNotifier
{
    public static function medicalLeave(MedicalLeave $leave): void
    {
        if (! Settings::enabled('notify.medical_leaves')) {
            return;
        }

        $leave->loadMissing(['employee', 'leaveType']);

        self::send(
            'Nueva licencia médica',
            $leave->employee->full_name.' registró '.$leave->leaveType->name.' del '.$leave->starts_on->format('d-m-Y').' al '.$leave->ends_on->format('d-m-Y').' ('.$leave->days_count.' días).',
        );
    }

    public static function permit(Permit $permit): void
    {
        if (! Settings::enabled('notify.permits')) {
            return;
        }

        $permit->loadMissing(['employee', 'type']);

        self::send(
            'Nuevo permiso',
            $permit->employee->full_name.' tiene '.$permit->type->name.' desde '.$permit->starts_at->format('d-m-Y H:i').' hasta '.$permit->ends_at->format('d-m-Y H:i').'.',
        );
    }

    public static function evaluation(Evaluation $evaluation): void
    {
        if (! Settings::enabled('notify.evaluations')) {
            return;
        }

        $evaluation->loadMissing('employee');
        $score = $evaluation->score === null ? 'sin nota' : 'nota '.number_format((float) $evaluation->score, 1, ',', '.');

        self::send(
            'Calificación registrada',
            $evaluation->employee->full_name.' quedó en '.$evaluation->process->label().' del periodo '.$evaluation->period_start_year.'–'.$evaluation->period_end_year.' ('.$score.').',
        );
    }

    public static function digest(bool $force = false): string
    {
        if (! $force && Settings::get('notify.last_sent_on') === today()->toDateString()) {
            return 'already';
        }

        $sections = array_filter([
            self::contractsSection(),
            self::birthdaysSection(),
            self::coursesSection(),
        ]);

        if ($sections === [] || ! self::send('Avisos de personas', implode("\n\n", $sections))) {
            return 'empty';
        }

        Settings::store(['notify.last_sent_on' => today()->toDateString()]);

        return 'sent';
    }

    private static function contractsSection(): ?string
    {
        if (! Settings::enabled('notify.contracts')) {
            return null;
        }

        $contracts = Contract::query()
            ->with('employee')
            ->whereNotNull('ends_on')
            ->whereDate('ends_on', '>=', today())
            ->whereDate('ends_on', '<=', today()->addDays(Settings::contractDays()))
            ->orderBy('ends_on')
            ->get();

        if ($contracts->isEmpty()) {
            return null;
        }

        $lines = $contracts->map(fn (Contract $contract): string => '- '.$contract->employee->full_name.' · '.$contract->position.' · término '.$contract->ends_on->format('d-m-Y'));

        return 'Contratos por vencer en '.Settings::contractDays()." días:\n".$lines->implode("\n");
    }

    private static function birthdaysSection(): ?string
    {
        if (! Settings::enabled('notify.birthdays')) {
            return null;
        }

        $week = BirthdayCalendar::current();
        $birthdays = $week->entries();

        if ($birthdays->isEmpty()) {
            return null;
        }

        $lines = $birthdays->map(fn (BirthdayNotice $birthday): string => '- '.$birthday->employee->full_name.' · '.$birthday->whenLabel().' · '.$birthday->ageLabel());

        return 'Cumpleaños de la semana ('.$week->rangeLabel()."):\n".$lines->implode("\n");
    }

    private static function coursesSection(): ?string
    {
        if (! Settings::enabled('notify.courses')) {
            return null;
        }

        $courses = TrainingCourse::query()
            ->with('employee')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->addDays(Settings::courseDays()))
            ->orderBy('valid_until')
            ->get();

        if ($courses->isEmpty()) {
            return null;
        }

        $lines = $courses->map(fn (TrainingCourse $course): string => '- '.$course->employee->full_name.' · '.$course->name.' · vence '.$course->valid_until->format('d-m-Y'));

        return 'Cursos por vencer en '.Settings::courseDays()." días:\n".$lines->implode("\n");
    }

    private static function send(string $subject, string $message): bool
    {
        $recipients = Settings::recipients();

        if ($recipients === []) {
            return false;
        }

        try {
            Settings::applyMail();
            MailNotice::send($recipients, $subject, $message);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
