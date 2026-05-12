<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\ExpenseApprovalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\TATController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\WorkingHourController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Developers land on their app launcher (CTS / ATS / Dialer); everyone
    // else goes straight to the ticketing dashboard.
    return auth()->user()?->isDeveloper()
        ? redirect()->route('developer.home')
        : redirect()->route('dashboard');
})->middleware('auth');

Route::middleware(['auth','verified'])->group(function () {

    // Developer area: an app launcher plus the modules being incubated under
    // the developer role — ATS (asset management) and the Dialer (Smartping
    // cloud telephony). Only role=developer reaches these. Developers can also
    // use the main ticketing app ("CTS" on the launcher); those routes keep
    // their own role gates, so a developer sees CTS at employee level.
    Route::middleware('role:developer')->prefix('developer')->name('developer.')->group(function () {
        Route::get('/',       [DeveloperController::class, 'home'])->name('home');
        Route::get('/assets', [DeveloperController::class, 'assets'])->name('assets');

        // --- Dialer module ---
        Route::prefix('dialer')->name('dialer.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Developer\DialerController::class, 'index'])->name('home');
            Route::post('/call', [\App\Http\Controllers\Developer\DialerController::class, 'call'])->name('call');
            Route::post('/call/{dialerTicket}/hangup', [\App\Http\Controllers\Developer\DialerController::class, 'hangup'])->name('hangup');

            // Customer database (manual add + CSV import with dedup)
            Route::get('/customers',        [\App\Http\Controllers\Developer\DialerCustomerController::class, 'index'])->name('customers.index');
            Route::post('/customers',       [\App\Http\Controllers\Developer\DialerCustomerController::class, 'store'])->name('customers.store');
            Route::patch('/customers/{dialerCustomer}', [\App\Http\Controllers\Developer\DialerCustomerController::class, 'update'])->name('customers.update');
            Route::get('/customers/import', [\App\Http\Controllers\Developer\DialerCustomerController::class, 'importForm'])->name('customers.import');
            Route::post('/customers/import',[\App\Http\Controllers\Developer\DialerCustomerController::class, 'import'])->name('customers.import.store');

            // Dialer tickets + call trail
            Route::get('/tickets',                   [\App\Http\Controllers\Developer\DialerTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{dialerTicket}',    [\App\Http\Controllers\Developer\DialerTicketController::class, 'show'])->name('tickets.show');
            Route::patch('/tickets/{dialerTicket}/notes', [\App\Http\Controllers\Developer\DialerTicketController::class, 'updateNotes'])->name('tickets.notes');
        });
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::get('/tickets/export', [TicketController::class, 'export'])->name('tickets.export');
    Route::get('/tickets/{ticket}/pdf', [TicketController::class, 'exportPdf'])->name('tickets.pdf');
    Route::resource('tickets', TicketController::class)->only(['index','create','store','show']);
    Route::post('/tickets/{ticket}/update',    [TicketController::class, 'addUpdate'])->name('tickets.update');
    Route::post('/tickets/{ticket}/reopen',    [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::post('/tickets/{ticket}/close',     [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/assign',    [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/expense',   [TicketController::class, 'addExpense'])->name('tickets.expense');
    Route::post('/tickets/{ticket}/attachment',[TicketController::class, 'addAttachment'])->name('tickets.attachment');
    Route::post('/tickets/{ticket}/red-flag',  [TicketController::class, 'toggleRedFlag'])->name('tickets.redflag');
    Route::patch('/tickets/{ticket}/vendor-reference', [TicketController::class, 'setVendorReference'])->name('tickets.vendorRef');

    // Team
    Route::middleware('role:resolver,admin,ciso')->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/{user}/tickets', [TeamController::class, 'memberTickets'])->name('team.member');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Projects (Admin + CISO only — gated further by ProjectPolicy)
    Route::middleware('role:resolver,admin,ciso')->group(function () {
        Route::resource('projects', ProjectController::class);
    });

    // Expense approval (CISO / Admin / Management — page only shows
    // expenses routed to the current user; per-row checks enforced inside).
    Route::middleware('role:resolver,admin,management')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/approvals', [ExpenseApprovalController::class, 'index'])->name('approvals');
        Route::post('/{expense}/approve', [ExpenseApprovalController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject',  [ExpenseApprovalController::class, 'reject'])->name('reject');
    });

    // Reports
    Route::middleware('role:resolver,admin,ciso')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/priority', [ReportController::class, 'priorityReport'])->name('reports.priority');
        Route::get('/tat', [ReportController::class, 'tatReport'])->name('reports.tat');
        Route::get('/expenses', [ReportController::class, 'expenseReport'])->name('reports.expenses');
        Route::get('/team-performance', [ReportController::class, 'teamReport'])->name('reports.team');
        Route::get('/aging', [ReportController::class, 'agingReport'])->name('reports.aging');
    });

    // Admin (admin role + CISO — CISO gets the full admin area)
    Route::middleware('role:admin,ciso')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('regions', RegionController::class)->except(['show']);
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::resource('vendors', VendorController::class)->except(['show']);
        Route::delete('vendors/{vendor}/attachments/{attachment}', [VendorController::class, 'destroyAttachment'])->name('vendors.attachments.destroy');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('subcategories', SubcategoryController::class)->except(['show']);
        Route::get('/tat', [TATController::class, 'index'])->name('tat.index');
        Route::patch('/tat/{config}', [TATController::class, 'update'])->name('tat.update');
        Route::get('/working-hours', [WorkingHourController::class, 'index'])->name('working-hours.index');
        Route::patch('/working-hours', [WorkingHourController::class, 'update'])->name('working-hours.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';
