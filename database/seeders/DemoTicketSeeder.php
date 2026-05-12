<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketExpense;
use App\Models\TicketUpdate;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Re-creates the demo ticket dataset against the current org structure
 * (Altum Credo states/branches/users from BranchSeeder + UserSeeder).
 *
 * Idempotent: clears existing tickets and re-seeds a curated set.
 */
class DemoTicketSeeder extends Seeder
{
    public function run(): void
    {
        // ── Resolve the user roster (created by UserSeeder) ──────────────
        $admin    = User::where('email', 'admin@altumcredo.com')->first();
        $yogesh   = User::where('email', 'yogesh@altumcredo.com')->first();             // CISO
        $prashant = User::where('email', 'prashant.d@altumcredo.com')->first();         // App TL
        $vishalS  = User::where('email', 'vishal@altumcredo.com')->first();             // Infra TL
        $sayli    = User::where('email', 'sayali@altumcredo.com')->first();             // App Jr
        $omkar    = User::where('email', 'omkar.jadhav@altumcredo.com')->first();       // App Jr
        $kalyani  = User::where('email', 'kalyani.g@altumcredo.com')->first();          // App Jr
        $vishalG  = User::where('email', 'it.trainee@altumcredo.com')->first();         // Infra Jr
        $kumar    = User::where('email', 'kumar@altumcredo.com')->first();              // Infra Jr

        // Management creators (CEO Office, Sales)
        $hari      = User::where('email', 'hari@altumcredo.com')->first();
        $pankaj    = User::where('email', 'pankaj@altumcredo.com')->first();
        $sandeep   = User::where('email', 'sandeep.upadhyay@altumcredo.com')->first();
        $sanjay    = User::where('email', 'sanjay@altumcredo.com')->first();
        $sarveish  = User::where('email', 'sarveish@altumcredo.com')->first();
        $vikrant   = User::where('email', 'vikrant@altumcredo.com')->first();
        $vivek     = User::where('email', 'vivek@altumcredo.com')->first();
        $ravindra  = User::where('email', 'ravindra.n@altumcredo.com')->first();
        $ujjwal    = User::where('email', 'ujjwal@altumcredo.com')->first();

        if (!$admin || !$prashant || !$vishalS) {
            $this->command?->warn('DemoTicketSeeder: required users missing, run UserSeeder first.');
            return;
        }

        // ── Branches (mapped to current BranchSeeder codes) ──────────────
        $puneCorp     = Branch::where('code', 'BR-MH-01')->first();   // Pune Corporate Office
        $puneRO       = Branch::where('code', 'BR-MH-02')->first();   // Pune RO
        $kolhapur     = Branch::where('code', 'BR-MH-04')->first();
        $nashik       = Branch::where('code', 'BR-MH-05')->first();
        $nagpur       = Branch::where('code', 'BR-MH-06')->first();
        $bangaloreRO  = Branch::where('code', 'BR-KA-01')->first();
        $mysore       = Branch::where('code', 'BR-KA-02')->first();
        $hassan       = Branch::where('code', 'BR-KA-07')->first();
        $coimbatore   = Branch::where('code', 'BR-TN-01')->first();
        $madurai      = Branch::where('code', 'BR-TN-02')->first();
        $jaipurRO     = Branch::where('code', 'BR-RJ-01')->first();
        $udaipur      = Branch::where('code', 'BR-RJ-08')->first();
        $vijaywadaRO  = Branch::where('code', 'BR-AP-01')->first();
        $vizag        = Branch::where('code', 'BR-AP-05')->first();
        $hyderabadRO  = Branch::where('code', 'BR-TG-01')->first();
        $warangal     = Branch::where('code', 'BR-TG-11')->first();
        $anyBranch    = $puneCorp ?: Branch::first();

        // ── Vendors ──────────────────────────────────────────────────────
        $dellVendor   = Vendor::where('name', 'like', '%Dell%')->first();
        $hpVendor     = Vendor::where('name', 'like', '%HP%')->first();
        $airtelVendor = Vendor::where('name', 'like', '%Airtel%')->first();

        // ── Categories / subcategories ───────────────────────────────────
        // Categories are unique by support_type+name; we look up specific issue types
        // so demo tickets actually carry meaningful classifications.
        $appCat   = Category::where('support_type', 'application')->where('name', 'Rapid Sales')->first();
        $appLMS   = Category::where('support_type', 'application')->where('name', 'LMS')->first();
        $appCredit= Category::where('support_type', 'application')->where('name', 'Credit')->first();
        $infraCat = Category::where('support_type', 'infrastructure')->where('name', 'Server')->first();
        $infraNet = Category::where('support_type', 'infrastructure')->where('name', 'Internet / Network')->first();
        $infraLap = Category::where('support_type', 'infrastructure')->where('name', 'Laptop')->first();
        $infraPrn = Category::where('support_type', 'infrastructure')->where('name', 'Printer')->first();
        $adminFac = Category::where('support_type', 'admin')->where('name', 'Facility Management')->first();
        $adminHR  = Category::where('support_type', 'admin')->where('name', 'HR Queries')->first();
        $adminSup = Category::where('support_type', 'admin')->where('name', 'Office Supplies')->first();

        if (!$appCat || !$infraCat || !$adminFac) {
            $this->command?->warn('DemoTicketSeeder: categories missing, run CategorySeeder first.');
            return;
        }

        $sub = function (?Category $cat, string $name) {
            if (!$cat) return null;
            return Subcategory::where('category_id', $cat->id)->where('name', $name)->first()
                ?: Subcategory::where('category_id', $cat->id)->first();
        };

        // ── Wipe existing demo + ticket children so reseed is clean ──────
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TicketAttachment::query()->delete();
        TicketExpense::query()->delete();
        TicketUpdate::query()->delete();
        TicketActivity::query()->delete();
        Ticket::withTrashed()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Counter for ticket numbers — start at 1 to match Ticket::generateTicketNumber()
        $seq = 0;
        $next = function () use (&$seq) {
            $seq++;
            return sprintf('ACHFPL-%04d', $seq);
        };

        $now = Carbon::now();

        $tickets = [
            // 1. Open routine — Sayli's region (Karnataka, Mysore branch)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appCat,
                'subcategory'   => $sub($appCat, 'Unable to login'),
                'branch'        => $mysore,
                'subject'       => 'Rapid Sales — unable to login this morning',
                'description'   => 'Login screen returns "invalid credentials" for sales team in Mysore branch since 8:30 AM.',
                'priority'      => 'high',
                'status'        => 'open',
                'creator'       => $hari ?? $admin,
                'created_at'    => $now->copy()->subHours(1),
                'tat_hours'     => 4,
                'activities'    => [
                    ['action_type' => 'created', 'who' => 'creator', 'at' => $now->copy()->subHours(1)],
                ],
            ],

            // 2. Assigned application ticket — Coimbatore (Tamil Nadu)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appLMS ?: $appCat,
                'subcategory'   => $sub($appLMS ?: $appCat, 'Payment receipt Failed'),
                'branch'        => $coimbatore,
                'subject'       => 'LMS payment receipt generation failing',
                'description'   => 'Disbursement receipts are not being generated since the morning batch run. Branch is unable to share docs with customers.',
                'priority'      => 'high',
                'status'        => 'assigned',
                'creator'       => $sandeep,
                'assignee'      => $sayli,
                'assigner'      => $prashant,
                'created_at'    => $now->copy()->subHours(6),
                'assigned_at'   => $now->copy()->subHours(5),
                'tat_hours'     => 8,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subHours(6)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subHours(5),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(4),
                        'note' => 'Reproduced — overnight batch failed on tax-config refresh, investigating.'],
                ],
            ],

            // 3. In-progress infrastructure ticket with vendor (Pune Corp)
            [
                'ticket_number' => $next(),
                'support_type'  => 'infrastructure',
                'category'      => $infraCat,
                'subcategory'   => $sub($infraCat, 'Server Down / Unresponsive'),
                'branch'        => $puneCorp,
                'vendor'        => $dellVendor,
                'vendor_reference' => 'DELL-SR-88472361',
                'subject'       => 'Pune Corp — RAID degraded on rack-2 server',
                'description'   => 'Dell PERC alert. Running on single disk. Logged with Dell support, awaiting drive.',
                'priority'      => 'high',
                'status'        => 'in_progress',
                'creator'       => $vivek,
                'assignee'      => $vishalG,
                'assigner'      => $vishalS,
                'created_at'    => $now->copy()->subDays(1)->subHours(2),
                'assigned_at'   => $now->copy()->subDays(1),
                'tat_hours'     => 24,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(1)->subHours(2)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(1),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'vendor_reference_updated', 'who' => 'assignee', 'at' => $now->copy()->subHours(20),
                        'description' => fn () => 'Vendor reference set to: DELL-SR-88472361',
                        'old_value' => null, 'new_value' => 'DELL-SR-88472361'],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(20),
                        'status_from' => 'assigned', 'status_to' => 'in_progress',
                        'note' => 'Logged with Dell support. SR captured.'],
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(8),
                        'note' => 'Dell dispatching replacement drive, ETA tomorrow 10 AM.'],
                ],
            ],

            // 4. On-hold infra ticket — Bengaluru RO (waiting on Airtel)
            [
                'ticket_number' => $next(),
                'support_type'  => 'infrastructure',
                'category'      => $infraNet ?: $infraCat,
                'subcategory'   => $sub($infraNet ?: $infraCat, 'WiFi Not Connecting'),
                'branch'        => $bangaloreRO,
                'vendor'        => $airtelVendor,
                'vendor_reference' => 'ATL-CRN-99102345',
                'subject'       => 'Bengaluru RO — branch ILL link flapping',
                'description'   => 'Internet ILL link drops every 30-40 minutes. Branch unable to access LMS reliably.',
                'priority'      => 'medium',
                'status'        => 'hold',
                'hold_started_at' => $now->copy()->subHours(6),
                'creator'       => $sarveish,
                'assignee'      => $vishalS,
                'assigner'      => $yogesh ?? $admin,
                'created_at'    => $now->copy()->subDays(2)->subHours(3),
                'assigned_at'   => $now->copy()->subDays(2),
                'tat_hours'     => 12,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(2)->subHours(3)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(2),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'status_changed', 'who' => 'assignee', 'at' => $now->copy()->subHours(6),
                        'description' => fn () => 'Ticket put on hold — waiting on Airtel NOC',
                        'old_value' => 'in_progress', 'new_value' => 'hold'],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(10),
                        'note' => 'Logged with Airtel NOC for line trace and CRN allocation.'],
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(6),
                        'status_from' => 'in_progress', 'status_to' => 'hold',
                        'note' => 'On hold: Airtel field engineer dispatching tomorrow morning.'],
                ],
            ],

            // 5. Resolved application — Vijaywada RO (Andhra Pradesh)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appCredit ?: $appCat,
                'subcategory'   => $sub($appCredit ?: $appCat, 'Document Upload'),
                'branch'        => $vijaywadaRO,
                'subject'       => 'Credit module — document upload throwing 500 error',
                'description'   => 'Uploading PD verification documents fails with a server error. Multiple users affected.',
                'priority'      => 'medium',
                'status'        => 'resolved',
                'creator'       => $ravindra,
                'assignee'      => $omkar,
                'assigner'      => $prashant,
                'created_at'    => $now->copy()->subDays(3)->subHours(1),
                'assigned_at'   => $now->copy()->subDays(3),
                'resolved_at'   => $now->copy()->subDays(2),
                'tat_hours'     => 8,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(3)->subHours(1)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(3),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subDays(2)->subHours(6),
                        'status_from' => 'assigned', 'status_to' => 'in_progress',
                        'note' => 'Storage path permissions had reset after deploy, fixing.'],
                    ['who' => 'assignee', 'at' => $now->copy()->subDays(2),
                        'status_from' => 'in_progress', 'status_to' => 'resolved',
                        'note' => 'Permissions corrected, uploads verified end-to-end.'],
                ],
            ],

            // 6. Closed admin/HR ticket — Jaipur RO
            [
                'ticket_number' => $next(),
                'support_type'  => 'admin',
                'category'      => $adminSup ?: $adminFac,
                'subcategory'   => $sub($adminSup ?: $adminFac, 'ID Card Issue / Replacement'),
                'branch'        => $jaipurRO,
                'subject'       => 'ID card reprint — new joiner EMP-2026-128',
                'description'   => 'Please reprint ID card for new joiner.',
                'priority'      => 'low',
                'status'        => 'closed',
                'creator'       => $sanjay,
                'assignee'      => $admin,
                'assigner'      => $admin,
                'created_at'    => $now->copy()->subDays(5)->subHours(2),
                'assigned_at'   => $now->copy()->subDays(5),
                'resolved_at'   => $now->copy()->subDays(4),
                'closed_at'     => $now->copy()->subDays(3),
                'tat_hours'     => 24,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(5)->subHours(2)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(5),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subDays(4),
                        'status_from' => 'assigned', 'status_to' => 'resolved',
                        'note' => 'ID card printed and handed over to admin desk.'],
                    ['who' => 'creator', 'at' => $now->copy()->subDays(3),
                        'status_from' => 'resolved', 'status_to' => 'closed',
                        'note' => 'Confirmed receipt, closing ticket.'],
                ],
            ],

            // 7. TAT-violated critical infra — Hyderabad RO
            [
                'ticket_number' => $next(),
                'support_type'  => 'infrastructure',
                'category'      => $infraCat,
                'subcategory'   => $sub($infraCat, 'Server Down / Unresponsive'),
                'branch'        => $hyderabadRO,
                'vendor'        => $hpVendor,
                'subject'       => 'UPS battery failure — Hyderabad RO',
                'description'   => 'Main UPS reporting battery failure. Site running on mains-only. Replacement urgent.',
                'priority'      => 'critical',
                'status'        => 'assigned',
                'is_tat_violated' => true,
                'creator'       => $hari,
                'assignee'      => $vishalS,
                'assigner'      => $admin,
                'created_at'    => $now->copy()->subHours(30),
                'assigned_at'   => $now->copy()->subHours(30),
                'tat_hours'     => 2,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subHours(30)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subHours(30),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'tat_breached', 'who' => 'assigner', 'at' => $now->copy()->subHours(28),
                        'description' => fn () => 'TAT breached'],
                ],
            ],

            // 8. Red-flagged management ticket (critical, in progress)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appCredit ?: $appCat,
                'subcategory'   => $sub($appCredit ?: $appCat, 'Deal Summary'),
                'branch'        => $puneCorp,
                'subject'       => 'MD dashboard — disbursement totals do not match report module',
                'description'   => 'Numbers on the management dashboard do not reconcile with the daily disbursement report.',
                'priority'      => 'critical',
                'status'        => 'in_progress',
                'is_red_flag'   => true,
                'creator'       => $pankaj,
                'assignee'      => $prashant,
                'assigner'      => $yogesh ?? $admin,
                'created_at'    => $now->copy()->subHours(4),
                'assigned_at'   => $now->copy()->subHours(3),
                'tat_hours'     => 4,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subHours(4),
                        'description' => fn () => 'Red-flagged ticket created by management'],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subHours(3),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(2),
                        'note' => 'Acknowledged. Comparing warehouse totals with the live report query now.'],
                ],
            ],

            // 9. Red-flagged + management — pending review (Vizag)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appCat,
                'subcategory'   => $sub($appCat, 'Connector Issue'),
                'branch'        => $vizag,
                'subject'       => 'Aadhaar connector failing intermittently in Vizag',
                'description'   => 'Aadhaar verification service times out 30-40% of the time. Slowing down all new files.',
                'priority'      => 'critical',
                'status'        => 'open',
                'is_red_flag'   => true,
                'creator'       => $sandeep,
                'created_at'    => $now->copy()->subHours(2),
                'tat_hours'     => 4,
                'activities' => [
                    ['action_type' => 'created', 'who' => 'creator', 'at' => $now->copy()->subHours(2),
                        'description' => fn () => 'Red-flagged ticket created by management'],
                ],
            ],

            // 10. Infra with pending expense — Coimbatore (replacement WAP)
            [
                'ticket_number' => $next(),
                'support_type'  => 'infrastructure',
                'category'      => $infraNet ?: $infraCat,
                'subcategory'   => $sub($infraNet ?: $infraCat, 'WiFi Not Connecting'),
                'branch'        => $coimbatore,
                'vendor'        => $hpVendor,
                'subject'       => 'Replacement WiFi AP — Coimbatore RO ground floor',
                'description'   => 'Existing AP at front desk is unresponsive. Need replacement unit.',
                'priority'      => 'medium',
                'status'        => 'in_progress',
                'creator'       => $ujjwal,
                'assignee'      => $kumar,
                'assigner'      => $vishalS,
                'created_at'    => $now->copy()->subDays(1)->subHours(4),
                'assigned_at'   => $now->copy()->subDays(1)->subHours(2),
                'tat_hours'     => 16,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(1)->subHours(4)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(1)->subHours(2),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'expense_added', 'who' => 'assignee', 'at' => $now->copy()->subHours(5),
                        'description' => fn () => 'Expense submitted for approval: ₹8,500.00'],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(5),
                        'note' => 'Ordered replacement AP; invoice uploaded for approval.'],
                ],
                'expense' => [
                    'description' => 'HP WiFi AP (ground floor replacement)',
                    'amount'      => 8500.00,
                    'expense_date'=> $now->copy()->subHours(5)->toDateString(),
                    'status'      => 'pending',
                ],
            ],

            // 11. Pending info — Madurai (TN), assigned to Sayli
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appCat,
                'subcategory'   => $sub($appCat, 'User ID locked / inactive'),
                'branch'        => $madurai,
                'subject'       => 'User ID locked — branch credit officer',
                'description'   => 'Branch credit officer locked out after 3 failed attempts. Needs unlock + password reset.',
                'priority'      => 'medium',
                'status'        => 'pending_info',
                'creator'       => $vikrant,
                'assignee'      => $sayli,
                'assigner'      => $prashant,
                'created_at'    => $now->copy()->subHours(5),
                'assigned_at'   => $now->copy()->subHours(4),
                'tat_hours'     => 8,
                'activities' => [
                    ['action_type' => 'created',        'who' => 'creator',  'at' => $now->copy()->subHours(5)],
                    ['action_type' => 'assigned',       'who' => 'assigner', 'at' => $now->copy()->subHours(4),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'status_changed', 'who' => 'assignee', 'at' => $now->copy()->subHours(2),
                        'description' => fn () => 'Requested more information',
                        'old_value' => 'assigned', 'new_value' => 'pending_info'],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(2),
                        'status_from' => 'assigned', 'status_to' => 'pending_info',
                        'note' => 'Please confirm employee ID and the exact error text on screen.'],
                ],
            ],

            // 12. Reopened resolved app ticket — Kolhapur (showing the reopen flow)
            [
                'ticket_number' => $next(),
                'support_type'  => 'application',
                'category'      => $appLMS ?: $appCat,
                'subcategory'   => $sub($appLMS ?: $appCat, 'SMS Issue'),
                'branch'        => $kolhapur,
                'subject'       => 'EMI reminder SMS not delivered to a subset of customers',
                'description'   => 'After last weekend\'s deploy, ~10% of EMI reminder SMS are silently failing.',
                'priority'      => 'medium',
                'status'        => 'reopen',
                'creator'       => $ravindra,
                'assignee'      => $kalyani,
                'assigner'      => $prashant,
                'created_at'    => $now->copy()->subDays(4),
                'assigned_at'   => $now->copy()->subDays(4),
                'reopen_count'  => 1,
                'reopened_at'   => $now->copy()->subHours(6),
                'tat_hours'     => 12,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subDays(4)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subDays(4),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                    ['action_type' => 'reopened', 'who' => 'creator',  'at' => $now->copy()->subHours(6),
                        'description' => fn ($t) => 'Ticket reopened by ' . ($t['creator']->name ?? '—') . ' (#1)',
                        'old_value' => 'resolved', 'new_value' => 'reopen'],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subDays(3),
                        'status_from' => 'assigned', 'status_to' => 'resolved',
                        'note' => 'Telecom gateway throttling adjusted, retried failed batch successfully.'],
                    ['who' => 'creator', 'at' => $now->copy()->subHours(6),
                        'status_from' => 'resolved', 'status_to' => 'reopen',
                        'note' => 'Issue is back today — ~5% failures still observed in latest batch.'],
                ],
            ],

            // 13. Routine printer ticket — Hassan (KA), low priority, assigned to Kumar
            [
                'ticket_number' => $next(),
                'support_type'  => 'infrastructure',
                'category'      => $infraPrn ?: $infraCat,
                'subcategory'   => $sub($infraPrn ?: $infraCat, 'Network Printer Offline'),
                'branch'        => $hassan,
                'subject'       => 'Hassan branch network printer offline',
                'description'   => 'Branch printer not visible on the network. Trying to take print of sanction letter.',
                'priority'      => 'low',
                'status'        => 'in_progress',
                'creator'       => $sarveish,
                'assignee'      => $kumar,
                'assigner'      => $vishalS,
                'created_at'    => $now->copy()->subHours(7),
                'assigned_at'   => $now->copy()->subHours(6),
                'tat_hours'     => 24,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subHours(7)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subHours(6),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
                'updates' => [
                    ['who' => 'assignee', 'at' => $now->copy()->subHours(3),
                        'status_from' => 'assigned', 'status_to' => 'in_progress',
                        'note' => 'Connected remotely — looks like a static IP conflict, asking branch to power cycle.'],
                ],
            ],

            // 14. HR query — admin support — Nashik (assigned to admin, simple)
            [
                'ticket_number' => $next(),
                'support_type'  => 'admin',
                'category'      => $adminHR ?: $adminFac,
                'subcategory'   => $sub($adminHR ?: $adminFac, 'Payslip Issue / Discrepancy'),
                'branch'        => $nashik,
                'subject'       => 'Payslip discrepancy — March 2026',
                'description'   => 'Variable component in March payslip looks incorrect. Need finance to review.',
                'priority'      => 'medium',
                'status'        => 'assigned',
                'creator'       => $vivek,
                'assignee'      => $admin,
                'assigner'      => $admin,
                'created_at'    => $now->copy()->subHours(20),
                'assigned_at'   => $now->copy()->subHours(20),
                'tat_hours'     => 24,
                'activities' => [
                    ['action_type' => 'created',  'who' => 'creator',  'at' => $now->copy()->subHours(20)],
                    ['action_type' => 'assigned', 'who' => 'assigner', 'at' => $now->copy()->subHours(20),
                        'description' => fn ($t) => 'Assigned to ' . ($t['assignee']->name ?? '—')],
                ],
            ],

            // 15. Facility — Warangal (TG), low, open (escalates because TAT-2 violated soon)
            [
                'ticket_number' => $next(),
                'support_type'  => 'admin',
                'category'      => $adminFac,
                'subcategory'   => $sub($adminFac, 'AC Not Working / Temperature Issue'),
                'branch'        => $warangal,
                'subject'       => 'AC not cooling — Warangal branch front office',
                'description'   => 'Front office AC tripping repeatedly. Branch is uncomfortable, customers waiting.',
                'priority'      => 'high',
                'status'        => 'open',
                'creator'       => $hari,
                'created_at'    => $now->copy()->subHours(3),
                'tat_hours'     => 8,
                'activities' => [
                    ['action_type' => 'created', 'who' => 'creator', 'at' => $now->copy()->subHours(3)],
                ],
            ],
        ];

        foreach ($tickets as $data) {
            if (empty($data['category']) || empty($data['subcategory']) || empty($data['branch']) || empty($data['creator'])) {
                continue;
            }

            $createdAt = $data['created_at'];
            $deadline  = $createdAt->copy()->addHours((int) $data['tat_hours']);

            $ticket = Ticket::create([
                'ticket_number'        => $data['ticket_number'],
                'support_type'         => $data['support_type'],
                'category_id'          => $data['category']->id,
                'subcategory_id'       => $data['subcategory']->id,
                'branch_id'            => $data['branch']->id,
                'vendor_id'            => $data['vendor']->id ?? null,
                'vendor_reference'     => $data['vendor_reference'] ?? null,
                'subject'              => $data['subject'],
                'description'          => $data['description'],
                'priority'             => $data['priority'],
                'status'               => $data['status'],
                'is_red_flag'          => (bool) ($data['is_red_flag'] ?? false),
                'is_tat_violated'      => (bool) ($data['is_tat_violated'] ?? false),
                'created_by'           => $data['creator']->id,
                'assigned_to'          => $data['assignee']->id ?? null,
                'assigned_by'          => $data['assigner']->id ?? null,
                'employee_contact_name'        => $data['creator']->name,
                'employee_contact_phone'       => $data['creator']->phone ?? '0000000000',
                'employee_contact_email'       => $data['creator']->email,
                'employee_contact_employee_id' => $data['creator']->employee_id ?? null,
                'assigned_at'          => $data['assigned_at']    ?? null,
                'resolved_at'          => $data['resolved_at']    ?? null,
                'closed_at'            => $data['closed_at']      ?? null,
                'reopened_at'          => $data['reopened_at']    ?? null,
                'reopen_count'         => $data['reopen_count']   ?? 0,
                'hold_started_at'      => $data['hold_started_at'] ?? null,
                'hold_total_seconds'   => 0,
                'tat_hours'            => $data['tat_hours'],
                'tat_deadline'         => $deadline,
                'status_entered_at'    => $createdAt,
                'status_tat_deadline'  => $deadline,
                'created_at'           => $createdAt,
                'updated_at'           => $createdAt,
            ]);

            foreach (($data['activities'] ?? []) as $a) {
                $userRef = $data[$a['who']] ?? $data['creator'];
                $description = $a['description'] ?? null;
                if (is_callable($description)) {
                    $description = $description($data);
                } elseif ($description === null) {
                    $description = $this->defaultActivityDescription($a['action_type'], $userRef);
                }

                TicketActivity::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $userRef?->id,
                    'action_type' => $a['action_type'],
                    'description' => $description,
                    'old_value'   => $a['old_value'] ?? null,
                    'new_value'   => $a['new_value'] ?? null,
                    'created_at'  => $a['at'],
                ]);
            }

            foreach (($data['updates'] ?? []) as $u) {
                $userRef = $data[$u['who']] ?? $data['creator'];
                TicketUpdate::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $userRef?->id,
                    'status_from' => $u['status_from'] ?? null,
                    'status_to'   => $u['status_to']   ?? null,
                    'note'        => $u['note']        ?? null,
                    'created_at'  => $u['at'],
                ]);
            }

            if (!empty($data['expense'])) {
                TicketExpense::create([
                    'ticket_id'    => $ticket->id,
                    'added_by'     => ($data['assignee'] ?? $data['creator'])->id,
                    'description'  => $data['expense']['description'],
                    'amount'       => $data['expense']['amount'],
                    'expense_date' => $data['expense']['expense_date'],
                    'status'       => $data['expense']['status'],
                ]);
            }
        }

        $this->command?->info('Seeded ' . count($tickets) . ' demo tickets (cleared previous tickets first).');
    }

    private function defaultActivityDescription(string $actionType, ?User $user): string
    {
        $name = $user?->name ?? '—';
        return match ($actionType) {
            'created'  => "Ticket created by {$name}",
            'assigned' => "Assigned",
            'reopened' => "Ticket reopened by {$name}",
            'closed'   => "Ticket closed by {$name}",
            default    => ucfirst(str_replace('_', ' ', $actionType)) . " by {$name}",
        };
    }
}
