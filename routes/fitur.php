<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BarberScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UlasanController;
use Illuminate\Support\Facades\Route;


// =========================
// AKSES ADMIN
// =========================

Route::middleware(['auth', 'role:admin'])->group(function () {

    // CRUD Service
    Route::resource('service', ServiceController::class)
        ->except(['show'])
        ->parameters(['service' => 'id']);

    // CRUD Outlet
    Route::resource('outlet', OutletController::class)
        ->except(['show'])
        ->parameters(['outlet' => 'id']);

    // CRUD Barber
    Route::resource('barber', BarberController::class)
        ->parameters(['barber' => 'id']);

    // Jadwal Barber
    Route::resource('jadwal', BarberScheduleController::class)
        ->except(['show'])
        ->parameters(['jadwal' => 'id']);

    // Kelola Booking
    Route::prefix('admin/booking')
        ->name('admin.booking.')
        ->group(function () {

            Route::get('/', [AdminBookingController::class, 'index'])
                ->name('index');

            Route::get('/kalender', [AdminBookingController::class, 'kalender'])
                ->name('kalender');

            Route::get('/events', [AdminBookingController::class, 'events'])
                ->name('events');

            Route::patch('/{id}/status', [AdminBookingController::class, 'updateStatus'])
                ->name('status');

            Route::delete('/{id}', [AdminBookingController::class, 'destroy'])
                ->name('destroy');
        });

    // Transaksi T3: Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])
        ->name('pembayaran.index');
    Route::get('/pembayaran/{idBooking}/bayar', [PembayaranController::class, 'create'])
        ->name('pembayaran.create');
    Route::post('/pembayaran', [PembayaranController::class, 'store'])
        ->name('pembayaran.store');

    // Transaksi T4: Daftar Ulasan (Admin)
    Route::get('/admin/ulasan', [UlasanController::class, 'index'])
        ->name('ulasan.index');
});


// =========================
// AKSES PELANGGAN
// =========================

Route::middleware(['auth', 'role:pelanggan'])->group(function () {

    Route::get('/booking', [BookingController::class, 'create'])
        ->name('booking.create');

    Route::get('/booking/slot', [BookingController::class, 'slot'])
        ->name('booking.slot');

    Route::get('/booking/riwayat', [BookingController::class, 'riwayat'])
        ->name('booking.riwayat');

    Route::post('/booking/konfirmasi', [BookingController::class, 'konfirmasi'])
        ->name('booking.konfirmasi');

    Route::post('/booking', [BookingController::class, 'store'])
        ->name('booking.store');

    Route::get('/booking/{kode}', [BookingController::class, 'receipt'])
        ->name('booking.receipt');

    Route::get('/booking/{kode}/ubah', [BookingController::class, 'ubah'])
        ->name('booking.ubah');

    Route::put('/booking/{kode}', [BookingController::class, 'perbarui'])
        ->name('booking.perbarui');

    Route::post('/booking/{kode}/batal', [BookingController::class, 'batal'])
        ->name('booking.batal');

    // Transaksi T4: Tulis Ulasan (Pelanggan)
    Route::get('/ulasan/{idBooking}/tulis', [UlasanController::class, 'create'])
        ->name('ulasan.create');
    Route::post('/ulasan', [UlasanController::class, 'store'])
        ->name('ulasan.store');
});