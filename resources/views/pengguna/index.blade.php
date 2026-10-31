@extends('layouts.sneat-dashboard')

@section('title', 'Pengguna')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">{{ session('success') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="pengguna-list-heading">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="pengguna-list-heading" class="text-base font-semibold text-slate-900">Daftar Pengguna</h2>
                    <p class="mt-1 text-sm text-slate-500">Akun yang dapat mengakses sistem.</p>
                </div>
                @if (auth()->user()?->peran !== 'Pimpinan')
                    <a href="{{ route('pengguna.create') }}" class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"><i class="icon-base bx bx-plus" aria-hidden="true"></i>Tambah Pengguna</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[48rem] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">ID</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Username</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Nama Lengkap</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Peran</th>
                            <th scope="col" class="px-4 py-3 font-semibold sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pengguna as $item)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->id_pengguna }}</td>
                                <td class="px-4 py-3.5 sm:px-6">{{ $item->username }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-900 sm:px-6">{{ $item->nama_lengkap }}</td>
                                <td class="px-4 py-3.5 sm:px-6"><span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-900">{{ $item->peran }}</span></td>
                                <td class="px-4 py-3.5 sm:px-6">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                        <a href="{{ route('pengguna.show', $item->id_pengguna) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Detail</a>
                                        <a href="{{ route('pengguna.edit', $item->id_pengguna) }}" class="font-semibold text-blue-800 underline-offset-4 hover:underline">Edit</a>
                                        <form action="{{ route('pengguna.destroy', $item->id_pengguna) }}" method="POST" data-confirm-delete="Yakin ingin menghapus pengguna ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-9 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 transition hover:bg-rose-100"><svg class="mr-1 inline size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada data pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
