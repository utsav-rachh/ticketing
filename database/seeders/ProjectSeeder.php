<?php
namespace Database\Seeders;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the Projects module + links a few demo tickets to projects.
 *
 * Owners are management users (per Projects feature spec). The IT Head
 * (Yogesh) is the creator since Admin + IT Head are the only roles
 * authorised to create projects.
 *
 * Idempotent: clears projects + unlinks tickets first, then re-seeds.
 */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $itHead = User::where('email', 'yogesh@altumcredo.com')->first();
        $admin  = User::where('email', 'admin@altumcredo.com')->first();
        $creator = $itHead ?: $admin;

        if (!$creator) {
            $this->command?->warn('ProjectSeeder: IT Head/Admin missing, run UserSeeder first.');
            return;
        }

        // Management owners
        $hari     = User::where('email', 'hari@altumcredo.com')->first();
        $pankaj   = User::where('email', 'pankaj@altumcredo.com')->first();
        $sandeep  = User::where('email', 'sandeep.upadhyay@altumcredo.com')->first();
        $sanjay   = User::where('email', 'sanjay@altumcredo.com')->first();
        $vikrant  = User::where('email', 'vikrant@altumcredo.com')->first();
        $vivek    = User::where('email', 'vivek@altumcredo.com')->first();

        // Wipe existing projects + un-link tickets so reseed is clean.
        Ticket::query()->whereNotNull('project_id')->update(['project_id' => null]);
        Project::withTrashed()->forceDelete();

        $today = Carbon::now();

        $projects = [
            [
                'name'        => 'LMS 2.0 Migration',
                'description' => 'Phase-wise migration of the loan management system from legacy stack to LMS 2.0. Includes data backfill, branch UAT and rollback drills.',
                'owner'       => $hari ?: $sandeep,
                'status'      => 'active',
                'start_date'  => $today->copy()->subDays(20),
                'end_date'    => $today->copy()->addDays(60),
                'link_subjects' => ['LMS payment receipt generation failing'],
            ],
            [
                'name'        => 'Branch Network Refresh — South Region',
                'description' => 'Replace ageing branch routers / WAPs across Karnataka, Tamil Nadu, Andhra Pradesh and Telangana branches. Vendor: Airtel + HP.',
                'owner'       => $pankaj ?: $vivek,
                'status'      => 'active',
                'start_date'  => $today->copy()->subDays(10),
                'end_date'    => $today->copy()->addDays(45),
                'link_subjects' => [
                    'Bengaluru RO — branch ILL link flapping',
                    'Replacement WiFi AP — Coimbatore RO ground floor',
                ],
            ],
            [
                'name'        => 'MD Dashboard Reconciliation',
                'description' => 'Reconcile management dashboard disbursement totals with the report module so leadership sees a single source of truth.',
                'owner'       => $sandeep ?: $hari,
                'status'      => 'active',
                'start_date'  => $today->copy()->subDays(5),
                'end_date'    => $today->copy()->addDays(20),
                'link_subjects' => ['MD dashboard — disbursement totals do not match report module'],
            ],
            [
                'name'        => 'Aadhaar / KYC Connector Stabilisation',
                'description' => 'Audit + harden the Aadhaar verification connector after intermittent failures observed across Vizag and other branches.',
                'owner'       => $vikrant ?: $sandeep,
                'status'      => 'on_hold',
                'start_date'  => $today->copy()->subDays(3),
                'end_date'    => null,
                'link_subjects' => ['Aadhaar connector failing intermittently in Vizag'],
            ],
            [
                'name'        => 'Pune Corp DC Hardware Refresh',
                'description' => 'RAID + UPS health audit and replacement for Pune Corporate Office racks; coordinates Dell + HP vendor SRs.',
                'owner'       => $vivek ?: $hari,
                'status'      => 'active',
                'start_date'  => $today->copy()->subDays(15),
                'end_date'    => $today->copy()->addDays(30),
                'link_subjects' => ['Pune Corp — RAID degraded on rack-2 server'],
            ],
            [
                'name'        => 'Q1 Office Compliance — ID Cards & Facilities',
                'description' => 'New-joiner ID card reprints + facility (HVAC) compliance checks for all ROs ahead of internal audit.',
                'owner'       => $sanjay ?: $sandeep,
                'status'      => 'completed',
                'start_date'  => $today->copy()->subDays(60),
                'end_date'    => $today->copy()->subDays(5),
                'link_subjects' => [
                    'ID card reprint — new joiner EMP-2026-128',
                    'AC not cooling — Warangal branch front office',
                ],
            ],
        ];

        $seq = 0;
        $created = 0;
        $linked = 0;

        foreach ($projects as $data) {
            if (!$data['owner']) {
                continue; // missing owner — skip rather than fail
            }
            $seq++;

            $project = Project::create([
                'number'      => sprintf('ACHFPL-PRJ-%04d', $seq),
                'name'        => $data['name'],
                'description' => $data['description'],
                'owner_id'    => $data['owner']->id,
                'status'      => $data['status'],
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
                'created_by'  => $creator->id,
            ]);
            $created++;

            foreach (($data['link_subjects'] ?? []) as $subject) {
                $linked += Ticket::where('subject', $subject)
                    ->whereNull('project_id')
                    ->update(['project_id' => $project->id]);
            }
        }

        $this->command?->info("ProjectSeeder: created {$created} projects, linked {$linked} tickets.");
    }
}
