<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

<<<<<<< HEAD
// Halaman publik (Guest, baca saja)
Route::get('/', [HomeController::class, 'landing'])->name('landing');
=======

// =========================
// HALAMAN PUBLIK
// =========================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/myhome', [HomeController::class, 'myHome']);

>>>>>>> 8c98ef33b483e4ce684eb4b405f22e9d210dc09b
Route::get('/about', [HomeController::class, 'about']);

Route::get('/contact', [HomeController::class, 'contact']);

<<<<<<< HEAD
// Login, register, reset password
Auth::routes();

// Dashboard (semua yang sudah login)
=======

// =========================
// AUTENTIKASI
// =========================

Auth::routes();


// =========================
// HALAMAN SETELAH LOGIN
// =========================

>>>>>>> 8c98ef33b483e4ce684eb4b405f22e9d210dc09b
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

<<<<<<< HEAD
require __DIR__ . '/fitur.php';
=======

// =========================
// ROUTE FITUR
// =========================

require __DIR__ . '/fitur.php';
>>>>>>> 8c98ef33b483e4ce684eb4b405f22e9d210dc09b
