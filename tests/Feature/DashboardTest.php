<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Project;
use App\Models\Region;
use App\Models\Subcategory;
use App\Models\TatConfiguration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Region $region;
    protected Branch $branch;
    protected Category $category;
    protected Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->region = Region::create(['name' => 'Maharashtra', 'code' => 'ST-MH']);
        $this->branch = Branch::create(['name' => 'Pune CO', 'code' => 'BR-MH-01', 'region_id' => $this->region->id]);
        $this->category = Category::create(['support_type' => 'application', 'name' => 'LMS', 'sort_order' => 1, 'is_active' => true]);
        $this->subcategory = Subcategory::create(['category_id' => $this->category->id, 'name' => 'Login', 'default_priority' => 'medium', 'is_active' => true]);
        TatConfiguration::updateOrCreate(['status' => 'open'], [
            'applies_to_transition' => 'open->in_progress', 'tat_hours' => 2, 'is_active' => true,
            'warning_threshold_pct' => 80, 'escalation_to_role' => 'tl',
        ]);
    }

    private function makeUser(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role).' '.uniqid(),
            'email' => $role.'_'.uniqid().'@altumcredo.test',
            'password' => Hash::make('password'),
            'role' => $role, 'is_active' => true, 'email_verified_at' => now(),
            'branch_id' => $this->branch->id, 'region_id' => $this->region->id,
        ], $overrides));
    }

    private function makeTicket(User $creator, array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'ticket_number' => 'TST-'.strtoupper(uniqid()),
            'support_type'  => 'application',
            'category_id'   => $this->category->id,
            'subcategory_id'=> $this->subcategory->id,
            'branch_id'     => $this->branch->id,
            'subject'       => 'Test ticket',
            'description'   => 'Body',
            'priority'      => 'medium',
            'status'        => 'open',
            'created_by'    => $creator->id,
            'tat_hours'     => 2,
            'tat_deadline'  => now()->addHours(2),
        ], $overrides));
    }

    public function test_admin_dashboard_shows_projects_summary_and_project_tickets_section(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeUser('management');
        $project = Project::create([
            'number' => Project::generateNumber(), 'name' => 'Phoenix Migration',
            'owner_id' => $owner->id, 'status' => 'active', 'created_by' => $admin->id,
        ]);
        $linked = $this->makeTicket($admin, ['project_id' => $project->id, 'subject' => 'Project-linked work']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Projects')              // summary strip
            ->assertSee('Project Tickets')       // collapsible section
            ->assertSee('Phoenix Migration')     // the project name in the row
            ->assertSee($project->number)
            ->assertSee($linked->ticket_number)
            ->assertSee('Management Tickets')
            ->assertSee('Recent Tickets');
    }

    public function test_ciso_dashboard_shows_project_tickets_section(): void
    {
        $ciso = $this->makeUser('resolver', ['resolver_level' => 'ciso']);

        $this->actingAs($ciso)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Project Tickets');
    }

    public function test_employee_dashboard_has_no_project_sections(): void
    {
        $employee = $this->makeUser('employee');

        $this->actingAs($employee)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent Tickets')
            ->assertDontSee('Project Tickets');
    }

    public function test_project_linked_ticket_does_not_duplicate_in_recent(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeUser('management');
        $project = Project::create([
            'number' => Project::generateNumber(), 'name' => 'Solo Project',
            'owner_id' => $owner->id, 'status' => 'active', 'created_by' => $admin->id,
        ]);
        $linked = $this->makeTicket($admin, ['project_id' => $project->id]);

        $resp = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        // The ticket number should appear exactly once (in the Project Tickets table only).
        $this->assertSame(1, substr_count($resp->getContent(), $linked->ticket_number));
    }
}
