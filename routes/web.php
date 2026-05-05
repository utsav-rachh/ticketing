<?php

use App\Http\Controllers\DashboardController;
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

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth','verified'])->group(function () {

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
    Route::middleware('role:resolver,admin')->group(function () {
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/{user}/tickets', [TeamController::class, 'memberTickets'])->name('team.member');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Projects (Admin + IT Head only — gated further by ProjectPolicy)
    Route::middleware('role:resolver,admin')->group(function () {
        Route::resource('projects', ProjectController::class);
    });

    // Expense approval (IT Head / Admin / Management — page only shows
    // expenses routed to the current user; per-row checks enforced inside).
    Route::middleware('role:resolver,admin,management')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/approvals', [ExpenseApprovalController::class, 'index'])->name('approvals');
        Route::post('/{expense}/approve', [ExpenseApprovalController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject',  [ExpenseApprovalController::class, 'reject'])->name('reject');
    });

    // Reports
    Route::middleware('role:resolver,admin')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/priority', [ReportController::class, 'priorityReport'])->name('reports.priority');
        Route::get('/tat', [ReportController::class, 'tatReport'])->name('reports.tat');
        Route::get('/expenses', [ReportController::class, 'expenseReport'])->name('reports.expenses');
        Route::get('/team-performance', [ReportController::class, 'teamReport'])->name('reports.team');
        Route::get('/aging', [ReportController::class, 'agingReport'])->name('reports.aging');
    });

    // Admin (admin role only)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
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
