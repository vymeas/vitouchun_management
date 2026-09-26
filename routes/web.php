<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentCardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TeachingController;
use App\Http\Controllers\TeacherRegistrationController;
use App\Http\Controllers\StudyShiftController;

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

Route::get('verify/student-card/{token}', [StudentCardController::class, 'verify'])->name('student-cards.verify');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'branch'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (from Breeze)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Branch Switcher (Navbar API)
    Route::post('/api/switch-branch', function (\Illuminate\Http\Request $request) {
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $branchId = $request->input('branch_id');
        if (empty($branchId)) {
            session()->forget('active_branch_id');
        } else {
            session(['active_branch_id' => $branchId]);
        }
        return response()->json(['success' => true]);
    })->name('branch.switch');


    // ================================================================
    // BRANCH MANAGEMENT (Super Admin only)
    // ================================================================
    Route::middleware(['permission:branches.view'])->group(function () {
        Route::resource('branches', BranchController::class);
    });

    // ================================================================
    // USER MANAGEMENT
    // ================================================================
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions');
        Route::post('users/{user}/permissions', [UserController::class, 'savePermissions'])->name('users.permissions.save');
    });

    // ================================================================
    // ACADEMIC MANAGEMENT
    // ================================================================
    Route::middleware(['permission:academic.view'])->group(function () {
        Route::resource('academic-years', \App\Http\Controllers\AcademicYearController::class);
        Route::resource('grades', \App\Http\Controllers\GradeController::class);
        Route::resource('subjects', \App\Http\Controllers\SubjectController::class);
        Route::resource('classes', \App\Http\Controllers\SchoolClassController::class);
        Route::resource('study-shifts', StudyShiftController::class)->except(['show']);
        Route::resource('students', \App\Http\Controllers\StudentController::class);
        Route::patch('students/{student}/suspend', [\App\Http\Controllers\StudentController::class, 'suspend'])->name('students.suspend');
        Route::patch('students/{student}/resume', [\App\Http\Controllers\StudentController::class, 'resume'])->name('students.resume');
        Route::resource('enrollments', \App\Http\Controllers\EnrollmentController::class);
    });

    // ================================================================
    // FINANCE & ACCOUNTING
    // ================================================================
    Route::middleware(['permission:finance.view'])->group(function () {
        Route::get('finance', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('payments/students/{student}/history', [PaymentController::class, 'history'])->name('payments.history');
        Route::get('payments/{payment}/invoice', [PaymentController::class, 'show'])->middleware('permission:invoice.view')->name('payments.invoice');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->middleware('permission:invoice.print')->name('payments.receipt');
        Route::middleware('permission:payment.edit')->group(function () {
            Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
            Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        });
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->middleware('permission:payment.delete')->name('payments.destroy');
        Route::get('payments/{payment}/refund', [PaymentController::class, 'refundCreate'])->middleware('permission:payment.edit')->name('payments.refund.create');
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refundStore'])->middleware('permission:payment.edit')->name('payments.refund.store');
        Route::get('refunds/{refund}/receipt', [PaymentController::class, 'refundReceipt'])->middleware('permission:invoice.print')->name('refunds.receipt');
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('accounting', AccountingController::class)->only(['index', 'show']);
        Route::get('finance/reports', [FinanceReportController::class, 'index'])->name('finance.reports');
        Route::get('finance/reports/payments.csv', [FinanceReportController::class, 'paymentsCsv'])->name('finance.reports.payments.csv');
    });

    Route::middleware(['permission:scores.view'])->group(function () {
        Route::get('scores/report', [ScoreController::class, 'index'])->name('scores.report');
        Route::resource('scores', ScoreController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    });

    Route::middleware(['permission:attendance.view'])->group(function () {
        Route::get('attendance/dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
        Route::get('attendance/absent', [AttendanceController::class, 'absent'])->name('attendance.absent');
        Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::resource('attendance', AttendanceController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    });

    Route::middleware(['permission:student_cards.view'])->group(function () {
        Route::get('digital-cards/dashboard', [StudentCardController::class, 'dashboard'])->name('digital-cards.dashboard');
        Route::get('digital-cards/{digitalCard}/print', [StudentCardController::class, 'print'])->name('digital-cards.print');
        Route::get('digital-cards/{digitalCard}/pdf', [StudentCardController::class, 'pdf'])->name('digital-cards.pdf');
        Route::post('digital-cards/{digitalCard}/deactivate', [StudentCardController::class, 'deactivate'])->name('digital-cards.deactivate');
        Route::post('digital-cards/{digitalCard}/lost', [StudentCardController::class, 'lost'])->name('digital-cards.lost');
        Route::post('digital-cards/{digitalCard}/renew', [StudentCardController::class, 'renew'])->name('digital-cards.renew');
        Route::post('digital-cards/{digitalCard}/replace', [StudentCardController::class, 'replace'])->name('digital-cards.replace');
        Route::resource('digital-cards', StudentCardController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware(['permission:reports.view'])->group(function () {
        Route::get('reports', [ReportsController::class, 'dashboard'])->name('reports.dashboard');
        Route::get('reports/{type}', [ReportsController::class, 'show'])->name('reports.show');
        Route::get('reports/{type}/export', [ReportsController::class, 'export'])->name('reports.export');
        Route::get('reports/{type}/pdf', [ReportsController::class, 'pdf'])->name('reports.pdf');
    });

    Route::middleware(['permission:employees.view'])->group(function () {
        Route::get('employees/dashboard', [EmployeeController::class, 'dashboard'])->name('employees.dashboard');
        Route::get('employees/export.csv', [EmployeeController::class, 'export'])->name('employees.export');
        Route::resource('employees', EmployeeController::class);
    });

    Route::middleware(['permission:teachers.view'])->group(function () {
        Route::get('teaching/dashboard', [TeachingController::class, 'dashboard'])->name('teaching.dashboard');
        Route::get('teachers', [TeachingController::class, 'teachers'])->name('teachers.index');
        Route::get('teachers/register', [TeacherRegistrationController::class, 'create'])->name('teachers.registration.create');
        Route::post('teachers/register', [TeacherRegistrationController::class, 'store'])->name('teachers.registration.store');
        Route::get('teachers/{teacher}', [TeacherRegistrationController::class, 'show'])->name('teachers.show');
        Route::get('teachers/{teacher}/edit', [TeacherRegistrationController::class, 'edit'])->name('teachers.edit');
        Route::put('teachers/{teacher}', [TeacherRegistrationController::class, 'update'])->name('teachers.update');
        Route::get('teacher-assignments', [TeachingController::class, 'assignments'])->name('teacher-assignments.index');
        Route::get('teacher-assignments/create', [TeachingController::class, 'createAssignment'])->name('teacher-assignments.create');
        Route::post('teacher-assignments', [TeachingController::class, 'storeAssignment'])->name('teacher-assignments.store');
        Route::get('teacher-subjects/create', [TeachingController::class, 'createSubject'])->name('teacher-subjects.create');
        Route::post('teacher-subjects', [TeachingController::class, 'storeSubject'])->name('teacher-subjects.store');
        Route::get('teacher-schedules', [TeachingController::class, 'schedule'])->name('teacher-schedules.index');
        Route::get('teacher-schedules/create', [TeachingController::class, 'createSchedule'])->name('teacher-schedules.create');
        Route::post('teacher-schedules', [TeachingController::class, 'storeSchedule'])->name('teacher-schedules.store');
        Route::delete('teacher-schedules/{schedule}', [TeachingController::class, 'destroySchedule'])->name('teacher-schedules.destroy');
    });

    // ================================================================
    // SETTINGS (Admin only)
    // ================================================================
    Route::middleware(['permission:settings.view'])->group(function () {
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('settings/services', [\App\Http\Controllers\ServiceController::class, 'index'])->name('settings.services.index');
        Route::put('settings/services/{service}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('settings.services.update');
    });

});
