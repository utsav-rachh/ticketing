<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TATController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth','verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::get('/tickets/export/{format}', [TicketController::class, 'export'])->name('tickets.export');
    Route::resource('tickets', TicketController::class)->only(['index','create','store','show']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/assign',  [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/activity',[TicketController::class, 'addActivity'])->name('tickets.activity');
    Route::post('/tickets/{ticket}/expense', [TicketController::class, 'addExpense'])->name('tickets.expense');
    Route::post('/tickets/{ticket}/attachment',[TicketController::class,'addAttachment'])->name('tickets.attachment');

    // Team
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::get('/team/{user}/tickets', [TeamController::class, 'memberTickets'])->name('team.member');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Reports
    Route::middleware('role:resolver')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/priority', [ReportController::class, 'priorityReport'])->name('reports.priority');
        Route::get('/tat', [ReportController::class, 'tatReport'])->name('reports.tat');
        Route::get('/expenses', [ReportController::class, 'expenseReport'])->name('reports.expenses');
        Route::get('/team-performance', [ReportController::class, 'teamReport'])->name('reports.team');
    });

    // Admin
    Route::middleware('role:resolver')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('/tat', [TATController::class, 'index'])->name('tat.index');
        Route::patch('/tat/{config}', [TATController::class, 'update'])->name('tat.update');
    });
});

require __DIR__.'/auth.php';
