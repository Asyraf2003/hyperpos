@extends('layouts.app')

@section('title', 'Edit Product')
@section('heading', 'Edit Product')

@section('content')
    <section class="section">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <h4 class="card-title mb-1">Edit Product</h4>
                                <p class="mb-0 text-muted">
                                    Ubah data identitas produk.
                                </p>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a href="{{ route('admin.products.stock.edit', ['productId' => $product->id()]) }}" class="btn btn-warning">
                                    Ubah Stok
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.products.update', ['productId' => $product->id()]) }}" method="post" id="product-master-form" data-product-master-form="1">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="kode_barang" class="form-label">Kode Barang</label>
                                        <input
                                            type="text"
                                            id="kode_barang"
                                            name="kode_barang"
                                            value="{{ old('kode_barang', $product->kodeBarang()) }}"
                                            class="form-control @error('kode_barang') is-invalid @enderror"
                                            placeholder="Opsional"
                                        >
                                        @error('kode_barang')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="nama_barang" class="form-label">Nama Barang</label>
                                        <input
                                            type="text"
                                            id="nama_barang"
                                            name="nama_barang"
                                            value="{{ old('nama_barang', $product->namaBarang()) }}"
                                            class="form-control @error('nama_barang') is-invalid @enderror"
                                            placeholder="Contoh: Ban Luar"
                                            required
                                        >
                                        @error('nama_barang')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="merek" class="form-label">Merek</label>
                                        <input
                                            type="text"
                                            id="merek"
                                            name="merek"
                                            value="{{ old('merek', $product->merek()) }}"
                                            class="form-control @error('merek') is-invalid @enderror"
                                            placeholder="Contoh: Federal"
                                            required
                                        >
                                        @error('merek')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="ukuran" class="form-label">Ukuran</label>
                                        <input
                                            type="number"
                                            id="ukuran"
                                            name="ukuran"
                                            value="{{ old('ukuran', $product->ukuran()) }}"
                                            class="form-control @error('ukuran') is-invalid @enderror"
                                            placeholder="Opsional"
                                            min="0"
                                            step="1"
                                        >
                                        @error('ukuran')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4" data-money-input-group>
                                        <label for="harga_jual_display" class="form-label">Harga Jual</label>

                                        <input
                                            type="hidden"
                                            id="harga_jual"
                                            name="harga_jual"
                                            value="{{ old('harga_jual', $product->hargaJual()->amount()) }}"
                                            data-money-raw
                                        >

                                        <input
                                            type="text"
                                            id="harga_jual_display"
                                            value="{{ old('harga_jual', $product->hargaJual()->amount()) }}"
                                            class="form-control @error('harga_jual') is-invalid @enderror"
                                            placeholder="Contoh: 15.000"
                                            inputmode="numeric"
                                            data-money-display
                                            required
                                        >

                                        @error('harga_jual')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-light-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-money-input.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/admin-product-master-form.js') }}"></script>
    <script>
        window.AdminMoneyInput?.bindBySelector(document);
    </script>
@endpush
