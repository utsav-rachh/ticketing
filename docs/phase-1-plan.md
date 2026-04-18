# Ticketing System — Overhaul Plan (Phase 1 + Phase 2 split)

## Context

The current Laravel 13 ticketing app (`c:\xampp\htdocs\ticketing`) is functional but was built around a flat 2-role model (employee / resolver) with manual priority selection, no branch hierarchy, no resolver sub-levels, no sidebar collapse, no Excel export wired up, and no TAT breach scheduling. After field/office review, a large set of business requirements was surfaced: branch-aware visibility, a split of admin vs. resolver duties, a resolver hierarchy with auto-escalation, fixed (non-user-editable) priority with MD red-flagging, a procurement "Hold" status that pauses TAT, a merged activity-timeline/status-update UX, employee-side attachments & notes, auto-assignment by region, expense approval (IT infra only, with IT Head approval), audit logs, collapsible sidebar with branding, and better reports. This plan delivers the essentials now (Phase 1) and defers genuinely deep or ML-heavy work to Phase 2, taking Zoho Desk as the "simple-but-solid" reference — we are *not* replicating Zoho, just using it as a feature sanity check.

**User clarifications captured:**
- Hierarchy: **Branch → Regional → Head (3 levels)**
- Resolver sub-hierarchy: **Junior → TL → IT Head**
- Admin is a **separate role** (not the IT Head)
- **Application Support tickets have NO expenses**; only IT Infrastructure & Admin categories can log expenses
- **Expense approval**: IT Head approves (Junior submits → IT Head approves/rejects)
- **Category auto-suggest** → **deferred to Phase 2**
- **MD/Management** identified by `is_management` flag on user; their tickets auto-set to `critical` + red-flagged
- Admin needs full CRUD over categories & subcategories
- Every category exposes an **"Others"** subcategory where the user types a free-text issue

---

## Phase 1 — Scope (build now)

### 1. Data model changes (migrations)

New tables:
- `regions` — id, name, code, timestamps
- `branches` — id, region_id (FK), name, code, address, is_active, timestamps
- `vendors` — id, name, contact_person, phone, email, is_active, timestamps *(lookup for vendor ticket details)*
- `ticket_updates` — id, ticket_id, user_id, status_from, status_to (nullable), note (nullable), created_at *(unified timeline + status, replaces splitting note/status into two concepts; keeps `ticket_activities` for system events)*
- `audit_logs` — id, user_id, auditable_type, auditable_id, action, changes (json), ip, user_agent, created_at *(admin-visible audit trail)*

Column additions:
- `users`: add `branch_id` (FK nullable), `region_id` (FK nullable), `employee_id` (string), `is_management` (bool, default false), `resolver_level` (enum: junior/tl/it_head, nullable), `assigned_region_id` (FK nullable, used for auto-assignment routing), `assigned_support_type` (enum: application/infrastructure/admin, nullable)
- `users.role` enum: extend to `employee | resolver | admin` (migration changes enum signature)
- `tickets`: add `branch_id` (FK), `vendor_id` (FK nullable), `is_red_flag` (bool, default false), `hold_started_at` (timestamp nullable), `hold_total_seconds` (int, default 0) *(running counter of paused TAT)*, `employee_contact_name`, `employee_contact_phone`, `employee_contact_email` *(optional "on behalf" fields — normally auto-filled from creator but editable)*
- `tickets.status` enum: add `hold` (infrastructure only — enforced in validator, not DB)
- `ticket_expenses`: add `status` enum(pending/approved/rejected, default pending), `approved_by` (FK users), `approved_at`, `rejection_reason` (nullable), `invoice_path` *(rename/repurpose existing `receipt_path` if present)*

### 2. Models & policies

- New: `Region`, `Branch`, `Vendor`, `TicketUpdate`, `AuditLog`
- `User`: add relationships (`branch`, `region`, `subordinates` stays, `assignedRegion`); helpers `isAdmin()`, `isResolver()`, `isJunior()`, `isTL()`, `isITHead()`, `isManagement()`, `visibleBranchIds()`
- `Ticket`: add `branch`, `region` (via branch), `vendor`, `updates` relations; scope `visibleTo($user)` rewritten:
  - admin/it_head: all tickets
  - tl: tickets assigned to their team OR within their support_type
  - junior: only tickets assigned to self
  - employee: tickets they created OR raised from any user in branches they can see (branch → regional → head hierarchy via their own `branch_id`/`region_id`)
  - management: their own tickets only
- `TicketPolicy`: extend with `hold()`, `approveExpense()`, `manageUsers()`, `manageCategories()`, `viewAuditLog()`
- New `RegionPolicy`, `BranchPolicy` — admin-only CRUD

### 3. Priority is now fixed (derived, not user-picked)

- Remove priority selector from ticket wizard (Step 3 in [resources/views/tickets/create.blade.php](resources/views/tickets/create.blade.php))
- Priority is **derived server-side**:
  1. If creator has `is_management = true` → `critical` + `is_red_flag = true`
  2. Else use `subcategory.default_priority` (already seeded)
  3. Admin can override priority on an existing ticket (audited); regular users cannot
- `TicketController@store`: drop `priority` from validated input; compute from the rules above

### 4. Ticket creation wizard changes

[resources/views/tickets/create.blade.php](resources/views/tickets/create.blade.php):
- **Step 1 (Support Type):** keep — Application / Infrastructure / Admin
- **Step 2 (Category):** keep
- **Step 3 (Issue Type):** remove priority selector; every category gets an **"Others"** subcategory (seeded) — when chosen, reveal a required `custom_issue` textarea
- **Step 4 (Details):** show the derived priority as a read-only chip; add:
  - Branch (auto-selected from the creator's `branch_id`, editable only by resolver/admin raising on behalf)
  - On-behalf-of section (optional): employee_id, name, phone, email — prefilled from creator but editable
  - **Vendor details (optional, Infrastructure only):** vendor dropdown (from `vendors` table) + free-text reference
  - Attachments: multi-file input (images/pdf, 10 MB each) — wire into existing `ticket_attachments`
  - Subject (required), Description (required when subcategory is "Others")

### 5. Hold status (Infrastructure only) with TAT pause

- Add `hold` to allowed status transitions for support_type = `infrastructure` only
- When transitioning → `hold`: set `hold_started_at = now()`
- When transitioning out of `hold`: `hold_total_seconds += now() - hold_started_at`, clear `hold_started_at`
- `Ticket::isOverdue()` and `tatProgress()` rewritten to use `effectiveElapsedSeconds = (now - created_at) - hold_total_seconds - (currently_on_hold ? now - hold_started_at : 0)`
- UI: show "On Hold since {time}" banner on ticket detail; hide TAT countdown while on hold

### 6. Merged activity timeline + status update form

On [resources/views/tickets/show.blade.php](resources/views/tickets/show.blade.php):
- Replace separate "Update Status" and "Add Note" forms with **one combined form**:
  - Status dropdown (optional — blank = no status change)
  - Note textarea (optional)
  - Attachment input (optional, employees can also attach)
  - Submit is enabled if *either* status or note is filled
- New `TicketController@addUpdate` handler writes to `ticket_updates` (single row carrying status transition + note). `ticket_activities` still records system events (assignment, expense approved, etc.) but user-originated comments/status changes go through `ticket_updates` so the timeline is one clean chronological list.
- Timeline view renders both `updates` and system `activities` interleaved by timestamp.
- **Employee permissions on their own ticket:** can add notes + attachments + see status updates, but cannot change status or assignment.

### 7. Role & hierarchy changes

- New role: `admin` (full CRUD on users, categories, subcategories, regions, branches, vendors, TAT configs; can view audit logs; cannot by default be assigned tickets)
- Resolver gains `resolver_level` (junior / tl / it_head)
- Route middleware extended: `role:admin`, `role:resolver`, and a combined `role:admin,resolver`
- Admin routes split under `/admin/*` — users, categories, subcategories, regions, branches, vendors, tat-config, audit-logs
- Resolver-only routes: `/tickets`, `/team`, `/reports`, `/expenses/approvals` (IT Head only)

### 8. Auto-assignment & escalation

`app/Services/TicketAssignmentService.php` (new):
- On ticket creation, find candidate resolvers where `resolver_level = 'junior'` AND `assigned_support_type = ticket.support_type` AND `assigned_region_id = ticket.branch.region_id`
- If multiple, pick one by simplest round-robin (least open tickets)
- If none match (fallback), assign to the TL for that support_type
- Write `ticket_activities` row for the assignment

Escalation (`app/Console/Commands/CheckTATEscalation.php`, scheduled every 15 min in `routes/console.php`):
- When ticket past `warning_threshold` (75%/80% of TAT, already in `tat_configurations`) and not resolved → notify assignee's TL
- When past 100% (TAT violated) and not resolved → notify IT Head + set `is_tat_violated = true`
- Uses existing `TATBreachedNotification` (now actually fired — today the job exists but is never scheduled)

### 9. Notifications — turn on

- Wire `TATBreachedNotification` into the scheduled command above
- Extend `TicketAssignedNotification` recipients to include the TL (cc)
- New `ManagementTicketNotification` — fires to IT Head + TL immediately when `is_management = true` creator raises a ticket
- New `ExpenseSubmittedNotification` → IT Head; `ExpenseDecisionNotification` → submitting junior
- Keep existing `TicketStatusNotification`; also include hold transitions
- Frontend: keep the bell icon; add unread count polling every 60s (simple `setInterval` fetch to a JSON endpoint — no Livewire required)

### 10. Expenses (Infrastructure + Admin only, approval workflow)

- Hide the "Add Expense" UI on Application Support tickets (server-side enforced in `TicketController@addExpense`)
- Expense form requires: amount, description, date, **invoice attachment** (required)
- On submit: `status = pending`, notify IT Head
- New page: `/expenses/approvals` (IT Head only) — list pending expenses; approve/reject with optional reason
- `ExpenseController@approve` / `@reject` — audits in `audit_logs`; notifies submitter
- Reports include only **approved** expenses in totals (pending shown separately)

### 11. Management red-flag system

- Ticket show page: if `is_red_flag`, show red banner + red border on list rows
- Dashboard: dedicated "Red-flagged tickets" card (count + link to filtered list)
- On creation by `is_management` user: auto set `is_red_flag = true`, `priority = 'critical'`
- Admin can also manually flag/unflag a ticket (audited)

### 12. Dashboard rework

[app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php) + [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php):
- Fix the existing complaint: "resolved/closed/in-progress don't update" — current stats use `Ticket::visibleTo()` scope; ensure counts query the same scope and re-run on every dashboard visit (no caching yet).
- Cards: Total, Open, Assigned, In-Progress, **On Hold** (new), Resolved, Closed, TAT Violated, **Red-Flagged** (new)
- Role-aware widgets:
  - employee: their branch's counts; recent own tickets
  - junior/tl: their queue with aging (created_at → now, colored by TAT %)
  - it_head/admin: company-wide with region breakdown + pending expense approvals count
- Simple bar chart (tickets by support_type, last 30 days) using a tiny inline SVG or Chart.js CDN — keep it minimal

### 13. Collapsible sidebar + branding

[resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php):
- Sidebar state stored in `localStorage` + a small Alpine.js snippet (Alpine already ships with Breeze assets)
- Default state: **collapsed** (icon-only rail), as the user requested
- Expanded state: icon + label
- Collapse/expand toggle (hamburger) pinned at the **bottom** of the sidebar
- When expanded, bottom of sidebar shows:
  - `Altum Credo Finance Private Limited` (pull from `config('app.company_name')`)
  - `Developed by 5P Media` (ensure visible — today present at line ~62 but overlooked in collapsed layout)
- When collapsed, those lines become tooltips on hover of a small info icon

### 14. Admin: category CRUD + Others enforcement + audit logs

- New admin pages: Regions, Branches, Vendors, Categories, Subcategories, Users (extend existing user form with branch/region/resolver_level/is_management/assigned_region)
- Categories page lets admin add/edit/disable/reorder; subcategories CRUD inside each category
- **Seeder + model observer** ensures every category has at least one subcategory named `Others`. On category create, auto-create the "Others" row
- Audit-log page `/admin/audit-logs` — filters by user, date, model, action; read-only
- An `Auditable` trait attached to `User`, `Ticket`, `Category`, `Subcategory`, `TatConfiguration`, `TicketExpense` that writes to `audit_logs` on save/update/delete

### 15. Excel export (extensive columns)

Currently `TicketController@export` returns 404 and `maatwebsite/excel` is already installed.
- New `App\Exports\TicketsExport` implementing `FromQuery`, `WithHeadings`, `WithMapping`, `Exportable`
- Columns: ticket_number, support_type, category, subcategory, custom_issue, subject, description, priority, status, is_red_flag, is_tat_violated, branch, region, creator_name, creator_employee_id, creator_phone, creator_email, assignee_name, assignee_level, assigned_at, hold_total_hours, tat_hours, tat_deadline, created_at, resolved_at, closed_at, vendor_name, total_expense_approved, total_expense_pending, last_update_note
- Filters: date range, status, support_type, region, assignee — same filters as the tickets index
- Button on `/tickets` only for resolver+admin (via `User::canExport()` — already exists)

### 16. Reports improvements (light — deeper work is Phase 2)

- Keep the 5 existing reports, but:
  - Add region filter everywhere
  - Add "expenses by status" (approved/pending/rejected) to the expense report
  - Show aging buckets (< 1 day, 1-3 days, 3-7 days, > 7 days) in team performance
- Heavier visual overhaul (charts library, downloadable PDF reports) → Phase 2

### 17. Seeders

- Update `UserSeeder`: create 1 admin, 1 IT Head, 1 TL (application), 1 TL (infrastructure), 2 juniors, 1 management user, existing employee + resolver preserved
- New `RegionSeeder`, `BranchSeeder` (4 regions, ~2 branches each to match "four states" described)
- New `VendorSeeder` (2–3 sample vendors)
- Extend `SubcategorySeeder` to add "Others" under every category
- Keep idempotent `updateOrCreate` pattern already established (commit 71b9e6c)

---

## Critical files to modify / create

**Migrations (new):** `create_regions_table`, `create_branches_table`, `create_vendors_table`, `create_ticket_updates_table`, `create_audit_logs_table`, `alter_users_add_hierarchy_fields`, `alter_users_extend_role_enum`, `alter_tickets_add_branch_vendor_redflag_hold`, `alter_tickets_extend_status_enum`, `alter_ticket_expenses_add_approval`

**Models (new):** `app/Models/Region.php`, `Branch.php`, `Vendor.php`, `TicketUpdate.php`, `AuditLog.php`
**Models (modify):** [app/Models/User.php](app/Models/User.php), [app/Models/Ticket.php](app/Models/Ticket.php), [app/Models/TicketExpense.php](app/Models/TicketExpense.php)

**Controllers (new):** `app/Http/Controllers/Admin/RegionController.php`, `BranchController.php`, `VendorController.php`, `SubcategoryController.php`, `AuditLogController.php`, `app/Http/Controllers/ExpenseApprovalController.php`
**Controllers (modify):** [app/Http/Controllers/TicketController.php](app/Http/Controllers/TicketController.php), [DashboardController.php](app/Http/Controllers/DashboardController.php), existing `Admin/UserController.php`, `Admin/CategoryController.php`

**Services (new):** `app/Services/TicketAssignmentService.php`, `app/Services/TatCalculator.php` (hold-aware elapsed-time logic)

**Commands (new):** `app/Console/Commands/CheckTATEscalation.php`; register in [routes/console.php](routes/console.php) (every 15 min)

**Notifications (new):** `ManagementTicketNotification`, `ExpenseSubmittedNotification`, `ExpenseDecisionNotification`; existing `TATBreachedNotification` + `TicketAssignedNotification` extended

**Policies:** extend [app/Policies/TicketPolicy.php](app/Policies/TicketPolicy.php); new `RegionPolicy`, `BranchPolicy`, `VendorPolicy`, `ExpensePolicy`, `AuditLogPolicy`

**Views (new):** `resources/views/admin/regions/*`, `branches/*`, `vendors/*`, `subcategories/*`, `audit-logs/*`; `resources/views/expenses/approvals.blade.php`
**Views (modify):** [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php) (collapse + footer), [dashboard.blade.php](resources/views/dashboard.blade.php), [tickets/create.blade.php](resources/views/tickets/create.blade.php), [tickets/show.blade.php](resources/views/tickets/show.blade.php), [tickets/index.blade.php](resources/views/tickets/index.blade.php)

**Exports (new):** `app/Exports/TicketsExport.php`

**Traits (new):** `app/Traits/Auditable.php`

**Seeders (new/modify):** `RegionSeeder`, `BranchSeeder`, `VendorSeeder`; update [UserSeeder](database/seeders/UserSeeder.php), [SubcategorySeeder](database/seeders/SubcategorySeeder.php)

**Existing functions to reuse:**
- `Ticket::visibleTo()` scope — extend, don't rewrite blindly
- `TatConfiguration::forPriority()` — keep; feed into new hold-aware calculator
- `User::canAssign()`, `canExport()` — extend for new roles
- `Ticket::generateTicketNumber()` — keep
- Existing `TicketActivity` logging — keep for system events; `ticket_updates` is additional for user comments + status

---

## Phase 2 — Deferred (do NOT build yet)

These were discussed but are explicitly out of scope for Phase 1 per the user's "simple app, don't give everything" instruction:

1. **Category auto-suggestion from typed issue text** — requires either a keyword engine with ongoing tuning or an LLM integration (cost/key/latency). Phase 2 delivers an intelligent suggester using either a trained classifier or a small LLM call, with a "Did you mean...?" UI on the Step 2/3 wizard.
2. **Advanced reports** — chart-heavy dashboards, scheduled email reports, PDF downloads with branded templates, drilldowns, SLA-compliance analytics.
3. **Sales / field-staff hierarchy** — the "sales person in the field" hierarchy the user mentioned is a separate vertical; IT ticketing only touches it via `branch/region`. A dedicated sales hierarchy module (team leads, territory mapping, performance) is Phase 2.
4. **Vendor management module** — Phase 1 has a vendor lookup on tickets. Phase 2 adds full vendor lifecycle (PO tracking, SLA per vendor, vendor performance reports).
5. **Mobile-friendly / PWA polish** and push notifications (today only email + in-app DB notifications).
6. **Knowledge base / canned responses** — Zoho Desk-style self-help for repetitive issues.
7. **Ticket merging / splitting / linking (parent-child tickets).**
8. **CSAT (customer satisfaction) survey** after ticket close.
9. **SLA policies per region / per priority overrides** (currently global per-priority TAT).
10. **Bulk actions** on ticket list (bulk assign, bulk close).

---

## Verification

After Phase 1 implementation:

1. **Migrations & seeders:** `php artisan migrate:fresh --seed` runs clean; log in as each role (employee, junior, tl, it_head, admin, management) — all credentials listed in updated seeder.
2. **Ticket creation:** as an employee tied to a branch, create a ticket: no priority selector visible; priority correct per subcategory; "Others" subcategory requires custom issue. As a management user, ticket is auto-critical and red-flagged.
3. **Auto-assignment:** ticket from Region A / Application → goes to the Region-A Application junior; verify via ticket detail + notification.
4. **Hold flow (infrastructure):** move ticket to `hold`, wait, move back — `hold_total_seconds` increments; TAT countdown pauses on the UI; `isOverdue()` accounts for the pause.
5. **Expense flow:** on an application ticket → add-expense UI hidden. On infrastructure ticket: junior submits with invoice → IT Head gets notification → approves → submitter gets notification → approved amount reflects in reports.
6. **Escalation:** set a test ticket's TAT to 1 minute via `TatConfiguration`, let scheduler fire (or run `php artisan tickets:check-escalation` manually) → TL gets warning notification, then IT Head gets violation notification after threshold crossed.
7. **Sidebar:** verify default collapsed, hamburger at bottom, company name + "Developed by 5P Media" visible when expanded.
8. **Merged update form:** post only a note → timeline entry recorded with no status change; post only a status → same; post both → single entry with both fields.
9. **Employee interaction:** as an employee on own ticket, add a note + attachment; as resolver, see them in the timeline.
10. **Excel export:** resolver on `/tickets` → Export → downloads `.xlsx` with all extensive columns + filters respected.
11. **Audit logs:** admin visits `/admin/audit-logs` → sees rows for category edits, user edits, ticket priority overrides, expense approvals.
12. **Role separation:** log in as resolver → `/admin/*` returns 403; log in as admin → no `/tickets` assignment queue but full admin access.
13. **Dashboard:** change a ticket's status through the lifecycle (open → assigned → in_progress → hold → in_progress → resolved → closed) — every card count updates on refresh.
14. **Red flag:** management-created ticket shows red banner + appears in dashboard "Red-flagged" card; admin-toggled flag persists and appears in audit log.

End-to-end smoke suite should exercise each of the above on a fresh seeded DB before the phase is called done.
