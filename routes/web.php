<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EndUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

Route::middleware(['auth', 'role:admin,user'])->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin,user'])
    ->name('dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
        Route::post('users/{user}/password-reset-link', [UserController::class, 'sendPasswordResetLink'])
            ->name('users.password-reset-link');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('end-users', EndUserController::class)
            ->except(['create', 'edit', 'show']);

    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('projects', ProjectController::class)
            ->except([
                'create',
                'edit',
                'show',
            ]);

    });

Route::middleware(['auth', 'role:user'])
    ->group(function () {

        Route::get(
            '/user/dashboard',
            [
                UserDashboardController::class,
                'index',
            ]
        )
            ->name('user.dashboard');

    });

Route::middleware(['auth', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        Route::resource('projects', UserProjectController::class)
            ->only([
                'index',
                'update',
            ]);

    });

require __DIR__.'/auth.php';
