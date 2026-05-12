<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** Guests hitting the root are sent to the login screen. */
    public function test_root_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    /** A signed-in employee lands on the ticketing dashboard. */
    public function test_root_redirects_signed_in_users_to_dashboard(): void
    {
        $user = User::create([
            'name'              => 'Employee',
            'email'             => 'employee_'.uniqid().'@altumcredo.test',
            'password'          => bcrypt('password'),
            'role'              => 'employee',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }
}
