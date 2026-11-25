<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


// Halaman publik (Guest, baca saja)
Route::get('/', [HomeController::class, 'landing'])->name('landing');
Route::get('/about', [HomeController::class, 'about']);
Route::get('/contact', [HomeController::class, 'contact']);

// Login, register, reset password
Auth::routes();

// Dashboard (semua yang sudah login)
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

require __DIR__.'/fitur.php';
