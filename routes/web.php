<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserManagementController;
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
    Route::get('/batches/{batch}/export-csv', [PayrollController::class, 'exportBatchCsv'])->name('batches.export-csv');

    // Annual Financial Reports & Dashboard
    Route::get('/reports/annual', [PayrollController::class, 'annualReport'])->name('reports.annual');
    Route::get('/reports/annual/export-csv', [PayrollController::class, 'exportAnnualReportCsv'])->name('reports.annual.export-csv');

    // Employee Management (fill in 13-digit citizen ID, position, etc.)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/bulk-update', [EmployeeController::class, 'bulkUpdateCitizenIds'])->name('employees.bulk-update');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

    // Settings (Agency header, Logo, Signer name & signature)
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');

    // User & Role Management + Activity Logs
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/activity-logs', [UserManagementController::class, 'activityLogs'])->name('activity-logs.index');

    // Payslip View, Edit & Print (accessible by Admin and the Payslip's owner)
    Route::get('/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/payslips/{payslip}/edit', [PayrollController::class, 'editPayslip'])->name('payslips.edit');
    Route::put('/payslips/{payslip}', [PayrollController::class, 'updatePayslip'])->name('payslips.update');
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('my-payslips');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});
