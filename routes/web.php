<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('sign_in'));
Route::get('/sign-in', fn () => Inertia::render('SignInView'),['title'=>'Sign In'])->name('sign_in');