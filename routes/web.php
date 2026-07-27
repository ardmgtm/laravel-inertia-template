<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\NotificationController;
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
        'captcha' => '/captcha/default?'.time(),
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
        Route::get('/data-table', 'dataTable')->name('user.data_table')->can('user.browse');
        Route::post('/', 'create')->name('user.create')->can('user.create');
        Route::put('/{user}', 'update')->name('user.update')->can('user.update');
        Route::delete('/{user}', 'delete')->name('user.delete')->can('user.delete');
        Route::post('/switch-status', 'switchStatus')->name('user.switch_status')->can('user.update');
    });

    Route::controller(RoleAndPermissionController::class)->prefix('user-roles')->group(function () {
        Route::get('/', 'index')->name('role.browse')->can('role.browse');
        Route::post('/', 'create')->name('role.create')->can('role.create');
        Route::put('/{role}', 'update')->name('role.update')->can('role.update');
        Route::delete('/{role}', 'delete')->name('role.delete')->can('role.delete');
        Route::get('/{role}/permissions', 'getRolePermission')->name('role.permission_list')->can('role.browse');
        Route::get('/{role}/users', 'getRoleUser')->name('role.user_list')->can('role.browse');
        Route::post('/{role}/switch-permission', 'switchPermission')->name('role.switch_permission')->can('role.assign_permission');
    });

    Route::controller(UserActivityController::class)->prefix('user-activity')->group(function () {
        Route::get('/', 'index')->name('user_activity.browse');
        Route::get('/data-table', 'dataTable')->name('user_activity.data_table');
    });

    Route::controller(AccountController::class)->prefix('account')->group(function () {
        Route::get('/', 'index')->name('account.browse');
        Route::post('/update-information', 'updateInformation')->name('account.update_information');
        Route::post('/change-password', 'changePassword')->name('account.change_password');
    });

    Route::controller(NotificationController::class)->prefix('notification')->group(function () {
        Route::get('/', 'getNotificationList')->name('notification.all');
        Route::get('/unread', 'getUnreadNotificationList')->name('notification.unread');
        Route::put('/{id}/read', 'markAsRead')->name('notification.mark_as_read');
        Route::put('/read-all', 'markAllAsRead')->name('notification.mark_all_as_read');
    });
});
