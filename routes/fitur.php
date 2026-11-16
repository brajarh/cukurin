<?php

// Route fitur buatan Orang 2 (Pengembang Fitur).
// Dipanggil dari routes/web.php lewat: require __DIR__.'/fitur.php';
//
// Nanti, bungkus blok ADMIN dengan middleware login/role dari Orang 1, contoh:
// Route::middleware(['auth', 'role:1'])->group(function () { ...blok admin... });

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BarberScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// ===== TAHAP 2: CRUD data master (admin) =====
Route::resource('service', ServiceController::class)
    ->except(['show'])
    ->parameters(['service' => 'id']);

Route::resource('outlet', OutletController::class)
    ->except(['show'])
    ->parameters(['outlet' => 'id']);

Route::resource('barber', BarberController::class)
    ->parameters(['barber' => 'id']);

// ===== TAHAP 3: Jadwal kerja barber (admin) =====
Route::resource('jadwal', BarberScheduleController::class)
    ->except(['show'])
    ->parameters(['jadwal' => 'id']);

// ===== TAHAP 4: Booking sisi customer (tanpa login) =====
// Urutan penting: route statis (slot, riwayat) harus sebelum /booking/{kode}
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::get('/booking/slot', [BookingController::class, 'slot'])->name('booking.slot');
Route::get('/booking/riwayat', [BookingController::class, 'riwayat'])->name('booking.riwayat');
Route::post('/booking/konfirmasi', [BookingController::class, 'konfirmasi'])->name('booking.konfirmasi');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/{kode}', [BookingController::class, 'receipt'])->name('booking.receipt');
Route::get('/booking/{kode}/ubah', [BookingController::class, 'ubah'])->name('booking.ubah');
Route::put('/booking/{kode}', [BookingController::class, 'perbarui'])->name('booking.perbarui');
Route::post('/booking/{kode}/batal', [BookingController::class, 'batal'])->name('booking.batal');

// ===== TAHAP 5: Booking sisi admin =====
Route::prefix('admin/booking')->name('admin.booking.')->group(function () {
    Route::get('/', [AdminBookingController::class, 'index'])->name('index');
    Route::get('/kalender', [AdminBookingController::class, 'kalender'])->name('kalender');
    Route::get('/events', [AdminBookingController::class, 'events'])->name('events');
    Route::patch('/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('status');
    Route::delete('/{id}', [AdminBookingController::class, 'destroy'])->name('destroy');
});
