<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Fleet\HmEntrySyncController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'permission:dashboard.view'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/fleet/hm-entry', function () {
        return view('fleet.hm-entry');
    })->middleware('permission:fleet.hm.input')->name('fleet.hm-entry');
    Route::post('/fleet/hm-entry/sync', HmEntrySyncController::class)
        ->middleware('permission:fleet.hm.input')
        ->name('fleet.hm-entry.sync');

    Route::get('/admin/users-roles', function () {
        return view('admin.user-role-management');
    })->middleware('permission:rbac.manage')->name('admin.users-roles');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
