<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptySystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_install_opens_with_only_the_super_administrator(): void
    {
        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertSame(0, Employee::query()->count());

        $admin = User::query()->firstOrFail();
        $this->assertTrue($admin->isSuperAdmin());

        $this->seed(ProductionSeeder::class);
        $this->assertSame(1, User::query()->count());

        $pages = [
            route('dashboard'),
            route('employees.index'),
            route('employees.create'),
            route('departments.index'),
            route('departments.create'),
            route('sections.create'),
            route('training-courses.create'),
            route('medical-leaves.create'),
            route('family-dependents.create'),
            route('contracts.create'),
            route('permits.create'),
            route('evaluations.create'),
            route('leave-types.index'),
            route('users.index'),
            route('activity-logs.index'),
            route('profile.edit'),
        ];

        $this->actingAs($admin);

        foreach ($pages as $url) {
            $response = $this->get($url);

            $this->assertSame(
                200,
                $response->status(),
                $url.' — '.($response->exception?->getMessage() ?? 'sin detalle')
            );
        }

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertSee('id="sidebar"', false);
        $dashboard->assertSee('Abrir menú', false);
        $dashboard->assertSee('Aún no hay funcionarios para graficar');
        $this->assertLessThan(
            strpos($dashboard->getContent(), 'id="sidebar"'),
            strpos($dashboard->getContent(), 'backdrop-blur')
        );
    }
}
