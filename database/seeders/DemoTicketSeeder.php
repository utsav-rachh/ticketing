<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketExpense;
use App\Models\TicketUpdate;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoTicketSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('email', 'admin@altumcredo.com')->first();
        $itHead   = User::where('email', 'yogesh@altumcredo.com')->first();
        $appTL    = User::where('email', 'prashant@altumcredo.com')->first();
        $appJr    = User::where('email', 'sayali@altumcredo.com')->first();
        $infraTL  = User::where('email', 'vishal@altumcredo.com')->first();
        $infraJr  = User::where('email', 'utsav@altumcredo.com')->first();
        $adminTL  = User::where('email', 'tl.adminhr@altumcredo.com')->first();
        $adminJr  = User::where('email', 'junior.adminhr@altumcredo.com')->first();
        $md       = User::where('email', 'md@altumcredo.com')->first();

        if (!$admin || !$appTL || !$infraTL) {
            $this->command?->warn('DemoTicketSeeder: required users missing, run UserSeeder first.');
            return;
        }

        // Branch employees (creators from across the country)
        $ravi    = User::where('email', 'ravi.mum@altumcredo.com')->first();
        $neha    = User::where('email', 'neha.pne@altumcredo.com')->first();
        $arjun   = User::where('email', 'arjun.del@altumcredo.com')->first();
        $simran  = User::where('email', 'simran.chd@altumcredo.com')->first();
        $karthik = User::where('email', 'karthik.blr@altumcredo.com')->first();
        $priya   = User::where('email', 'priya.chn@altumcredo.com')->first();
        $anindya = User::where('email', 'anindya.kol@altumcredo.com')->first();
        $manoj   = User::where('email', 'manoj.bhu@altumcredo.com')->first();

        $mumbaiBranch = Branch::where('code', 'BR-MUM-01')->first();
        $puneBranch   = Branch::where('code', 'BR-PNE-01')->first();
        $delhiBranch  = Branch::where('code', 'BR-DEL-01')->first();
        $chdBranch    = Branch::where('code', 'BR-CHD-01')->first();
        $blrBranch    = Branch::where('code', 'BR-BLR-01')->first();
        $chnBranch    = Branch::where('code', 'BR-CHN-01')->first();
        $kolBranch    = Branch::where('code', 'BR-KOL-01')->first();
        $bhuBranch    = Branch::where('code', 'BR-BHU-01')->first();
        $anyBranch    = $mumbaiBranch ?: Branch::first();
        $dellVendor   = Vendor::where('name', 'like', '%Dell%')->first() ?: Vendor::first();
        $ciscoVendor  = Vendor::where('name', 'like', '%Cisco%')->first() ?: Vendor::skip(1)->first() ?: $dellVendor;

        $appCat    = Category::where('support_type', 'application')->first();
        $infraCat  = Category::where('support_type', 'infrastructure')->first();
        $adminCat  = Category::where('support_type', 'admin')->first();
        $appSub    = Subcategory::where('category_id', $appCat?->id)->first();
        $infraSub  = Subcategory::where('category_id', $infraCat?->id)->first();
        $adminSub  = Subcategory::where('category_id', $adminCat?->id)->first();

        if (!$appCat || !$infraCat || !$adminCat) {
            $this->command?->warn('DemoTicketSeeder: categories missing, run CategorySeeder first.');
            return;
        }

        $tickets = [
            // 1. Open application ticket, unassigned — Mumbai Ops
            [
                'ticket_number' => 'TKT-DEMO-001',
                'support_type'  => 'application',
                'category_id'   => $appCat->id,
                'subcategory_id'=> $appSub?->id,
                'branch_id'     => $mumbaiBranch?->id ?? $anyBranch?->id,
                'subject'       => 'LOS application not loading',
                'description'   => 'Loan origination system shows a blank screen after login. Started this morning, tried on two browsers.',
                'priority'      => 'high',
                'status'        => 'open',
                'created_by'    => ($ravi?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subHours(1),
                'tat_hours'     => 4,
                'activities'    => [
                    ['action_type' => 'created', 'description' => 'Ticket created by ' . ($ravi?->name ?? $md->name), 'user' => $ravi ?? $md, 'at' => Carbon::now()->subHours(1)],
                ],
            ],

            // 2. Assigned application ticket — Pune Sales
            [
                'ticket_number' => 'TKT-DEMO-002',
                'support_type'  => 'application',
                'category_id'   => $appCat->id,
                'subcategory_id'=> $appSub?->id,
                'branch_id'     => $puneBranch?->id ?? $anyBranch?->id,
                'subject'       => 'Report export to Excel fails',
                'description'   => 'Clicking Export on the disbursement report throws a 500 error.',
                'priority'      => 'medium',
                'status'        => 'assigned',
                'assigned_to'   => $appJr?->id,
                'assigned_by'   => $appTL->id,
                'assigned_at'   => Carbon::now()->subHours(5),
                'created_by'    => ($neha?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subHours(6),
                'tat_hours'     => 8,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($neha?->name ?? $md->name), 'user' => $neha ?? $md, 'at' => Carbon::now()->subHours(6)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $appJr?->name,                     'user' => $appTL,       'at' => Carbon::now()->subHours(5)],
                ],
                'updates' => [
                    ['note' => 'Reproduced locally, looks like a timeout in the PDF renderer. Investigating.', 'user' => $appJr, 'at' => Carbon::now()->subHours(4)],
                ],
            ],

            // 3. In-progress infra ticket, with vendor + vendor_reference — Delhi Credit
            [
                'ticket_number' => 'TKT-DEMO-003',
                'support_type'  => 'infrastructure',
                'category_id'   => $infraCat->id,
                'subcategory_id'=> $infraSub?->id,
                'branch_id'     => $delhiBranch?->id ?? $anyBranch?->id,
                'vendor_id'     => $dellVendor?->id,
                'vendor_reference' => 'DELL-SR-88472361',
                'subject'       => 'Server RAID degraded — Delhi HQ',
                'description'   => 'Dell PERC alert on rack-2 server. Running on single disk. Requested replacement.',
                'priority'      => 'high',
                'status'        => 'in_progress',
                'assigned_to'   => $infraJr?->id,
                'assigned_by'   => $infraTL->id,
                'assigned_at'   => Carbon::now()->subDays(1),
                'created_by'    => ($arjun?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subDays(1)->subHours(2),
                'tat_hours'     => 24,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($arjun?->name ?? $md->name), 'user' => $arjun ?? $md, 'at' => Carbon::now()->subDays(1)->subHours(2)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $infraJr?->name,                    'user' => $infraTL,       'at' => Carbon::now()->subDays(1)],
                    ['action_type' => 'vendor_reference_updated', 'description' => 'Vendor reference set to: DELL-SR-88472361', 'old_value' => null, 'new_value' => 'DELL-SR-88472361', 'user' => $infraJr, 'at' => Carbon::now()->subHours(20)],
                ],
                'updates' => [
                    ['status_from' => 'assigned', 'status_to' => 'in_progress', 'note' => 'Logged with Dell support. SR number captured.', 'user' => $infraJr, 'at' => Carbon::now()->subHours(20)],
                    ['note' => 'Dell dispatching replacement drive, ETA tomorrow 10 AM.', 'user' => $infraJr, 'at' => Carbon::now()->subHours(8)],
                ],
            ],

            // 4. On-hold infra ticket — Bengaluru IT Support
            [
                'ticket_number' => 'TKT-DEMO-004',
                'support_type'  => 'infrastructure',
                'category_id'   => $infraCat->id,
                'subcategory_id'=> $infraSub?->id,
                'branch_id'     => $blrBranch?->id ?? $anyBranch?->id,
                'vendor_id'     => $ciscoVendor?->id,
                'vendor_reference' => 'CSC-TAC-991203',
                'subject'       => 'Core switch packet loss — Bengaluru',
                'description'   => 'Intermittent 15-20% packet loss on branch VLAN 40.',
                'priority'      => 'medium',
                'status'        => 'hold',
                'hold_started_at' => Carbon::now()->subHours(6),
                'hold_total_seconds' => 0,
                'assigned_to'   => $infraTL->id,
                'assigned_by'   => $itHead?->id ?? $admin->id,
                'assigned_at'   => Carbon::now()->subDays(2),
                'created_by'    => ($karthik?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subDays(2)->subHours(3),
                'tat_hours'     => 12,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($karthik?->name ?? $md->name), 'user' => $karthik ?? $md, 'at' => Carbon::now()->subDays(2)->subHours(3)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $infraTL->name,                       'user' => $itHead ?? $admin, 'at' => Carbon::now()->subDays(2)],
                    ['action_type' => 'status_changed', 'description' => 'Ticket put on hold — waiting on Cisco TAC', 'old_value' => 'in_progress', 'new_value' => 'hold', 'user' => $infraTL, 'at' => Carbon::now()->subHours(6)],
                ],
                'updates' => [
                    ['note' => 'Logged TAC ticket with Cisco for deeper packet capture review.', 'user' => $infraTL, 'at' => Carbon::now()->subHours(10)],
                    ['status_from' => 'in_progress', 'status_to' => 'hold', 'note' => 'On hold: awaiting Cisco engineer callback.', 'user' => $infraTL, 'at' => Carbon::now()->subHours(6)],
                ],
            ],

            // 5. Resolved application ticket — Chennai Ops
            [
                'ticket_number' => 'TKT-DEMO-005',
                'support_type'  => 'application',
                'category_id'   => $appCat->id,
                'subcategory_id'=> $appSub?->id,
                'branch_id'     => $chnBranch?->id ?? $anyBranch?->id,
                'subject'       => 'Password reset not sending email',
                'description'   => 'The Forgot Password flow does not deliver the reset email.',
                'priority'      => 'medium',
                'status'        => 'resolved',
                'assigned_to'   => $appJr?->id,
                'assigned_by'   => $appTL->id,
                'assigned_at'   => Carbon::now()->subDays(3),
                'resolved_at'   => Carbon::now()->subDays(2),
                'created_by'    => ($priya?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subDays(3)->subHours(1),
                'tat_hours'     => 8,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($priya?->name ?? $md->name), 'user' => $priya ?? $md, 'at' => Carbon::now()->subDays(3)->subHours(1)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $appJr?->name,                      'user' => $appTL,        'at' => Carbon::now()->subDays(3)],
                ],
                'updates' => [
                    ['status_from' => 'assigned', 'status_to' => 'in_progress', 'note' => 'SMTP queue was stuck, restarting mailer.', 'user' => $appJr, 'at' => Carbon::now()->subDays(2)->subHours(6)],
                    ['status_from' => 'in_progress', 'status_to' => 'resolved', 'note' => 'Mailer restarted, delivery restored. Verified with a test account.', 'user' => $appJr, 'at' => Carbon::now()->subDays(2)],
                ],
            ],

            // 6. Closed admin ticket — Chandigarh HR
            [
                'ticket_number' => 'TKT-DEMO-006',
                'support_type'  => 'admin',
                'category_id'   => $adminCat->id,
                'subcategory_id'=> $adminSub?->id,
                'branch_id'     => $chdBranch?->id ?? $anyBranch?->id,
                'subject'       => 'ID card reprint for new joiner',
                'description'   => 'Please reprint ID for new joiner, employee code EMP-2026-044.',
                'priority'      => 'low',
                'status'        => 'closed',
                'assigned_to'   => $adminJr?->id,
                'assigned_by'   => $adminTL?->id ?? $admin->id,
                'assigned_at'   => Carbon::now()->subDays(5),
                'resolved_at'   => Carbon::now()->subDays(4),
                'closed_at'     => Carbon::now()->subDays(3),
                'created_by'    => ($simran?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subDays(5)->subHours(2),
                'tat_hours'     => 24,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($simran?->name ?? $md->name), 'user' => $simran ?? $md,    'at' => Carbon::now()->subDays(5)->subHours(2)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . ($adminJr?->name ?? 'Admin HR'),     'user' => $adminTL ?? $admin, 'at' => Carbon::now()->subDays(5)],
                ],
                'updates' => [
                    ['status_from' => 'assigned', 'status_to' => 'resolved', 'note' => 'ID card printed and handed over.',  'user' => $adminJr ?? $admin, 'at' => Carbon::now()->subDays(4)],
                    ['status_from' => 'resolved', 'status_to' => 'closed',   'note' => 'Confirmed receipt, closing ticket.', 'user' => $simran ?? $md,     'at' => Carbon::now()->subDays(3)],
                ],
            ],

            // 7. TAT-violated critical infra ticket — Kolkata Credit
            [
                'ticket_number' => 'TKT-DEMO-007',
                'support_type'  => 'infrastructure',
                'category_id'   => $infraCat->id,
                'subcategory_id'=> $infraSub?->id,
                'branch_id'     => $kolBranch?->id ?? $anyBranch?->id,
                'vendor_id'     => $dellVendor?->id,
                'subject'       => 'UPS battery failure in Kolkata branch',
                'description'   => 'Main UPS reporting battery failure. Site running on mains-only.',
                'priority'      => 'critical',
                'status'        => 'assigned',
                'is_tat_violated' => true,
                'assigned_to'   => $infraTL->id,
                'assigned_by'   => $admin->id,
                'assigned_at'   => Carbon::now()->subHours(30),
                'created_by'    => ($anindya?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subHours(30),
                'tat_hours'     => 2,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Ticket created by ' . ($anindya?->name ?? $md->name), 'user' => $anindya ?? $md, 'at' => Carbon::now()->subHours(30)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $infraTL->name,                       'user' => $admin,           'at' => Carbon::now()->subHours(30)],
                    ['action_type' => 'tat_breached', 'description' => 'TAT breached', 'user' => $admin, 'at' => Carbon::now()->subHours(28)],
                ],
            ],

            // 8. Red-flagged management ticket (critical)
            [
                'ticket_number' => 'TKT-DEMO-008',
                'support_type'  => 'application',
                'category_id'   => $appCat->id,
                'subcategory_id'=> $appSub?->id,
                'branch_id'     => $anyBranch?->id,
                'subject'       => 'MD dashboard shows wrong disbursement totals',
                'description'   => 'Numbers on the management dashboard do not reconcile with the report module.',
                'priority'      => 'critical',
                'status'        => 'in_progress',
                'is_red_flag'   => true,
                'assigned_to'   => $appTL->id,
                'assigned_by'   => $itHead?->id ?? $admin->id,
                'assigned_at'   => Carbon::now()->subHours(3),
                'created_by'    => $md->id,
                'created_at'    => Carbon::now()->subHours(4),
                'tat_hours'     => 4,
                'activities'    => [
                    ['action_type' => 'created',  'description' => 'Red-flagged ticket created by management',      'user' => $md,    'at' => Carbon::now()->subHours(4)],
                    ['action_type' => 'assigned', 'description' => 'Assigned to ' . $appTL->name,                  'user' => $itHead ?? $admin, 'at' => Carbon::now()->subHours(3)],
                ],
                'updates' => [
                    ['note' => 'Acknowledged. Comparing warehouse totals with the live report query now.', 'user' => $appTL, 'at' => Carbon::now()->subHours(2)],
                ],
            ],

            // 9. Infra ticket with pending expense — Bhubaneswar Sales
            [
                'ticket_number' => 'TKT-DEMO-009',
                'support_type'  => 'infrastructure',
                'category_id'   => $infraCat->id,
                'subcategory_id'=> $infraSub?->id,
                'branch_id'     => $bhuBranch?->id ?? $anyBranch?->id,
                'vendor_id'     => $ciscoVendor?->id,
                'subject'       => 'Replacement WiFi AP for ground floor',
                'description'   => 'Existing AP at front desk is unresponsive. Need replacement unit.',
                'priority'      => 'medium',
                'status'        => 'in_progress',
                'assigned_to'   => $infraJr?->id,
                'assigned_by'   => $infraTL->id,
                'assigned_at'   => Carbon::now()->subDays(1)->subHours(2),
                'created_by'    => ($manoj?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subDays(1)->subHours(4),
                'tat_hours'     => 16,
                'activities'    => [
                    ['action_type' => 'created',      'description' => 'Ticket created by ' . ($manoj?->name ?? $md->name), 'user' => $manoj ?? $md, 'at' => Carbon::now()->subDays(1)->subHours(4)],
                    ['action_type' => 'assigned',     'description' => 'Assigned to ' . $infraJr?->name,                    'user' => $infraTL,       'at' => Carbon::now()->subDays(1)->subHours(2)],
                    ['action_type' => 'expense_added','description' => 'Expense submitted for approval: ₹8,500.00',         'user' => $infraJr,      'at' => Carbon::now()->subHours(5)],
                ],
                'updates' => [
                    ['note' => 'Ordered replacement AP; invoice uploaded for approval.', 'user' => $infraJr, 'at' => Carbon::now()->subHours(5)],
                ],
                'expense' => [
                    'description' => 'Cisco WiFi AP (ground floor replacement)',
                    'amount'      => 8500.00,
                    'expense_date'=> Carbon::now()->subHours(5)->toDateString(),
                    'status'      => 'pending',
                    'added_by'    => $infraJr?->id,
                ],
            ],

            // 10. Pending info — Pune Sales
            [
                'ticket_number' => 'TKT-DEMO-010',
                'support_type'  => 'application',
                'category_id'   => $appCat->id,
                'subcategory_id'=> $appSub?->id,
                'branch_id'     => $puneBranch?->id ?? $anyBranch?->id,
                'subject'       => 'Cannot log in to CRM',
                'description'   => 'Login screen says invalid credentials for the last hour.',
                'priority'      => 'medium',
                'status'        => 'pending_info',
                'assigned_to'   => $appJr?->id,
                'assigned_by'   => $appTL->id,
                'assigned_at'   => Carbon::now()->subHours(4),
                'created_by'    => ($neha?->id) ?? $md->id,
                'created_at'    => Carbon::now()->subHours(5),
                'tat_hours'     => 8,
                'activities'    => [
                    ['action_type' => 'created',        'description' => 'Ticket created by ' . ($neha?->name ?? $md->name), 'user' => $neha ?? $md, 'at' => Carbon::now()->subHours(5)],
                    ['action_type' => 'assigned',       'description' => 'Assigned to ' . $appJr?->name,                     'user' => $appTL,       'at' => Carbon::now()->subHours(4)],
                    ['action_type' => 'status_changed', 'description' => 'Requested more information',    'old_value' => 'assigned', 'new_value' => 'pending_info', 'user' => $appJr, 'at' => Carbon::now()->subHours(2)],
                ],
                'updates' => [
                    ['status_from' => 'assigned', 'status_to' => 'pending_info', 'note' => 'Can you confirm your employee ID and the exact error text?', 'user' => $appJr, 'at' => Carbon::now()->subHours(2)],
                ],
            ],
        ];

        foreach ($tickets as $data) {
            $activities = $data['activities'] ?? [];
            $updates    = $data['updates']    ?? [];
            $expense    = $data['expense']    ?? null;
            unset($data['activities'], $data['updates'], $data['expense']);

            // Skip silently if subcategory/category not resolvable for this support_type.
            if (empty($data['subcategory_id']) || empty($data['category_id'])) {
                continue;
            }

            $createdAt = $data['created_at'];
            $tatHours  = $data['tat_hours'];
            $data['tat_deadline'] = $createdAt->copy()->addHours((int) $tatHours);

            $ticket = Ticket::updateOrCreate(
                ['ticket_number' => $data['ticket_number']],
                $data + ['updated_at' => Carbon::now()]
            );

            // Reset child rows so reseeding gives clean state
            $ticket->activities()->delete();
            $ticket->updates()->delete();
            $ticket->expenses()->delete();

            foreach ($activities as $a) {
                TicketActivity::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $a['user']?->id,
                    'action_type' => $a['action_type'],
                    'description' => $a['description'],
                    'old_value'   => $a['old_value'] ?? null,
                    'new_value'   => $a['new_value'] ?? null,
                    'created_at'  => $a['at'],
                ]);
            }

            foreach ($updates as $u) {
                TicketUpdate::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $u['user']?->id,
                    'status_from' => $u['status_from'] ?? null,
                    'status_to'   => $u['status_to']   ?? null,
                    'note'        => $u['note']        ?? null,
                    'created_at'  => $u['at'],
                ]);
            }

            if ($expense) {
                TicketExpense::create([
                    'ticket_id'    => $ticket->id,
                    'added_by'     => $expense['added_by'],
                    'description'  => $expense['description'],
                    'amount'       => $expense['amount'],
                    'expense_date' => $expense['expense_date'],
                    'status'       => $expense['status'],
                ]);
            }
        }

        $this->command?->info('Seeded ' . count($tickets) . ' demo tickets.');
    }
}
