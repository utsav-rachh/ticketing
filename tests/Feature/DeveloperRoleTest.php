<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests for the developer-role workspace: the app launcher
 * (CTS / ATS / Dialer), the ATS scaffold, the Dialer module, and the fact
 * that developers can now also use the main ticketing app ("CTS").
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

    public function test_developer_sees_the_app_launcher(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)
            ->get(route('developer.home'))
            ->assertOk()
            ->assertSee('CTS')
            ->assertSee('ATS')
            ->assertSee('Dialer');
    }

    public function test_developer_can_open_ats_and_dialer(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)->get(route('developer.assets'))->assertOk()->assertSee('Asset Management');
        $this->actingAs($dev)->get(route('developer.dialer.home'))->assertOk()->assertSee('Dialer');
        $this->actingAs($dev)->get(route('developer.dialer.customers.index'))->assertOk()->assertSee('Customers');
        $this->actingAs($dev)->get(route('developer.dialer.tickets.index'))->assertOk()->assertSee('Call log');
    }

    public function test_admin_cannot_open_developer_area(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('developer.home'))->assertForbidden();
        $this->actingAs($admin)->get(route('developer.dialer.home'))->assertForbidden();
    }

    public function test_ciso_cannot_open_developer_area(): void
    {
        $ciso = $this->makeUser('resolver', ['resolver_level' => 'ciso']);

        $this->actingAs($ciso)->get(route('developer.home'))->assertForbidden();
    }

    public function test_developer_can_now_reach_the_ticketing_app(): void
    {
        $dev = $this->makeUser('developer');

        // "CTS" on the launcher — developers get the main app at employee level.
        $this->actingAs($dev)->get('/dashboard')->assertOk();
        $this->actingAs($dev)->get('/tickets')->assertOk();
    }

    public function test_developer_landing_redirects_to_launcher(): void
    {
        $dev = $this->makeUser('developer');

        $this->actingAs($dev)->get('/')->assertRedirect(route('developer.home'));
    }
}
