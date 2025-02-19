<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\User\RoleAndPermissionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', [AuthenticationController::class, 'loginPage'])->name('login_page')->middleware(['guest']);
Route::post('/login', [AuthenticationController::class, 'login'])->name('login')->middleware(['guest']);

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard/DashboardView'))->name('dashboard');

    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('user.browse');
        Route::post('/users', 'store')->name('user.create');
        Route::put('/users/{user}', 'update')->name('user.update');
        Route::delete('/users/{user}', 'destroy')->name('user.delete');

        Route::get('/users/data-table', 'dataTable')->name('user.data_table');
    });
    Route::controller(RoleAndPermissionController::class)->group(function () {
        Route::get('/user-roles', 'index')->name('role.browse');
        Route::post('/role', 'create')->name('role.create')->can('role.create');
        Route::put('/role/{role}', 'update')->name('role.update')->can('role.update');
        Route::delete('/role/{role}', 'delete')->name('role.delete')->can('role.delete');
        Route::get('/role/{role}/permissions', 'getRolePermission')->name('role.permission_list')->can('role.browse');
        Route::get('/role/{role}/users', 'getRoleUser')->name('role.user_list')->can('role.browse');
        Route::put('/role/{role}/switch-permission', 'switchPermission')->name('role.switch_permission')->can('role.assign_permission');
    });
});
