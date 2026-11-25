# Paket Anggota 3: pagination M3, laporan Query Builder, tes

Disesuaikan dengan `main` commit `1652d08` (ada `detail_booking`, `pembayaran`, `ulasan`).
Tidak ada migration baru dan tidak ada file milik Braja/Gerry yang ditimpa.

## Cara pasang
```
git checkout main && git pull
git checkout -b feature/uas-anggota3
```
Salin isi folder ini (`app/`, `resources/`, `tests/`) ke root project. Pilih Replace kalau ditanya.
Hanya `BarberController`, `BarberScheduleController`, dan 9 view barber/jadwal yang menimpa file lama (semuanya M3 milikmu).

## Dua perubahan manual (file bersama, jadi tidak kutimpa)
**1. `routes/fitur.php`**: tambahkan `use` di bagian atas:
```php
use App\Http\Controllers\LaporanController;
```
lalu tambahkan di dalam grup `role:admin` (misalnya setelah route `ulasan.index`):
```php
    // Laporan rekap (Query Builder)
    Route::get('/admin/laporan', [LaporanController::class, 'index'])
        ->name('admin.laporan');
```
**2. `resources/views/partials/navbar.blade.php`**: di blok `@if ($isAdmin)`, setelah link Ulasan:
```blade
<li class="nav-item"><a class="nav-link" href="{{ route('admin.laporan') }}">Laporan</a></li>
```

## Jalankan
```
npm install && npm run build
php artisan test
```

## Isi
| File | Keterangan |
|---|---|
| `BarberController`, `BarberScheduleController` | paginate(10), pencarian tidak peduli huruf besar/kecil, jadwal bisa dicari nama barber |
| `views/barber/*`, `views/jadwal/*` | Bootstrap, tanpa `nav-admin` dan flash ganda (layout sudah punya) |
| `LaporanController`, `views/admin/laporan/index` | 4 rekap dengan `DB::table`: per cabang, per bulan, kinerja barber, layanan terlaris |
| `tests/Concerns/DataUji.php` | pembantu data uji (booking header + detail + pembayaran) |
| `tests/Feature/BarberJadwalTest.php` | 20 tes M3 |
| `tests/Feature/LaporanTest.php` | 11 tes laporan |

## Catatan
- Laporan menghitung pendapatan dari tabel `pembayaran` (periode = `dibayar_pada`), bukan dari status booking,
  jadi tetap benar setelah Braja mengubah status di T2.
- Tes dijalankan di SQLite. Laporan memakai `to_char` untuk PostgreSQL, tetapi belum diuji di PostgreSQL:
  coba buka `/admin/laporan` sekali setelah database beralih ke PostgreSQL.
- Tes `ExampleTest` bawaan Laravel gagal kalau `npm run build` belum dijalankan (manifest Vite tidak ada).
