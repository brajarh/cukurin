<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;


// =========================
// HALAMAN PUBLIK
// =========================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/myhome', [HomeController::class, 'myHome']);

Route::get('/about', [HomeController::class, 'about']);

Route::get('/contact', [HomeController::class, 'contact']);


// =========================
// AUTENTIKASI
// =========================

Auth::routes();


// =========================
// HALAMAN SETELAH LOGIN
// =========================

Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');


// =========================
// ROUTE FITUR
// =========================

require __DIR__ . '/fitur.php';