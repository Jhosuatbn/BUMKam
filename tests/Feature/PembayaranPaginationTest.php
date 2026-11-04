<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_list_is_paginated_and_keeps_installment_history_for_visible_rows(): void
    {
        $staff = Pengguna::create([
            'id_pengguna' => 'PGN-PAY-PAGE', 'username' => 'payment_page_staff', 'password' => bcrypt('secret'),
            'nama_lengkap' => 'Staff Pembayaran', 'peran' => 'Staff',
        ]);
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-PAY-PAGE', 'nama_pelanggan' => 'Pelanggan Pembayaran', 'nik' => '1234432112344321',
            'no_telepon' => '081234567890', 'alamat' => 'Alamat uji pagination pembayaran',
        ]);
        $order = Pemesanan::create([
            'id_pemesanan' => 'PSN-PAY-PAGE', 'id_pelanggan' => $customer->id_pelanggan,
            'tanggal_pemesanan' => now()->toDateString(), 'skema_pembayaran' => 'Angsuran', 'dp' => 0,
            'tenor' => 24, 'bunga' => 0, 'total_harga' => 240000, 'angsuran' => 10000,
            'status_pemesanan' => 'Belum Lunas',
        ]);

        for ($index = 1; $index <= 17; $index++) {
            Pembayaran::create([
                'id_pembayaran' => 'BYR-PAGE-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'id_pemesanan' => $order->id_pemesanan,
                'tanggal_pembayaran' => now()->subDays(17 - $index)->toDateString(),
                'nominal' => 10000,
                'total_pembayaran' => $index * 10000,
                'sisa_pembayaran' => (24 - $index) * 10000,
                'status_pembayaran' => 'Belum Lunas',
                'metode_pembayaran' => 'tunai',
                'status_gateway' => 'settlement',
            ]);
        }

        $this->actingAs($staff)->get(route('pembayaran.index'))
            ->assertOk()
            ->assertSee('Pelanggan Pembayaran')
            ->assertViewHas('pembayaran', function ($payments) {
                $first = $payments->first();

                return $payments->total() === 17
                    && $payments->perPage() === 15
                    && $payments->count() === 15
                    && $first->pemesanan->pelanggan->nama_pelanggan === 'Pelanggan Pembayaran'
                    && $first->pemesanan->pembayaran->count() === 17;
            });
    }
}
