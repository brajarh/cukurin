<?php

// Helper global (Modul 11). Didaftarkan lewat composer.json -> autoload.files

if (!function_exists('numberToRupiah')) {
    function numberToRupiah($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('rupiahToNumber')) {
    function rupiahToNumber(string $rp): int
    {
        return (int) preg_replace('/\D/', '', $rp);
    }
}

if (!function_exists('formatDurasi')) {
    // 90 -> "1 jam 30 menit", 45 -> "45 menit"
    function formatDurasi($menit): string
    {
        $menit = (int) $menit;
        $jam = intdiv($menit, 60);
        $sisa = $menit % 60;

        if ($jam > 0 && $sisa > 0) {
            return "{$jam} jam {$sisa} menit";
        }
        if ($jam > 0) {
            return "{$jam} jam";
        }
        return "{$menit} menit";
    }
}

if (!function_exists('formatJam')) {
    // "09:00:00" -> "09:00"
    function formatJam($waktu): string
    {
        return substr((string) $waktu, 0, 5);
    }
}
