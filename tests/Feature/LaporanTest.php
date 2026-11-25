<?php

namespace Tests\Feature;

use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataUji;
use Tests\TestCase;

/**
 * Tes laporan Query Builder (/admin/laporan): akses, angka agregat, filter, validasi.
 */
class LaporanTest extends TestCase
{
    use RefreshDatabase, DataUji;

    private $outletA;
    private $outletB;
    private $potong;
    private $jenggot;
    private $raka;
    private $fajar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanTes();
    }

    /**
     * Skenario: cabang A (Raka) menerima 2 pembayaran: 55.000 (potong+jenggot) dan 35.000 (potong).
     * Cabang B (Fajar) menerima 1 pembayaran 35.000. Satu booking Fajar belum dibayar (tidak dihitung).
     */
    private function siapkanSkenario(): void
    {
        $this->outletA = $this->buatOutlet('Cabang A');
        $this->outletB = $this->buatOutlet('Cabang B');
        $this->potong = $this->buatService('Potong', 35000, 30);
        $this->jenggot = $this->buatService('Jenggot', 20000, 30);
        $this->raka = $this->buatBarber($this->outletA, [$this->potong, $this->jenggot], 'Raka');
        $this->fajar = $this->buatBarber($this->outletB, [$this->potong], 'Fajar');
        $this->buatBarber($this->outletB, [$this->potong], 'Tanpa Transaksi');

        $this->bayar($this->buatBooking($this->raka, [$this->potong, $this->jenggot], null, '10:00'), 55000);
        $this->bayar($this->buatBooking($this->raka, [$this->potong], null, '11:00'), 35000);
        $this->bayar($this->buatBooking($this->fajar, [$this->potong], null, '10:00'), 35000);
        $this->buatBooking($this->fajar, [$this->potong], null, '12:00'); // belum dibayar
    }

    private function urlPeriodeIni(string $tambahan = ''): string
    {
        $now = SlotService::sekarang();

        return '/admin/laporan?dari=' . $now->copy()->startOfMonth()->format('Y-m-d')
            . '&sampai=' . $now->copy()->endOfMonth()->format('Y-m-d') . $tambahan;
    }

    // ---------------- Akses ----------------
    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get('/admin/laporan')->assertRedirect('/login');
    }

    public function test_pelanggan_tidak_boleh_membuka_laporan(): void
    {
        $this->actingAs($this->userDenganRole('pelanggan'));

        $this->get('/admin/laporan')->assertForbidden();
    }

    public function test_admin_bisa_membuka_laporan_kosong(): void
    {
        $this->loginAdmin();

        $this->get('/admin/laporan')->assertOk()->assertSee('Laporan dan Rekap');
    }

    // ---------------- Angka ----------------
    public function test_pendapatan_per_cabang_dijumlah_dari_pembayaran(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $res = $this->get($this->urlPeriodeIni())->assertOk();
        $perCabang = $res->viewData('perCabang');

        $this->assertCount(2, $perCabang);
        $this->assertSame('Cabang A', $perCabang[0]->nama_outlet); // terbesar di atas
        $this->assertEquals(90000, $perCabang[0]->total_pendapatan);
        $this->assertEquals(2, $perCabang[0]->jumlah_transaksi);
        $this->assertEquals(35000, $perCabang[1]->total_pendapatan);
        $this->assertEquals(125000, $res->viewData('totalPendapatan'));
    }

    public function test_pendapatan_per_bulan_dikelompokkan_per_tahun_bulan(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $perBulan = $this->get($this->urlPeriodeIni())->viewData('perBulan');

        $this->assertCount(1, $perBulan);
        $this->assertSame(SlotService::sekarang()->format('Y-m'), $perBulan[0]->bulan);
        $this->assertEquals(125000, $perBulan[0]->total_pendapatan);
        $this->assertEquals(3, $perBulan[0]->jumlah_transaksi);
    }

    public function test_layanan_terlaris_dihitung_dari_detail_booking(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $perLayanan = $this->get($this->urlPeriodeIni())->viewData('perLayanan');

        $this->assertSame('Potong', $perLayanan[0]->nama_service);
        $this->assertEquals(3, $perLayanan[0]->jumlah_dipesan);
        $this->assertEquals(105000, $perLayanan[0]->nilai);
        $this->assertSame('Jenggot', $perLayanan[1]->nama_service);
        $this->assertEquals(1, $perLayanan[1]->jumlah_dipesan);
        $this->assertEquals(20000, $perLayanan[1]->nilai);
    }

    public function test_kinerja_barber_menampilkan_barber_tanpa_transaksi_dengan_pendapatan_nol(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $perBarber = $this->get($this->urlPeriodeIni())->viewData('perBarber')->keyBy('nama_barber');

        $this->assertCount(3, $perBarber);
        $this->assertEquals(90000, $perBarber['Raka']->pendapatan);
        $this->assertEquals(2, $perBarber['Raka']->total_dibayar);
        $this->assertEquals(35000, $perBarber['Fajar']->pendapatan);
        $this->assertEquals(0, $perBarber['Tanpa Transaksi']->pendapatan);
        $this->assertEquals(0, $perBarber['Tanpa Transaksi']->total_dibayar);
    }

    // ---------------- Filter ----------------
    public function test_filter_cabang_membatasi_semua_rekap(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $res = $this->get($this->urlPeriodeIni("&id_outlet={$this->outletB->id_outlet}"));

        $this->assertCount(1, $res->viewData('perCabang'));
        $this->assertSame('Cabang B', $res->viewData('perCabang')[0]->nama_outlet);
        $this->assertEquals(35000, $res->viewData('totalPendapatan'));
        $this->assertCount(2, $res->viewData('perBarber')); // Fajar dan Tanpa Transaksi
        $this->assertSame('Potong', $res->viewData('perLayanan')[0]->nama_service);
        $this->assertEquals(1, $res->viewData('perLayanan')[0]->jumlah_dipesan);
    }

    public function test_pembayaran_di_luar_periode_tidak_dihitung(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();
        $bulanLalu = SlotService::sekarang()->subMonths(2);
        $this->bayar($this->buatBooking($this->raka, [$this->potong], null, '14:00'), 99000, $bulanLalu);

        $res = $this->get($this->urlPeriodeIni());

        $this->assertEquals(125000, $res->viewData('totalPendapatan'));
    }

    public function test_periode_tanpa_pembayaran_menghasilkan_rekap_kosong(): void
    {
        $this->loginAdmin();
        $this->siapkanSkenario();

        $res = $this->get('/admin/laporan?dari=2020-01-01&sampai=2020-01-31')->assertOk();

        $this->assertCount(0, $res->viewData('perCabang'));
        $this->assertEquals(0, $res->viewData('totalPendapatan'));
        $this->assertSame(0, $res->viewData('perLayanan')->count());
    }

    // ---------------- Validasi ----------------
    public function test_tanggal_akhir_tidak_boleh_lebih_kecil_dari_tanggal_awal(): void
    {
        $this->loginAdmin();

        $this->get('/admin/laporan?dari=2026-11-20&sampai=2026-11-01')
            ->assertSessionHasErrors('sampai');
    }
}
