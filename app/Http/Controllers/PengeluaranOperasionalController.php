<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengeluaranOperasionalController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $rows = PengeluaranOperasional::with('pencatat')
            ->when(! empty($filters['start_date']), fn ($query) => $query->whereDate('tanggal_pengeluaran', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date']), fn ($query) => $query->whereDate('tanggal_pengeluaran', '<=', $filters['end_date']))
            ->latest('tanggal_pengeluaran')->paginate(25)->withQueryString();
        $total = PengeluaranOperasional::query()
            ->when(! empty($filters['start_date']), fn ($query) => $query->whereDate('tanggal_pengeluaran', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date']), fn ($query) => $query->whereDate('tanggal_pengeluaran', '<=', $filters['end_date']))
            ->sum('nominal');
        return view('pengeluaran.index', compact('rows', 'total'));
    }

    public function create()
    {
        $this->staffOnly();
        return view('pengeluaran.create');
    }

    public function store(Request $request)
    {
        $this->staffOnly();
        $data = $request->validate([
            'tanggal_pengeluaran' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:100'],
            'keterangan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999999'],
        ]);
        PengeluaranOperasional::create([
            ...$data,
            'id_pengeluaran' => 'OPR-' . now()->format('ymd') . '-' . Str::upper(Str::random(8)),
            'dicatat_oleh' => $request->user()->id_pengguna,
        ]);
        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran operasional berhasil dicatat.');
    }

    private function staffOnly(): void
    {
        abort_unless(auth()->user()?->peran === 'Staff', 403);
    }
}
