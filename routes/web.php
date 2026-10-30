<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTemplateController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentTransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Setup\AcademicYearController;
use App\Http\Controllers\Setup\AssignmentController;
use App\Http\Controllers\Setup\ClassController;
use App\Http\Controllers\Setup\SubjectController;
use App\Http\Controllers\Setup\TermController;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('admin-only', DashboardController::class)->middleware(RoleMiddleware::class.':'.Roles::ADMIN)->name('admin-only');

    Route::get('manage-users', DashboardController::class)->middleware(PermissionMiddleware::class.':'.Permissions::USERS_MANAGE)->name('manage-users');

    // Users & roles: admins create accounts, assign roles and remove accounts.
    Route::middleware('permission:'.Permissions::USERS_MANAGE)->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('users/{user}/subjects', [UserController::class, 'subjects'])->name('users.subjects');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // People: students. Teachers can enrol students in their own classes;
    // admins manage everyone. Delete stays admin-only.
    Route::middleware('permission:'.Permissions::STUDENTS_MANAGE.'|'.Permissions::STUDENTS_ENROL)->group(function () {
        Route::resource('students', StudentController::class)
            ->only(['index', 'store', 'edit', 'update']);

        Route::middleware('permission:'.Permissions::STUDENTS_MANAGE)->group(function () {
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        });

        Route::middleware('permission:'.Permissions::IMPORT_EXPORT)->group(function () {
            Route::get('students/export', [StudentTransferController::class, 'export'])->name('students.export');
            Route::post('students/import', [StudentTransferController::class, 'import'])->name('students.import');
        });
    });

    Route::middleware('permission:'.Permissions::GUARDIANS_MANAGE)->group(function () {
        Route::resource('guardians', GuardianController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy']);
    });

    // Marks: teachers enter scores for their own assignments.
    Route::middleware('permission:'.Permissions::MARKS_VIEW)->group(function () {
        Route::get('marks', [MarkController::class, 'index'])->name('marks.index');

        Route::post('marks', [MarkController::class, 'store'])
            ->middleware('permission:'.Permissions::MARKS_ENTER)
            ->name('marks.store');
    });

    // Report compilation & workflow: compile -> approve -> publish.
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])
            ->middleware('permission:'.Permissions::REPORTS_COMPILE.'|'.Permissions::REPORTS_APPROVE.'|'.Permissions::REPORTS_PUBLISH)
            ->name('index');

        Route::post('compile', [ReportController::class, 'compile'])
            ->middleware('permission:'.Permissions::REPORTS_COMPILE)
            ->name('compile');

        Route::post('{report}/approve', [ReportController::class, 'approve'])
            ->middleware('permission:'.Permissions::REPORTS_APPROVE)
            ->name('approve');

        Route::post('{report}/publish', [ReportController::class, 'publish'])
            ->middleware('permission:'.Permissions::REPORTS_PUBLISH)
            ->name('publish');

        Route::get('{report}/pdf', [ReportController::class, 'pdf'])
            ->middleware('permission:'.Permissions::REPORTS_COMPILE.'|'.Permissions::REPORTS_APPROVE.'|'.Permissions::REPORTS_PUBLISH)
            ->name('pdf');
    });

    // Attendance per term: admins and class teachers (checked in the controller).
    Route::post('reports/attendance', [ReportController::class, 'attendance'])
        ->middleware('permission:'.Permissions::MARKS_ENTER)
        ->name('reports.attendance');

    // Report card layout (admin).
    Route::middleware('permission:'.Permissions::SETUP_MANAGE)->group(function () {
        Route::get('settings/report-template', [ReportTemplateController::class, 'edit'])->name('settings.report-template');
        Route::put('settings/report-template', [ReportTemplateController::class, 'update'])->name('settings.report-template.update');
    });

    Route::get('audit', [AuditLogController::class, 'index'])
        ->middleware('permission:'.Permissions::AUDIT_LOG)
        ->name('audit.index');

    // Parent portal: guardians download published reports for their children.
    Route::middleware('permission:'.Permissions::PORTAL_REPORTS_VIEW)->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalController::class, 'index'])->name('index');
        Route::get('reports/{report}/pdf', [PortalController::class, 'pdf'])->name('reports.pdf');
    });

    // School setup module (admin): years, terms, classes, subjects, assignments.
    Route::middleware('permission:'.Permissions::SETUP_MANAGE)->prefix('setup')->name('setup.')->group(function () {
        Route::get('/', [AcademicYearController::class, 'index'])->name('index');

        Route::resource('years', AcademicYearController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['years' => 'year'])
            ->names([
                'index' => 'years',
                'store' => 'years.store',
                'edit' => 'years.edit',
                'update' => 'years.update',
                'destroy' => 'years.destroy',
            ]);

        Route::get('years/{year}/terms', [TermController::class, 'index'])->name('year.terms');
        Route::post('years/{year}/terms', [TermController::class, 'store'])->name('year.terms.store');
        Route::put('years/{year}/terms/{term}', [TermController::class, 'update'])->name('year.terms.update');
        Route::delete('years/{year}/terms/{term}', [TermController::class, 'destroy'])->name('year.terms.destroy');

        Route::resource('classes', ClassController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['classes' => 'class'])
            ->names([
                'index' => 'classes',
                'store' => 'classes.store',
                'edit' => 'classes.edit',
                'update' => 'classes.update',
                'destroy' => 'classes.destroy',
            ]);

        Route::resource('subjects', SubjectController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['subjects' => 'subject'])
            ->names([
                'index' => 'subjects',
                'store' => 'subjects.store',
                'edit' => 'subjects.edit',
                'update' => 'subjects.update',
                'destroy' => 'subjects.destroy',
            ]);

        Route::resource('assignments', AssignmentController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['assignments' => 'assignment'])
            ->names([
                'index' => 'assignments',
                'store' => 'assignments.store',
                'edit' => 'assignments.edit',
                'update' => 'assignments.update',
                'destroy' => 'assignments.destroy',
            ]);
    });
});
