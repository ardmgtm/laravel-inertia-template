<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\User\RoleAndPermissionController;
use App\Http\Controllers\User\UserActivityController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Broadcast::routes(['middleware' => ['auth']]);

// Captcha API endpoint for refreshing
Route::get('/api/captcha/refresh', function () {
    return response()->json([
        'captcha' => '/captcha/default?' . time()
    ]);
})->name('captcha.refresh');

Route::get('/login', [AuthenticationController::class, 'loginPage'])->name('login_page')->middleware(['guest']);
Route::post('/login', [AuthenticationController::class, 'login'])->name('login')->middleware(['guest']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard/DashboardView'))->name('dashboard');

    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/', 'index')->name('user.browse')->can('user.browse');
    });

    Route::controller(RoleAndPermissionController::class)->prefix('user-roles')->group(function () {
        Route::get('/', 'index')->name('role.browse')->can('role.browse');
        Route::post('/', 'create')->name('role.create')->can('role.create');
        Route::put('/{role}', 'update')->name('role.update')->can('role.update');
        Route::delete('/{role}', 'delete')->name('role.delete')->can('role.delete');
    });

    Route::controller(UserActivityController::class)->prefix('user-activity')->group(function () {
        Route::get('/', 'index')->name('user_activity.browse');
    });

    Route::controller(AccountController::class)->prefix('account')->group(function () {
        Route::get('/', 'index')->name('account.browse');
    });
});
