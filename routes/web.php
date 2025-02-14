<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', [AuthenticationController::class, 'loginPage'])->name('login_page')->middleware(['guest']);
Route::post('/login', [AuthenticationController::class, 'login'])->name('login')->middleware(['guest']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard/DashboardView'))->name('dashboard');

    Route::controller(UserController::class)->group(function(){
        Route::get('/users','index')->name('user.browse');
        Route::post('/users','store')->name('user.create');
        Route::put('/users/{user}','update')->name('user.update');
        Route::delete('/users/{user}','destroy')->name('user.delete');
    });
});

