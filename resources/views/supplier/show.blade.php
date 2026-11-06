@extends('layouts.sneat-dashboard')

@section('title', 'Detail Supplier')

@section('content')
    <section class="card mb-4 overflow-hidden" aria-labelledby="supplier-detail-heading">
        <div class="card-header d-flex align-items-start justify-content-between gap-3">
            <div class="min-w-0">
                <h1 id="supplier-detail-heading" class="card-title h4 mb-1">{{ $supplier->nama_supplier }}</h1>
                <p class="mb-0 text-body-secondary">
                    {{ $supplier->id_supplier }}
                    <span aria-hidden="true">·</span>
                    {{ $supplier->no_telepon ?: 'Kontak belum diisi' }}
                </p>
            </div>
            <a class="btn bumkam-green-action d-inline-flex flex-shrink-0 align-items-center"
                href="{{ route('supplier.index') }}">
                <i class="icon-base bx bx-arrow-back" aria-hidden="true"></i>
                Kembali
            </a>
        </div>

        <div class="card-body row g-3">
            <div class="col-md-4">
                <small class="text-body-secondary">Penanggung Jawab Kontak</small>
                <div>{{ $supplier->kontak_person ?: '—' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-body-secondary">Email</small>
                <div class="text-break">{{ $supplier->email ?: '—' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-body-secondary">Alamat</small>
                <div>{{ $supplier->alamat ?: '—' }}</div>
            </div>
        </div>
    </section>

    <section class="card overflow-hidden" aria-labelledby="supplier-purchase-history-heading">
        <div class="card-header">
            <h2 id="supplier-purchase-history-heading" class="card-title mb-0">Riwayat Pembelian</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">No. Transaksi</th>
                        <th scope="col">Barang</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Petugas Penerima</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplier->barangMasuk as $entry)
                        @foreach ($entry->details as $line)
                            <tr>
                                <td class="text-nowrap">{{ $entry->tanggal_masuk?->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $entry->id_barang_masuk }}</td>
                                <td>{{ $line->barang?->nama_barang }}</td>
                                <td>{{ number_format($line->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $entry->penerima?->nama_lengkap ?? $entry->penerima?->username ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('barang-masuk.show', $entry) }}"
                                        class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                                        title="Lihat detail penerimaan {{ $entry->id_barang_masuk }}"
                                        aria-label="Lihat detail penerimaan {{ $entry->id_barang_masuk }}">
                                        <i class="icon-base bx bx-show" aria-hidden="true"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-body-secondary">
                                Belum ada riwayat pembelian dari supplier ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
