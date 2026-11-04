<section class="card overflow-hidden">
    @isset($heading)
        <div class="card-header">
            <h1 class="card-title mb-0">{{ $heading }}</h1>
        </div>
    @endisset
    <div class="card-body">
        @if ($errors->any())
            <div role="alert" class="alert alert-danger" aria-live="assertive">
                <div class="fw-semibold">Data supplier belum dapat disimpan:</div>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ $action }}" class="row g-3">
            @csrf
            @if($method ?? false) @method($method) @endif
            <div class="col-md-6">
                <label class="form-label" for="nama_supplier">Nama Supplier</label>
                <input id="nama_supplier" name="nama_supplier" required maxlength="150" class="form-control" value="{{ old('nama_supplier',$supplier->nama_supplier ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="kontak_person">Nama Kontak</label>
                <input id="kontak_person" name="kontak_person" maxlength="100" class="form-control" value="{{ old('kontak_person',$supplier->kontak_person ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="no_telepon">Nomor Telepon</label>
                <input id="no_telepon" name="no_telepon" maxlength="30" class="form-control" value="{{ old('no_telepon',$supplier->no_telepon ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email',$supplier->email ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" class="form-control">{{ old('alamat',$supplier->alamat ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan" rows="2" class="form-control">{{ old('catatan',$supplier->catatan ?? '') }}</textarea>
            </div>
            @isset($supplier)
                <div class="col-12">
                    <label class="form-label" for="is_active">Status</label>
                    <select id="is_active" name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active',$supplier->is_active))>Aktif</option>
                        <option value="0" @selected(!old('is_active',$supplier->is_active))>Nonaktif</option>
                    </select>
                </div>
            @endisset
            <div class="col-12 d-flex justify-content-end gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('supplier.index') }}">Batal</a>
                <button class="btn bumkam-green-action" data-no-auto-icon type="submit">Simpan Supplier</button>
            </div>
        </form>
    </div>
</section>
