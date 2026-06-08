<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\InstallHealthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.installed')->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::get('/install/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/install/database', [InstallController::class, 'storeDatabase'])->name('install.database.store');
    Route::get('/install/site', [InstallController::class, 'site'])->name('install.site');
    Route::post('/install/site', [InstallController::class, 'storeSite'])->name('install.site.store');
    Route::get('/install/admin', [InstallController::class, 'admin'])->name('install.admin');
    Route::post('/install/admin', [InstallController::class, 'storeAdmin'])->name('install.admin.store');
    Route::get('/install/review', [InstallController::class, 'review'])->name('install.review');
    Route::post('/install/review', [InstallController::class, 'finish'])->name('install.finish');
    Route::get('/install/health', InstallHealthController::class)->name('install.health');
});

Route::middleware('installed')->group(function () {
    Route::get('/install/complete', [InstallController::class, 'complete'])->name('install.complete');
});
