<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->peran === 'Pimpinan', 403);
        $query = AuditTrail::query()->latest('created_at');
        $query->when($request->filled('pengguna'), fn ($builder) => $builder->where('id_pengguna', $request->string('pengguna')->toString()));
        $query->when($request->filled('jenis'), fn ($builder) => $builder->where('jenis_aktivitas', $request->string('jenis')->toString()));
        $query->when($request->filled('dari'), fn ($builder) => $builder->whereDate('created_at', '>=', $request->date('dari')));
        $query->when($request->filled('sampai'), fn ($builder) => $builder->whereDate('created_at', '<=', $request->date('sampai')));
        $query->when($request->filled('q'), function ($builder) use ($request) {
            $term = '%'.$request->string('q')->toString().'%';
            $builder->where(fn ($search) => $search->where('nama_pengguna', 'like', $term)->orWhere('deskripsi', 'like', $term));
        });

        return view('audit-trail.index', [
            'aktivitas' => $query->paginate(20)->withQueryString(),
            'pengguna' => Pengguna::orderBy('nama_lengkap')->get(['id_pengguna', 'nama_lengkap']),
            'filter' => $request->only('pengguna', 'jenis', 'dari', 'sampai', 'q'),
        ]);
    }
}
