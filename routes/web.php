<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Fleet\HmEntrySyncController;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
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

    Route::get('/procurement', function () {
        $user = auth()->user();

        abort_unless(
            $user?->can('procurement.pr.create')
            || $user?->can('procurement.pr.approve')
            || $user?->can('procurement.po.manage')
            || $user?->can('procurement.do.receive'),
            403
        );

        return view('procurement.index');
    })->name('procurement.index');

    Route::get('/inventory', function () {
        return view('inventory.index', [
            'inventoryItems' => InventoryItem::query()->latest()->get(),
            'inventoryMovements' => InventoryMovement::query()->latest()->take(20)->get(),
        ]);
    })->middleware('permission:inventory.view')->name('inventory.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
