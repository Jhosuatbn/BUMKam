@if ($errors->any())
    <div role="alert" class="alert alert-danger" aria-live="assertive">
        <div class="fw-semibold">Pengajuan belum dapat disimpan:</div>
        <ul class="mb-0 mt-2 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="mb-3">
    <label for="id_pemesanan" class="form-label">Pemesanan yang diretur</label>
    <select required id="id_pemesanan" name="id_pemesanan" class="form-select">
        <option value="">Pilih pemesanan</option>
        @foreach ($orders as $order)
            @php
                $items = $order->detailPemesanan->map(function ($detail) use ($order) {
                    $already = $order->returPelanggan->whereIn('status', ['Menunggu Persetujuan', 'Disetujui'])
                        ->flatMap(fn ($return) => $return->details)
                        ->where('id_detail_pemesanan', $detail->id_detail)
                        ->sum('jumlah');
                    return ['id' => $detail->id_detail, 'name' => $detail->barang?->nama_barang ?? $detail->id_barang, 'ordered' => (int) $detail->jumlah, 'available' => max(0, (int) $detail->jumlah - (int) $already)];
                })->filter(fn ($item) => $item['available'] > 0)->values();
            @endphp
            @if ($items->isNotEmpty())
                <option value="{{ $order->id_pemesanan }}" data-items="{{ $items->toJson() }}" @selected(old('id_pemesanan') === $order->id_pemesanan)>{{ $order->id_pemesanan }} · {{ $order->pelanggan?->nama_pelanggan ?? '-' }}</option>
            @endif
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label for="alasan" class="form-label">Alasan retur</label>
    <textarea required minlength="5" maxlength="2000" id="alasan" name="alasan" rows="3" class="form-control" placeholder="Jelaskan alasan pengembalian barang">{{ old('alasan') }}</textarea>
</div>
<div id="return-items" class="space-y-3"><p class="mb-0 text-sm text-body-secondary">Pilih pemesanan untuk memuat barang yang masih dapat diajukan retur.</p></div>
<script>
(() => {
    const order = document.getElementById('id_pemesanan');
    const target = document.getElementById('return-items');
    if (!order || !target) return;
    const previousItems = @json(old('items', []));
    const esc = (value) => String(value).replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    order.addEventListener('change', () => {
        target.replaceChildren();
        const selected = order.selectedOptions[0];
        if (!selected?.dataset.items) { target.innerHTML = '<p class="mb-0 text-sm text-body-secondary">Pilih pemesanan untuk memuat barang.</p>'; return; }
        JSON.parse(selected.dataset.items).forEach((item, index) => {
            const row = document.createElement('fieldset'); row.className = 'rounded-xl border border-slate-200 p-3';
            row.innerHTML = `<legend class="px-2 font-semibold">${esc(item.name)} · tersedia untuk retur ${item.available}</legend><label class="mb-3 flex items-center gap-2"><input type="checkbox" data-return-enable> Ajukan retur untuk barang ini</label><input type="hidden" name="items[${index}][id_detail]" value="${esc(item.id)}" disabled><div class="grid grid-cols-1 gap-3 sm:grid-cols-3"><div><label class="form-label">Jumlah</label><input class="form-control" type="number" name="items[${index}][jumlah]" min="1" max="${item.available}" value="1" required disabled></div><div><label class="form-label">Kondisi</label><select class="form-select" name="items[${index}][kondisi_barang]" required disabled><option>Layak Dijual</option><option>Rusak</option><option>Perlu Pemeriksaan</option></select></div><div><label class="form-label">Keterangan</label><input class="form-control" name="items[${index}][keterangan]" maxlength="1000" disabled></div></div>`;
            const checkbox = row.querySelector('[data-return-enable]');
            checkbox.addEventListener('change', (event) => row.querySelectorAll('input:not([type="checkbox"]), select').forEach((control) => { control.disabled = !event.target.checked; }));
            const previous = previousItems.find((value) => String(value.id_detail) === String(item.id));
            if (previous) { checkbox.checked = true; checkbox.dispatchEvent(new Event('change')); row.querySelector('[name$="[jumlah]"]').value = previous.jumlah ?? 1; row.querySelector('[name$="[kondisi_barang]"]').value = previous.kondisi_barang ?? 'Layak Dijual'; row.querySelector('[name$="[keterangan]"]').value = previous.keterangan ?? ''; }
            target.append(row);
        });
    });
    if (order.value) order.dispatchEvent(new Event('change'));
})();
</script>
