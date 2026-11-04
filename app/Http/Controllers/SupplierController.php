<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditTrailWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    private function staffOnly(): void { abort_unless(auth()->user()?->peran === 'Staff', 403); }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['q'] ?? '');

        $suppliers = Supplier::query()
            ->withCount('barangMasuk')
            ->when($search !== '', fn ($query) => $query->where(function ($matches) use ($search) {
                $matches->where('nama_supplier', 'like', '%' . $search . '%')
                    ->orWhere('no_telepon', 'like', '%' . $search . '%');
            }))
            ->orderBy('nama_supplier')
            ->paginate(15)
            ->withQueryString();
        return view('supplier.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['barangMasuk' => fn ($q) => $q->with('details.barang', 'penerima')->latest('tanggal_masuk')]);
        return view('supplier.show', compact('supplier'));
    }

    public function create() { $this->staffOnly(); return view('supplier.create'); }

    public function store(Request $request, AuditTrailWriter $audit)
    {
        $this->staffOnly();
        $data = $request->validate(['nama_supplier' => ['required','string','max:150'], 'kontak_person' => ['nullable','string','max:100'], 'no_telepon' => ['nullable','string','max:30'], 'email' => ['nullable','email','max:255'], 'alamat' => ['nullable','string','max:2000'], 'catatan' => ['nullable','string','max:2000']]);
        $data['id_supplier'] = 'SUP-' . Str::upper(Str::random(12));
        $supplier = Supplier::create($data);
        $audit->record($request->user(), $request, 'create', 'Supplier', 'Supplier ditambahkan: ' . $supplier->nama_supplier);
        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier) { $this->staffOnly(); return view('supplier.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier, AuditTrailWriter $audit)
    {
        $this->staffOnly();
        $data = $request->validate(['nama_supplier' => ['required','string','max:150'], 'kontak_person' => ['nullable','string','max:100'], 'no_telepon' => ['nullable','string','max:30'], 'email' => ['nullable','email','max:255'], 'alamat' => ['nullable','string','max:2000'], 'catatan' => ['nullable','string','max:2000'], 'is_active' => ['required','boolean']]);
        $supplier->update($data);
        $audit->record($request->user(), $request, 'update', 'Supplier', 'Supplier diperbarui: ' . $supplier->nama_supplier);
        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier, AuditTrailWriter $audit)
    {
        $this->staffOnly();
        abort_if($supplier->barangMasuk()->exists(), 422, 'Supplier yang sudah memiliki riwayat barang masuk tidak dapat dihapus. Nonaktifkan saja supplier ini.');
        $name = $supplier->nama_supplier;
        $supplier->delete();
        $audit->record($request->user(), $request, 'delete', 'Supplier', 'Supplier dihapus: ' . $name);
        return back()->with('success', 'Supplier berhasil dihapus.');
    }
}
