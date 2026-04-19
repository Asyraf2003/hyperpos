@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Ubah Stok Produk')
@section('heading', 'Ubah Stok Produk')
@section('back_url', route('admin.products.show', ['productId' => $product->id()]))

@section('content')
    <section class="section">
        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title mb-1">Informasi Produk</h4>
                            <p class="mb-0 text-muted">Penyesuaian stok keluar untuk produk terpilih.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="ui-key-value mb-3">
                            <small>Kode Barang</small>
                            <div>{{ $product->kodeBarang() ?: '-' }}</div>
                        </div>

                        <div class="ui-key-value mb-3">
                            <small>Nama Barang</small>
                            <div>{{ $product->namaBarang() }}</div>
                        </div>

                        <div class="ui-key-value mb-3">
                            <small>Merek</small>
                            <div>{{ $product->merek() }}</div>
                        </div>

                        <div class="ui-key-value mb-3">
                            <small>Ukuran</small>
                            <div>{{ $product->ukuran() ?? '-' }}</div>
                        </div>

                        <div class="ui-key-value">
                            <small>Stok Saat Ini</small>
                            <div>{{ $currentStock }}</div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="ui-form-actions">
                            <a href="{{ route('admin.products.edit', ['productId' => $product->id()]) }}" class="btn btn-light-primary">
                                Edit Identitas
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title mb-1">Form Penyesuaian Stok</h4>
                            <p class="mb-0 text-muted">
                                Bagian ini khusus untuk pencatatan pengurangan stok operasional melalui mutasi.
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        @error('stock_adjustment')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <form
                            action="{{ route('admin.products.stock-adjustments.store', ['productId' => $product->id()]) }}"
                            method="post"
                            id="product-stock-adjustment-form"
                            data-product-stock-adjustment-form="1"
                        >
                            @csrf

                            <div class="form-group mb-4">
                                <label for="adjusted_at" class="form-label">Tanggal Mutasi</label>
                                <input
                                    type="date"
                                    data-ui-date="single"
                                    id="adjusted_at"
                                    name="adjusted_at"
                                    value="{{ old('adjusted_at', now()->format('Y-m-d')) }}"
                                    class="form-control @error('adjusted_at') is-invalid @enderror"
                                    required
                                >
                                @error('adjusted_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="qty_issue" class="form-label">Kuantitas Keluar</label>
                                <input
                                    type="number"
                                    id="qty_issue"
                                    name="qty_issue"
                                    value="{{ old('qty_issue') }}"
                                    class="form-control @error('qty_issue') is-invalid @enderror"
                                    min="1"
                                    step="1"
                                    placeholder="Contoh: 2"
                                    required
                                >
                                @error('qty_issue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="reason" class="form-label">Alasan</label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="4"
                                    class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="Contoh: barang rusak, hilang, atau retur keluar"
                                    required
                                >{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="ui-form-actions">
                                <button type="submit" class="btn btn-primary">
                                    Catat Penyesuaian Stok
                                </button>
                                <a href="{{ route('admin.products.show', ['productId' => $product->id()]) }}" class="btn btn-light-secondary">
                                    Kembali ke Detail
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
