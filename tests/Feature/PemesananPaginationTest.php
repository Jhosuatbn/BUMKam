<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemesananPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_list_is_paginated_by_the_database(): void
    {
        $staff = $this->user('Staff', 'order_list_staff');
        $this->createOrders(17, 'Menunggu Persetujuan');

        $response = $this->actingAs($staff)->get(route('pemesanan.index'));

        $response->assertOk()->assertViewHas('pemesanan', function ($orders) {
            return $orders->total() === 17
                && $orders->perPage() === 15
                && $orders->count() === 15;
        });
    }

    public function test_approval_list_pagination_keeps_the_selected_status_filter(): void
    {
        $leader = $this->user('Pimpinan', 'approval_list_leader');
        $this->createOrders(17, 'Menunggu Persetujuan');

        $response = $this->actingAs($leader)->get(route('pemesanan.persetujuan', [
            'status' => 'Menunggu Persetujuan',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertViewHas('halamanPersetujuan', true)
            ->assertViewHas('status', 'Menunggu Persetujuan')
            ->assertViewHas('pemesanan', function ($orders) {
                parse_str((string) parse_url($orders->url(1), PHP_URL_QUERY), $query);

                return $orders->total() === 17
                    && $orders->currentPage() === 2
                    && $orders->count() === 2
                    && ($query['status'] ?? null) === 'Menunggu Persetujuan';
            });
    }

    private function user(string $role, string $username): Pengguna
    {
        return Pengguna::create([
            'id_pengguna' => 'PGN-'.strtoupper(substr(md5($username), 0, 10)),
            'username' => $username,
            'password' => bcrypt('secret'),
            'nama_lengkap' => $username,
            'peran' => $role,
        ]);
    }

    private function createOrders(int $count, string $approvalStatus): void
    {
        $customer = Pelanggan::create([
            'id_pelanggan' => 'PLG-PAGINATION',
            'nama_pelanggan' => 'Pelanggan Pagination',
            'nik' => '1122334455667788',
            'no_telepon' => '081234567890',
            'alamat' => 'Alamat uji pagination',
        ]);

        for ($index = 1; $index <= $count; $index++) {
            Pemesanan::create([
                'id_pemesanan' => 'PSN-PAGE-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'id_pelanggan' => $customer->id_pelanggan,
                'tanggal_pemesanan' => now()->toDateString(),
                'skema_pembayaran' => 'Tanpa DP',
                'dp' => 0,
                'tenor' => 12,
                'bunga' => 0,
                'total_harga' => 120000,
                'angsuran' => 10000,
                'status_pemesanan' => 'Belum Lunas',
                'status_persetujuan' => $approvalStatus,
            ]);
        }
    }
}
