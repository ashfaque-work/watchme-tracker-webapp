<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('shift')->middleware(['auth:sanctum', 'role:super-admin|admin|hr'])->group(function () {
        Route::get('/create', [ShiftController::class, 'create'])->name('shift.create');
        Route::post('/create', [ShiftController::class, 'store'])->name('shift.store');
        Route::get('/edit', [ShiftController::class, 'edit']);
        Route::delete('/delete', [ShiftController::class, 'delete']);

        Route::post('/update-user-shift', [ShiftController::class, 'updateUserShift'])->name('shift.updateUserShift');
    });

    Route::prefix('activity')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('activity');
        Route::put('/update-entries', [ActivityController::class, 'updateEntries'])->name('updateEntries');
        Route::put('/update-manual-entries', [ActivityController::class, 'updateManualEntries'])->name('updateManualEntries');
        Route::put('/update-break-entries', [ActivityController::class, 'updateBreakEnteries'])->name('updateBreakEnteries');
    });

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:super-admin|admin|hr'])->group(function () {
        Route::get('/get-user-log', [AdminController::class,'getUserLog'])->name('admin.getUserLog');
        Route::get('users', [AdminController::class, 'userList'])->name('admin.user-list');
        Route::put('assign-manager-to-user', [AdminController::class, 'assignManagerToUser'])->name('admin.assign-manager');
        Route::put('assign-role-to-user', [AdminController::class, 'assignRole'])->name('admin.assign-role');
        Route::post('create-user', [AdminController::class, 'createUser'])->name('admin.create-user');
        Route::post('/updateUserStatus', [AdminController::class, 'updateUserStatus'])->name('admin.updateUserStatus');
        Route::put('/update-user-password', [AdminController::class, 'updateUserPassword'])->name('admin.updateUserPassword');
        Route::get('/manual-entries', [AdminController::class, 'manualEntries'])->name('admin.manualEntries');
        Route::get('/update-total-time', [AdminController::class, 'updateTotalTime'])->name('admin.updateTotalTime');


        Route::get('/timesheet', [TimesheetController::class, 'index'])->name('admin.timesheet');
        Route::get('/timesheet/export', [TimesheetController::class, 'export'])->name('admin.timesheet.export');

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
        Route::patch('/settings/update-hr-permission', [AdminSettingsController::class, 'updateHrPermission'])->name('admin.updateHrPermission');
    });

    Route::prefix('manager')->middleware(['auth:sanctum', 'role:manager'])->group(function () {
        Route::get('get-user-log', [AdminController::class, 'getUserLog'])->name('manager.getUserLog');
        Route::get('users', [AdminController::class, 'userListForManager'])->name('manager.user-list');
    });
});

require __DIR__ . '/auth.php';
