<?php

use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\RoleAndPermissionController;
use App\Http\Controllers\API\UserActivityController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/data-table', 'dataTable')->name('api.user.data_table')->can('user.browse');
        Route::post('/', 'create')->name('api.user.create')->can('user.create');
        Route::put('/{user}', 'update')->name('api.user.update')->can('user.update');
        Route::delete('/{user}', 'delete')->name('api.user.delete')->can('user.delete');
        Route::post('/switch-status', 'switchStatus')->name('api.user.switch_status')->can('user.update');
    });

    Route::controller(RoleAndPermissionController::class)->prefix('user-roles')->group(function () {
        Route::get('/{role}/permissions', 'getRolePermission')->name('api.role.permission_list')->can('role.browse');
        Route::get('/{role}/users', 'getRoleUser')->name('api.role.user_list')->can('role.browse');
        Route::put('/{role}/switch-permission', 'switchPermission')->name('api.role.switch_permission')->can('role.assign_permission');
    });

    Route::controller(UserActivityController::class)->prefix('user-activity')->group(function () {
        Route::get('/data-table', 'dataTable')->name('api.user_activity.data_table');
    });

    Route::controller(AccountController::class)->prefix('account')->group(function () {
        Route::post('/update-information', 'updateInformation')->name('api.account.update_information');
        Route::post('/change-password', 'changePassword')->name('api.account.change_password');
    });

    Route::controller(NotificationController::class)->prefix('notification')->group(function () {
        Route::get('/', 'getNotificationList')->name('api.notification.all');
        Route::get('/unread', 'getUnreadNotificationList')->name('api.notification.unread');
    });
});
