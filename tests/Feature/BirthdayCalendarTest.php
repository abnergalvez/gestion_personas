<?php

namespace Tests\Feature;

use App\Enums\EmploymentQuality;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Support\BirthdayCalendar;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirthdayCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_birthday_week_changes_on_friday_at_noon(): void
    {
        $this->assertSame(
            ['2026-11-14', '2026-11-20'],
            $this->bounds('2026-11-20 11:59:00'),
        );
        $this->assertSame(
            ['2026-11-21', '2026-11-27'],
            $this->bounds('2026-11-20 12:00:00'),
        );
        $this->assertSame(
            ['2026-11-21', '2026-11-27'],
            $this->bounds('2026-11-25 15:30:00'),
        );
        $this->assertSame(
            ['2026-11-21', '2026-11-27'],
            $this->bounds('2026-11-27 11:59:00'),
        );
        $this->assertSame(
            ['2026-11-28', '2026-12-04'],
            $this->bounds('2026-11-27 12:00:00'),
        );
        $this->assertSame(
            ['2026-12-26', '2027-01-01'],
            $this->bounds('2026-12-25 12:00:00'),
        );
    }

    public function test_the_dashboard_lists_birthdays_for_the_open_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00', 'America/Santiago'));
        $admin = User::factory()->create();
        $housing = Department::query()->create(['name' => 'Vivienda']);
        $this->employee('11111111-1', 'Ana', 'Pérez', '1990-11-21', $housing->id);
        $this->employee('22222222-2', 'Bruno', 'Soto', '1988-11-20', $housing->id);
        $this->employee('33333333-3', 'Carla', 'Díaz', '1992-11-27', $housing->id);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cumpleaños de la semana')
            ->assertSee('sábado 21-11-2026 al viernes 27-11-2026')
            ->assertSee('viernes 20-11-2026 a las 12:00')
            ->assertSee('viernes 27-11-2026 a las 12:00')
            ->assertSee('Ana Pérez')
            ->assertSee('Ya cumplió')
            ->assertSee('Carla Díaz')
            ->assertSee('Próximo')
            ->assertDontSee('Bruno Soto');
    }

    public function test_birthdays_can_be_searched_by_month_and_week(): void
    {
        $admin = User::factory()->create();
        $this->get(route('birthdays.index'))->assertRedirect(route('login'));
        $this->employee('11111111-1', 'Ana', 'Pérez', '1990-11-21');
        $this->employee('44444444-4', 'Diego', 'León', '1985-11-08');
        $this->employee('55555555-5', 'Elena', 'Rivas', '1980-01-01');

        $this->actingAs($admin)->get(route('birthdays.index', [
            'year' => 2026,
            'month' => 11,
            'week' => '2026-11-21',
        ]))->assertOk()
            ->assertSee('Ana Pérez')
            ->assertDontSee('Diego León')
            ->assertSee('Sábado 21-11 al viernes 27-11', false);

        $this->actingAs($admin)->get(route('birthdays.index', [
            'year' => 2026,
            'month' => 12,
            'week' => '2026-12-26',
        ]))->assertOk()->assertSee('Elena Rivas');
    }

    public function test_a_february_29_birthday_is_kept_on_february_28_outside_a_leap_year(): void
    {
        $this->employee('66666666-6', 'Elisa', 'Leal', '1992-02-29');
        $today = CarbonImmutable::parse('2027-02-28', 'America/Santiago');
        $week = collect(BirthdayCalendar::weeksTouching(2027, 2))->first(
            fn (BirthdayCalendar $week): bool => $week->entries($today)->contains(
                fn ($notice): bool => $notice->employee->first_name === 'Elisa',
            ),
        );

        $this->assertNotNull($week);
        $notice = $week->entries($today)->first();
        $this->assertSame('2027-02-28', $notice->occursOn->toDateString());
        $this->assertSame(35, $notice->turningAge);

        $leap = collect(BirthdayCalendar::weeksTouching(2024, 2))->first(
            fn (BirthdayCalendar $week): bool => $week->entries($today)->contains(
                fn ($notice): bool => $notice->occursOn->toDateString() === '2024-02-29',
            ),
        );
        $this->assertNotNull($leap);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function bounds(string $moment): array
    {
        $week = BirthdayCalendar::current(CarbonImmutable::parse($moment, 'America/Santiago'));

        return [$week->startsOn->toDateString(), $week->endsOn->toDateString()];
    }

    private function employee(string $rut, string $firstName, string $lastName, string $birthDate, ?int $departmentId = null): Employee
    {
        return Employee::query()->create([
            'rut' => $rut,
            'first_name' => $firstName,
            'paternal_last_name' => $lastName,
            'maternal_last_name' => 'Rojas',
            'birth_date' => $birthDate,
            'employment_quality' => EmploymentQuality::Contract,
            'department_id' => $departmentId,
        ]);
    }
}
