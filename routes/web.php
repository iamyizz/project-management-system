<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EndUserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserProjectController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        Route::resource('users', UserController::class);
    });

Route::middleware(['auth','role:admin'])
->prefix('admin')
->name('admin.')
->group(function(){

    Route::resource('end-users', EndUserController::class)
        ->except(['create','edit','show']);

});

Route::middleware(['auth','role:admin'])
->prefix('admin')
->name('admin.')
->group(function(){

    Route::resource('projects', ProjectController::class)
        ->except([
            'create',
            'edit',
            'show'
        ]);

});

Route::middleware(['auth','role:user'])
->group(function(){

    Route::get(
        '/user/dashboard',
        [
            UserDashboardController::class,
            'index'
        ]
    )
    ->name('user.dashboard');


});

Route::middleware(['auth','role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function(){

        Route::resource('projects', UserProjectController::class)
            ->only([
                'index',
                'update'
            ]);

    });

require __DIR__.'/auth.php';
