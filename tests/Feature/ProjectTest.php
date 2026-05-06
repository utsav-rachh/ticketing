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

/**
 * Feature / E2E tests for the Projects module + project-linked ticket flow.
 *
 * The project does not have Dusk / Cypress installed; these tests drive the
 * full HTTP pipeline (routing → middleware → policy → controller → DB) which
 * is the project's "e2e" surface.
 */
class ProjectTest extends TestCase
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
        $this->branch = Branch::create([
            'name' => 'Pune Corporate Office',
            'code' => 'BR-MH-01',
            'region_id' => $this->region->id,
        ]);
        $this->category = Category::create([
            'support_type' => 'application',
            'name'         => 'LMS',
            'sort_order'   => 1,
            'is_active'    => true,
        ]);
        $this->subcategory = Subcategory::create([
            'category_id'      => $this->category->id,
            'name'             => 'Payment receipt Failed',
            'default_priority' => 'medium',
            'is_active'        => true,
        ]);
        // The TAT migration already seeded an 'open' row — use updateOrCreate
        // so the test setUp is idempotent.
        TatConfiguration::updateOrCreate(
            ['status' => 'open'],
            [
                'applies_to_transition' => 'open->in_progress',
                'tat_hours'             => 2,
                'is_active'             => true,
                'warning_threshold_pct' => 80,
                'escalation_to_role'    => 'tl',
            ]
        );
    }

    private function makeUser(string $role, array $overrides = []): User
    {
        return User::create(array_merge([
            'name'              => ucfirst($role) . ' ' . uniqid(),
            'email'             => $role . '_' . uniqid() . '@altumcredo.test',
            'password'          => Hash::make('password'),
            'role'              => $role,
            'is_active'         => true,
            'email_verified_at' => now(),
            'branch_id'         => $this->branch->id,
            'region_id'         => $this->region->id,
        ], $overrides));
    }

    private function makeITHead(): User
    {
        return $this->makeUser('resolver', [
            'resolver_level' => 'it_head',
            'name'           => 'IT Head',
            'email'          => 'ithead_' . uniqid() . '@altumcredo.test',
        ]);
    }

    private function makeManagementOwner(): User
    {
        return $this->makeUser('management', [
            'name'  => 'Management Owner',
            'email' => 'mgmt_' . uniqid() . '@altumcredo.test',
        ]);
    }

    public function test_it_head_can_view_projects_index(): void
    {
        $itHead = $this->makeITHead();

        $this->actingAs($itHead)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Projects');
    }

    public function test_admin_can_view_projects_index(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('projects.index'))
            ->assertOk();
    }

    public function test_junior_resolver_cannot_view_projects(): void
    {
        $junior = $this->makeUser('resolver', ['resolver_level' => 'junior']);

        $this->actingAs($junior)
            ->get(route('projects.index'))
            ->assertForbidden();
    }

    public function test_employee_is_blocked_from_projects_route(): void
    {
        $employee = $this->makeUser('employee');

        // role middleware (resolver|admin) blocks before policy runs
        $this->actingAs($employee)
            ->get(route('projects.index'))
            ->assertForbidden();
    }

    public function test_management_user_cannot_view_projects(): void
    {
        $mgmt = $this->makeManagementOwner();

        $this->actingAs($mgmt)
            ->get(route('projects.index'))
            ->assertForbidden();
    }

    public function test_it_head_can_create_a_project(): void
    {
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        $payload = [
            'name'        => 'LMS 2.0 Migration',
            'description' => 'Phase-wise LMS migration',
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'start_date'  => now()->toDateString(),
            'end_date'    => now()->addMonths(2)->toDateString(),
        ];

        $response = $this->actingAs($itHead)
            ->post(route('projects.store'), $payload);

        $project = Project::firstWhere('name', 'LMS 2.0 Migration');
        $this->assertNotNull($project);
        $this->assertSame('ACHFPL-PRJ-0001', $project->number);
        $this->assertSame($owner->id, $project->owner_id);
        $this->assertSame($itHead->id, $project->created_by);

        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_project_number_is_sequential(): void
    {
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        foreach (['Alpha', 'Beta', 'Gamma'] as $name) {
            $this->actingAs($itHead)->post(route('projects.store'), [
                'name'     => $name,
                'owner_id' => $owner->id,
                'status'   => 'active',
            ]);
        }

        $numbers = Project::orderBy('id')->pluck('number')->all();
        $this->assertSame(['ACHFPL-PRJ-0001', 'ACHFPL-PRJ-0002', 'ACHFPL-PRJ-0003'], $numbers);
    }

    public function test_it_head_can_view_project_detail(): void
    {
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        $project = Project::create([
            'number'      => Project::generateNumber(),
            'name'        => 'Network Refresh',
            'description' => null,
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'created_by'  => $itHead->id,
        ]);

        $this->actingAs($itHead)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Network Refresh');
    }

    public function test_create_ticket_linked_to_existing_project(): void
    {
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        $project = Project::create([
            'number'      => Project::generateNumber(),
            'name'        => 'Aadhaar Connector Stabilisation',
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'created_by'  => $itHead->id,
        ]);

        $payload = [
            'support_type'                 => 'application',
            'category_id'                  => $this->category->id,
            'subcategory_id'               => $this->subcategory->id,
            'region_id'                    => $this->region->id,
            'branch_id'                    => $this->branch->id,
            'subject'                      => 'Receipt failure',
            'description'                  => 'Receipt generation broken after deploy',
            'employee_contact_employee_id' => 'EMP-1',
            'employee_contact_name'        => $itHead->name,
            'employee_contact_phone'       => '9999999999',
            'project_mode'                 => 'existing',
            'project_id'                   => $project->id,
        ];

        $this->actingAs($itHead)
            ->post(route('tickets.store'), $payload)
            ->assertRedirect();

        $ticket = Ticket::firstWhere('subject', 'Receipt failure');
        $this->assertNotNull($ticket);
        $this->assertSame($project->id, $ticket->project_id);
    }

    public function test_create_ticket_with_inline_new_project_persists_both_atomically(): void
    {
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        $payload = [
            'support_type'                 => 'application',
            'category_id'                  => $this->category->id,
            'subcategory_id'               => $this->subcategory->id,
            'region_id'                    => $this->region->id,
            'branch_id'                    => $this->branch->id,
            'subject'                      => 'Ticket spawning new project',
            'description'                  => 'Should create project + ticket in one go',
            'employee_contact_employee_id' => 'EMP-2',
            'employee_contact_name'        => $itHead->name,
            'employee_contact_phone'       => '8888888888',
            'project_mode'                 => 'new',
            'new_project_name'             => 'Inline Project',
            'new_project_owner_id'         => $owner->id,
            'new_project_description'      => 'Created inline from ticket form',
        ];

        $this->actingAs($itHead)
            ->post(route('tickets.store'), $payload)
            ->assertRedirect();

        $project = Project::firstWhere('name', 'Inline Project');
        $ticket  = Ticket::firstWhere('subject', 'Ticket spawning new project');

        $this->assertNotNull($project, 'Inline project should be created');
        $this->assertNotNull($ticket,  'Ticket should be created');
        $this->assertSame($project->id, $ticket->project_id);
        $this->assertSame($owner->id,   $project->owner_id);
    }

    public function test_junior_resolver_cannot_link_ticket_to_project(): void
    {
        $junior = $this->makeUser('resolver', ['resolver_level' => 'junior']);
        $itHead = $this->makeITHead();
        $owner  = $this->makeManagementOwner();

        $project = Project::create([
            'number'      => Project::generateNumber(),
            'name'        => 'Project A',
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'created_by'  => $itHead->id,
        ]);

        $payload = [
            'support_type'                 => 'application',
            'category_id'                  => $this->category->id,
            'subcategory_id'               => $this->subcategory->id,
            'region_id'                    => $this->region->id,
            'branch_id'                    => $this->branch->id,
            'subject'                      => 'Junior tries to attach project',
            'description'                  => 'Should ignore project_mode',
            'employee_contact_employee_id' => 'EMP-3',
            'employee_contact_name'        => $junior->name,
            'employee_contact_phone'       => '7777777777',
            'project_mode'                 => 'existing',
            'project_id'                   => $project->id,
        ];

        $this->actingAs($junior)->post(route('tickets.store'), $payload);

        $ticket = Ticket::firstWhere('subject', 'Junior tries to attach project');
        $this->assertNotNull($ticket);
        $this->assertNull($ticket->project_id, 'Junior cannot bind a ticket to a project');
    }

    public function test_admin_can_update_a_project(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeManagementOwner();

        $project = Project::create([
            'number'      => Project::generateNumber(),
            'name'        => 'Original Name',
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'created_by'  => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'name'     => 'Renamed Project',
                'owner_id' => $owner->id,
                'status'   => 'on_hold',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertSame('Renamed Project', $project->fresh()->name);
        $this->assertSame('on_hold',         $project->fresh()->status);
    }

    public function test_admin_can_archive_a_project(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeManagementOwner();

        $project = Project::create([
            'number'      => Project::generateNumber(),
            'name'        => 'Archive Me',
            'owner_id'    => $owner->id,
            'status'      => 'active',
            'created_by'  => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
