<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::query()->first());
    }

    public function test_main_screens_render(): void
    {
        $employee = Employee::query()->firstOrFail();
        $contract = Contract::query()->firstOrFail();
        $department = Department::query()->firstOrFail();
        $section = Section::query()->firstOrFail();
        $account = User::query()->firstOrFail();
        $activityLog = ActivityLog::query()->create([
            'user_id' => $account->id,
            'user_name' => $account->name,
            'user_email' => $account->email,
            'action' => ActivityAction::Viewed,
            'description' => 'Consultó el inicio.',
            'method' => 'GET',
            'url' => url('/'),
            'ip_address' => '127.0.0.1',
        ]);

        $routes = [
            route('dashboard'),
            route('employees.index'),
            route('employees.create'),
            route('employees.show', $employee),
            route('employees.edit', $employee),
            route('departments.index'),
            route('departments.edit', $department),
            route('sections.edit', $section),
            route('contracts.create', ['employee_id' => $employee->id]),
            route('sections.index'),
            route('sections.create'),
            route('training-courses.index'),
            route('training-courses.create'),
            route('medical-leaves.index'),
            route('medical-leaves.create'),
            route('family-dependents.index'),
            route('family-dependents.create'),
            route('contracts.index'),
            route('contracts.create'),
            route('contracts.show', $contract),
            route('permits.index'),
            route('permits.create'),
            route('evaluations.index'),
            route('evaluations.create'),
            route('leave-types.index'),
            route('permit-types.index'),
            route('contract-document-types.index'),
            route('users.index'),
            route('users.create'),
            route('users.edit', $account),
            route('activity-logs.index'),
            route('activity-logs.show', $activityLog),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);

            $this->assertSame(
                200,
                $response->status(),
                $url.' — '.($response->exception?->getMessage() ?? 'sin detalle')
            );
        }
    }
}
