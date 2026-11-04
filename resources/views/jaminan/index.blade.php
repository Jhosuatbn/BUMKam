@extends('layouts.sneat-dashboard')

@section('title', 'Manajemen Jaminan')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="alert alert-success">{{ session('success') }}</div>
        @endif

        <section class="card overflow-hidden" aria-labelledby="jaminan-list-heading">
            <div class="card-header border-b">
                <h2 id="jaminan-list-heading" class="card-title mb-1">Manajemen Jaminan</h2>
                <p class="mb-0 text-body-secondary">
                    @if (auth()->user()->peran === 'Pimpinan')
                        Tinjau dokumen dan berikan keputusan verifikasi.
                    @else
                        Lihat status jaminan, tindak lanjuti penolakan, dan catat pengembalian dokumen.
                    @endif
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID Jaminan / Pemesanan</th>
                            <th scope="col">Pelanggan</th>
                            <th scope="col">Jenis Dokumen</th>
                            <th scope="col">Nomor Dokumen</th>
                            <th scope="col">Petugas Penerima</th>
                            <th scope="col">Verifikasi</th>
                            <th scope="col">Pengembalian</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jaminan as $item)
                            @php
                                $statusVerifikasiClass = match ($item->status_verifikasi_label) {
                                    'Disetujui' => 'success',
                                    'Ditolak' => 'danger',
                                    default => 'warning',
                                };
                                $statusPengembalianClass = $item->status_pengembalian === 'Sudah Dikembalikan' ? 'success' : 'secondary';
                            @endphp
                            <tr>
                                <td class="text-nowrap"><strong>{{ $item->id_jaminan }}</strong><small class="d-block text-body-secondary">{{ $item->id_pemesanan }}</small></td>
                                <td>{{ $item->pemesanan?->pelanggan?->nama_pelanggan ?? '—' }}</td>
                                <td>{{ $item->jenis_dokumen === 'Surat Tanah' ? 'Sertifikat Tanah' : $item->jenis_dokumen }}</td>
                                <td>{{ $item->nomor_dokumen }}</td>
                                <td>{{ $item->penerima?->nama_lengkap ?? $item->penerima?->username ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $statusVerifikasiClass }}">{{ $item->status_verifikasi_label }}</span>
                                    @if ($item->alasan_penolakan)
                                        <small class="d-block text-danger">{{ $item->alasan_penolakan }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-label-{{ $statusPengembalianClass }}">{{ $item->status_pengembalian_label }}</span></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('jaminan.show', $item) }}"><i class="icon-base bx bx-show-alt me-1" aria-hidden="true"></i>Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-5 text-center text-body-secondary">Belum ada dokumen jaminan yang tercatat. Data jaminan dibuat bersama pengajuan pemesanan kredit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jaminan->hasPages())
                <div class="card-footer">{{ $jaminan->links() }}</div>
            @endif
        </section>
    </div>
@endsection
