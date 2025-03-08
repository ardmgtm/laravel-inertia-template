<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\User\RoleAndPermissionController;
use App\Http\Controllers\User\UserActivityController;
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
        Route::get('/users', 'index')->name('user.browse')->can('user.browse');
        Route::post('/users', 'create')->name('user.create')->can('user.create');
        Route::put('/users/{user}', 'update')->name('user.update')->can('user.update');
        Route::delete('/users/{user}', 'delete')->name('user.delete')->can('user.delete');

        Route::get('/users/data-table', 'dataTable')->name('user.data_table')->can('user.browse');
        Route::post('/users/switch-status','switchStatus')->name('user.switch_status')->can('user.update');
    });
    Route::controller(RoleAndPermissionController::class)->group(function () {
        Route::get('/user-roles', 'index')->name('role.browse')->can('role.browse');
        Route::post('/user-roles', 'create')->name('role.create')->can('role.create');
        Route::put('/user-roles/{role}', 'update')->name('role.update')->can('role.update');
        Route::delete('/user-roles/{role}', 'delete')->name('role.delete')->can('role.delete');

        Route::get('/user-roles/{role}/permissions', 'getRolePermission')->name('role.permission_list')->can('role.browse');
        Route::get('/user-roles/{role}/users', 'getRoleUser')->name('role.user_list')->can('role.browse');
        Route::put('/user-roles/{role}/switch-permission', 'switchPermission')->name('role.switch_permission')->can('role.assign_permission');
    });
    Route::controller(UserActivityController::class)->group(function(){
        Route::get('/user-activity','index')->name('user_activity.browse');
        Route::get('/user-activity/data-table', 'dataTable')->name('user_activity.data_table');
    });
    Route::controller(AccountController::class)->group(function () {
        Route::get('/account', 'index')->name('account.browse');
        Route::post('/account/update-information', 'updateInformation')->name('account.update_information');
        Route::post('/account/change-password', 'changePassword')->name('account.change_password');
    });
});
