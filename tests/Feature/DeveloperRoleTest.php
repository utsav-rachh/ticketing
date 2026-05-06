<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for the developer-role sandbox.
 */
class DeveloperRoleTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name'              => ucfirst($role),
            'email'             => $role . '_' . uniqid() . '@altumcredo.test',
            'password'          => Hash::make('password'),
            'role'              => $role,
            'is_active'         => true,
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function test_developer_can_open_sandbox_home(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)
            ->get(route('developer.home'))
            ->assertOk()
            ->assertSee('Developer Sandbox');
    }

    public function test_developer_can_open_assets_and_dialer_prototypes(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)->get(route('developer.assets'))->assertOk()->assertSee('Asset Management');
        $this->actingAs($dev)->get(route('developer.dialer'))->assertOk()->assertSee('Dialer');
    }

    public function test_admin_cannot_open_developer_sandbox(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('developer.home'))->assertForbidden();
    }

    public function test_it_head_cannot_open_developer_sandbox(): void
    {
        $itHead = $this->makeUser('resolver', ['resolver_level' => 'it_head']);

        $this->actingAs($itHead)->get(route('developer.home'))->assertForbidden();
    }

    public function test_developer_is_redirected_away_from_main_dashboard(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)
            ->get('/dashboard')
            ->assertRedirect(route('developer.home'));
    }

    public function test_developer_is_redirected_away_from_tickets(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)
            ->get('/tickets')
            ->assertRedirect(route('developer.home'));
    }
}
