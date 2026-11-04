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
        $filters = $request->validate([
            'pengguna' => ['nullable', 'string', 'max:20'],
            'jenis' => ['nullable', 'string', 'max:40'],
            'dari' => ['bail', 'nullable', 'date_format:Y-m-d'],
            'sampai' => ['bail', 'nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'q' => ['nullable', 'string', 'max:150'],
        ]);

        $query = AuditTrail::query()->latest('created_at');
        $query->when(! empty($filters['pengguna']), fn ($builder) => $builder->where('id_pengguna', $filters['pengguna']));
        $query->when(! empty($filters['jenis']), fn ($builder) => $builder->where('jenis_aktivitas', $filters['jenis']));
        $query->when(! empty($filters['dari']), fn ($builder) => $builder->whereDate('created_at', '>=', $filters['dari']));
        $query->when(! empty($filters['sampai']), fn ($builder) => $builder->whereDate('created_at', '<=', $filters['sampai']));
        $query->when(! empty($filters['q']), function ($builder) use ($filters) {
            $term = '%'.$filters['q'].'%';
            $builder->where(fn ($search) => $search->where('nama_pengguna', 'like', $term)->orWhere('deskripsi', 'like', $term));
        });

        return view('audit-trail.index', [
            'aktivitas' => $query->paginate(20)->withQueryString(),
            'pengguna' => Pengguna::orderBy('nama_lengkap')->get(['id_pengguna', 'nama_lengkap']),
            'filter' => $filters,
        ]);
    }
}
