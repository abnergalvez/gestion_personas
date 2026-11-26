<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractDocumentTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FamilyDependentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MedicalLeaveController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\PermitTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemErrorController;
use App\Http\Controllers\TrainingCourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');
    Route::get('archivos', [FileController::class, 'show'])->name('files.show');

    Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('perfil/contrasena', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('sections', SectionController::class)->except(['show']);
    Route::get('training-courses/export', [TrainingCourseController::class, 'export'])->name('training-courses.export');
    Route::resource('training-courses', TrainingCourseController::class)->except(['show']);
    Route::get('medical-leaves/export', [MedicalLeaveController::class, 'export'])->name('medical-leaves.export');
    Route::resource('medical-leaves', MedicalLeaveController::class)->except(['show']);
    Route::get('family-dependents/export', [FamilyDependentController::class, 'export'])->name('family-dependents.export');
    Route::resource('family-dependents', FamilyDependentController::class)->except(['show']);
    Route::get('contracts/export', [ContractController::class, 'export'])->name('contracts.export');
    Route::resource('contracts', ContractController::class);
    Route::post('contracts/{contract}/documents', [ContractController::class, 'storeDocument'])->name('contracts.documents.store');
    Route::delete('contracts/{contract}/documents/{document}', [ContractController::class, 'destroyDocument'])->name('contracts.documents.destroy');
    Route::get('permits/export', [PermitController::class, 'export'])->name('permits.export');
    Route::resource('permits', PermitController::class)->except(['show']);
    Route::get('evaluations/export', [EvaluationController::class, 'export'])->name('evaluations.export');
    Route::resource('evaluations', EvaluationController::class)->except(['show']);
    Route::middleware('superadmin')->group(function () {
        Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
        Route::resource('permit-types', PermitTypeController::class)->except(['show']);
        Route::resource('contract-document-types', ContractDocumentTypeController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/mail-test', [SettingController::class, 'sendTest'])->name('settings.mail-test');
        Route::post('settings/notices', [SettingController::class, 'sendNotices'])->name('settings.notices');
        Route::get('system-errors', [SystemErrorController::class, 'index'])->name('system-errors.index');
        Route::post('system-errors/{systemError}/review', [SystemErrorController::class, 'review'])->name('system-errors.review');
        Route::get('system-errors/{systemError}', [SystemErrorController::class, 'show'])->name('system-errors.show');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });
});
