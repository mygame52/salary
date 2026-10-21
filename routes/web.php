<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('dashboard')
            : redirect()->route('my-payslips');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Admin / Finance Officer Routes
    Route::get('/dashboard', [PayrollController::class, 'dashboard'])->name('dashboard');
    Route::post('/batches/upload', [PayrollController::class, 'upload'])->name('batches.upload');
    Route::post('/batches/cancel-upload', [PayrollController::class, 'cancelUpload'])->name('batches.cancel-upload');
    Route::get('/batches/{batch}', [PayrollController::class, 'showBatch'])->name('batches.show');
    Route::put('/batches/{batch}', [PayrollController::class, 'updateBatch'])->name('batches.update');
    Route::delete('/batches/{batch}', [PayrollController::class, 'destroyBatch'])->name('batches.destroy');
    Route::get('/batches/{batch}/print-all', [PayrollController::class, 'printBatch'])->name('batches.print');

    // Employee Management (fill in 13-digit citizen ID, position, etc.)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::put('/employees/bulk-update', [EmployeeController::class, 'bulkUpdateCitizenIds'])->name('employees.bulk-update');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

    // Settings (Agency header, Logo, Signer name & signature)
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Payslip View & Print (accessible by Admin and the Payslip's owner)
    Route::get('/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('my-payslips');
});
