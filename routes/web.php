<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TransferController::class, 'home'])->name('home');

Route::post('/transfers', [TransferController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('transfers.store');

Route::post('/uploads/{file}/chunks', [TransferController::class, 'uploadChunk'])
    ->middleware('throttle:120,1')
    ->name('uploads.chunks');

Route::get('/transfers/{token}/status', [TransferController::class, 'status'])
    ->name('transfers.status');

Route::get('/t/{token}', [TransferController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('transfers.show');

Route::post('/t/{token}/unlock', [TransferController::class, 'unlock'])
    ->middleware('throttle:10,1')
    ->name('transfers.unlock');

Route::get('/t/{token}/files/{file}', [TransferController::class, 'downloadFile'])
    ->middleware('throttle:30,1')
    ->name('transfers.files.download');

Route::get('/t/{token}/download.zip', [TransferController::class, 'downloadZip'])
    ->middleware('throttle:10,1')
    ->name('transfers.zip');

Route::get('/health', [HealthController::class, 'live'])->name('health.live');
Route::get('/ready', [HealthController::class, 'ready'])->name('health.ready');
