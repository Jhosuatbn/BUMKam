@extends('layouts.sneat-dashboard')

@section('title', 'Detail Barang Masuk')

@section('content')
    <section class="card overflow-hidden" aria-labelledby="receipt-detail-heading">
        <div class="card-header d-flex align-items-start justify-content-between gap-3">
            <div class="min-w-0">
                <h1 id="receipt-detail-heading" class="card-title h4 mb-1">Detail Penerimaan</h1>
                <p class="mb-0 text-body-secondary">
                    {{ $receipt->id_barang_masuk }}
                    <span aria-hidden="true">·</span>
                    {{ $receipt->tanggal_masuk?->format('d/m/Y') }}
                </p>
            </div>
            <a class="btn bumkam-green-action d-inline-flex flex-shrink-0 align-items-center"
                href="{{ route('barang-masuk.index') }}">
                <i class="icon-base bx bx-arrow-back" aria-hidden="true"></i>
                Kembali
            </a>
        </div>

        <div class="card-body row g-3">
            <div class="col-md-4">
                <small class="text-body-secondary">Supplier</small>
                <div>{{ $receipt->supplier?->nama_supplier ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-body-secondary">Nomor Faktur</small>
                <div>{{ $receipt->nomor_faktur ?: '—' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-body-secondary">Petugas Penerima</small>
                <div>{{ $receipt->penerima?->nama_lengkap ?? $receipt->penerima?->username ?? '—' }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Barang</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Harga Satuan</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->details as $line)
                        <tr>
                            <td>{{ $line->barang?->nama_barang ?? '—' }}</td>
                            <td>{{ number_format($line->jumlah, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($line->harga_satuan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">{{ $receipt->catatan ?: 'Tidak ada catatan.' }}</div>
    </section>
@endsection
