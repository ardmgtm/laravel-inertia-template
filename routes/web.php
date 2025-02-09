<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('sign_in'));
Route::get('/sign-in', fn () => Inertia::render('SignInView'))->name('sign_in');

Route::get('/dashboard', fn () => Inertia::render('Dashboard/DashboardView'))->name('dashboard');
