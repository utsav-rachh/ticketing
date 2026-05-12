<?php

use App\Http\Controllers\Api\SmartpingWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
| Stateless endpoints (no session / CSRF). Currently only the Smartping
| dialer webhooks live here. Each handler must return 200 immediately and
| push the real work onto the queue — see SmartpingWebhookController.
*/

Route::prefix('smartping')->name('api.smartping.')->middleware('verify.smartping')->group(function () {
    Route::post('/call-status', [SmartpingWebhookController::class, 'callStatus'])->name('call-status');
    Route::post('/recording',   [SmartpingWebhookController::class, 'recording'])->name('recording');
    Route::post('/incoming',    [SmartpingWebhookController::class, 'incoming'])->name('incoming');
    Route::post('/missed',      [SmartpingWebhookController::class, 'missed'])->name('missed');
});
