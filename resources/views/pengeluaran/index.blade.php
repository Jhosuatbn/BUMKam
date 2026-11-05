@extends('layouts.sneat-dashboard')
@section('title', 'Pengeluaran Operasional')
@section('content')
<div class="space-y-5">
    @if (session('success'))<div role="status" class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div role="alert" class="alert alert-danger" aria-live="assertive">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <section class="card overflow-hidden"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3"><div><h2 class="card-title mb-1">Pengeluaran Operasional</h2><p class="mb-0 text-body-secondary">Catatan kas keluar dengan alasan, tanggal, nominal, dan pencatat.</p></div>@if (auth()->user()?->peran === 'Staff')<button type="button" class="btn bumkam-green-action rounded-xl" data-bs-toggle="modal" data-bs-target="#catatPengeluaranModal"><i class="icon-base bx bx-plus me-1"></i>Catat Pengeluaran</button>@endif</div>
        <div class="card-body border-bottom"><form method="GET" class="flex flex-wrap items-end gap-3"><div><label class="form-label" for="start_date">Dari tanggal</label><input class="form-control" type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"></div><div><label class="form-label" for="end_date">Sampai tanggal</label><input class="form-control" type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"></div><button class="btn bumkam-green-action rounded-xl"><i class="icon-base bx bx-filter-alt me-1" aria-hidden="true"></i>Tampilkan</button></form></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nomor</th><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th>Dicatat Oleh</th></tr></thead><tbody>@forelse ($rows as $row)<tr><td class="fw-semibold">{{ $row->id_pengeluaran }}</td><td>{{ $row->tanggal_pengeluaran?->format('d/m/Y') }}</td><td>{{ $row->kategori }}</td><td>{{ $row->keterangan }}</td><td>Rp {{ number_format($row->nominal, 0, ',', '.') }}</td><td>{{ $row->pencatat?->nama_lengkap ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="py-5 text-center text-body-secondary">Belum ada catatan pengeluaran.</td></tr>@endforelse</tbody></table></div>
        <div class="card-footer flex flex-wrap justify-between gap-2"><span>{{ $rows->links() }}</span><strong>Total hasil filter: Rp {{ number_format($total, 0, ',', '.') }}</strong></div>
    </section>
</div>
@if (auth()->user()?->peran === 'Staff')
    <div class="modal fade" id="catatPengeluaranModal" tabindex="-1" aria-labelledby="catatPengeluaranModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form action="{{ route('pengeluaran.store') }}" method="POST" data-rupiah-form>@csrf
                <div class="modal-header"><div><h2 class="modal-title fs-5" id="catatPengeluaranModalLabel">Catat Pengeluaran Operasional</h2><p class="mb-0 mt-1 small text-body-secondary">Catatan pengeluaran tersimpan sebagai histori.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup" data-no-auto-icon></button></div>
                <div class="modal-body">
                    @if ($errors->any())<div role="alert" class="alert alert-danger">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
                    <div class="mb-3"><label for="tanggal_pengeluaran" class="form-label">Tanggal</label><input required type="date" name="tanggal_pengeluaran" id="tanggal_pengeluaran" value="{{ old('tanggal_pengeluaran', now()->toDateString()) }}" class="form-control"></div>
                    <div class="mb-3"><label for="kategori" class="form-label">Kategori</label><input required maxlength="100" name="kategori" id="kategori" value="{{ old('kategori') }}" class="form-control" placeholder="Contoh: listrik, transportasi, ATK"></div>
                    <div class="mb-3"><label for="keterangan" class="form-label">Keterangan</label><input required maxlength="255" name="keterangan" id="keterangan" value="{{ old('keterangan') }}" class="form-control"></div>
                    <label for="nominal" class="form-label">Nominal</label><div class="input-group"><span class="input-group-text">Rp</span><input required type="text" inputmode="numeric" autocomplete="off" name="nominal" id="nominal" value="{{ old('nominal') }}" class="form-control" placeholder="4.000.000" data-rupiah-input></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-xl" data-bs-dismiss="modal" data-no-auto-icon>Batal</button><button class="btn bumkam-green-action rounded-xl" type="submit" data-no-auto-icon>Simpan Pengeluaran</button></div>
            </form>
        </div></div>
    </div>
    <script>document.body.appendChild(document.getElementById('catatPengeluaranModal'));</script>
    <script>
    (() => {
        const input = document.querySelector('[data-rupiah-input]');
        const form = document.querySelector('[data-rupiah-form]');
        if (!input || !form) return;
        const format = () => { const digits = input.value.replace(/\D/g, ''); input.value = digits ? Number(digits).toLocaleString('id-ID') : ''; };
        format();
        input.addEventListener('input', format);
        form.addEventListener('submit', () => { input.value = input.value.replace(/\D/g, ''); });
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('catatPengeluaranModal')).show());
        @endif
    })();
    </script>
@endif
@endsection
