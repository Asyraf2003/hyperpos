@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Buat Nota Pemasok')
@section('heading', 'Buat Nota Pemasok')

@section('content')
    <section class="section">
        <form action="{{ route('admin.procurement.supplier-invoices.store') }}" method="post" novalidate>
            @csrf

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <h4 class="card-title mb-1">Rincian Nota</h4>
                                    <p class="mb-0 text-muted">
                                        Cari produk dengan mengetik nama, merek, ukuran, atau kode.
                                    </p>
                                </div>

                                <button type="button" id="add-procurement-line" class="btn btn-primary">
                                    Tambah Rincian
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @error('supplier_invoice')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div id="procurement-line-items" data-next-index="{{ count($lineItemsView) }}">
                                @foreach ($lineItemsView as $lineView)
                                    <div class="border rounded p-3 mb-3" data-line-item>
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                            <div>
                                                <h6 class="mb-0">Rincian <span data-line-label>{{ $lineView['line_no'] }}</span></h6>
                                                <small class="text-muted">Isi produk, kuantitas, dan total nilai rincian.</small>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-light-danger" data-remove-line>
                                                Hapus
                                            </button>
                                        </div>

                                        <input
                                            type="hidden"
                                            name="lines[{{ $lineView['index'] }}][line_no]"
                                            value="{{ $lineView['line_no'] }}"
                                            data-line-no
                                        >

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group mb-3 position-relative">
                                                    <label class="form-label">Produk</label>

                                                    <input
                                                        type="hidden"
                                                        name="lines[{{ $lineView['index'] }}][product_id]"
                                                        value="{{ $lineView['selected_product_id'] }}"
                                                        data-product-id
                                                    >

                                                    <input
                                                        type="text"
                                                        value="{{ $lineView['selected_label'] }}"
                                                        class="form-control @error('lines.' . $lineView['index'] . '.product_id') is-invalid @enderror"
                                                        placeholder="Ketik minimal 2 huruf untuk mencari produk"
                                                        autocomplete="off"
                                                        data-product-search
                                                    >

                                                    <div
                                                        class="list-group position-absolute w-100 shadow-sm d-none"
                                                        style="z-index: 20;"
                                                        data-product-results
                                                    ></div>

                                                    @error('lines.' . $lineView['index'] . '.product_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Jumlah (Pcs)</label>
                                                    <input
                                                        type="text"
                                                        inputmode="numeric"
                                                        name="lines[{{ $lineView['index'] }}][qty_pcs]"
                                                        value="{{ $lineView['qty_pcs'] }}"
                                                        class="form-control @error('lines.' . $lineView['index'] . '.qty_pcs') is-invalid @enderror"
                                                        placeholder="Contoh: 2"
                                                        data-qty-input
                                                        required
                                                    >
                                                    @error('lines.' . $lineView['index'] . '.qty_pcs')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-8">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Total Rincian (Rupiah)</label>

                                                    <input
                                                        type="hidden"
                                                        name="lines[{{ $lineView['index'] }}][line_total_rupiah]"
                                                        value="{{ $lineView['line_total_raw'] }}"
                                                        data-money-raw
                                                    >

                                                    <input
                                                        type="text"
                                                        inputmode="numeric"
                                                        value="{{ $lineView['line_total_display'] }}"
                                                        class="form-control @error('lines.' . $lineView['index'] . '.line_total_rupiah') is-invalid @enderror"
                                                        placeholder="Contoh: 150.000"
                                                        data-money-display
                                                        required
                                                    >

                                                    @error('lines.' . $lineView['index'] . '.line_total_rupiah')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <template id="procurement-line-template">
                                <div class="border rounded p-3 mb-3" data-line-item>
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div>
                                            <h6 class="mb-0">Rincian <span data-line-label>__LINE_NO__</span></h6>
                                            <small class="text-muted">Isi produk, kuantitas, dan total nilai rincian.</small>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-light-danger" data-remove-line>
                                            Hapus
                                        </button>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="lines[__INDEX__][line_no]"
                                        value="__LINE_NO__"
                                        data-line-no
                                    >

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3 position-relative">
                                                <label class="form-label">Produk</label>

                                                <input type="hidden" name="lines[__INDEX__][product_id]" value="" data-product-id>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Ketik minimal 2 huruf untuk mencari produk"
                                                    autocomplete="off"
                                                    data-product-search
                                                >

                                                <div
                                                    class="list-group position-absolute w-100 shadow-sm d-none"
                                                    style="z-index: 20;"
                                                    data-product-results
                                                ></div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Jumlah (Pcs)</label>
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    name="lines[__INDEX__][qty_pcs]"
                                                    value="1"
                                                    class="form-control"
                                                    placeholder="Contoh: 2"
                                                    data-qty-input
                                                    required
                                                >
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Total Rincian (Rupiah)</label>

                                                <input
                                                    type="hidden"
                                                    name="lines[__INDEX__][line_total_rupiah]"
                                                    value=""
                                                    data-money-raw
                                                >

                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    value=""
                                                    class="form-control"
                                                    placeholder="Contoh: 150.000"
                                                    data-money-display
                                                    required
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Informasi Nota</h4>
                            <p class="mb-0 text-muted">Informasi pemasok dan tanggal transaksi.</p>
                        </div>

                        <div class="card-body">
                            <div class="form-group mb-4">
                                <label for="nomor_faktur" class="form-label">Nomor Faktur</label>
                                <input
                                    type="text"
                                    id="nomor_faktur"
                                    name="nomor_faktur"
                                    value="{{ old('nomor_faktur') }}"
                                    class="form-control @error('nomor_faktur') is-invalid @enderror"
                                    placeholder="Contoh: INV-SUP-2026-0001"
                                    required
                                >
                                @error('nomor_faktur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="nama_pt_pengirim" class="form-label">Nama PT Pengirim</label>
                                <input
                                    type="text"
                                    id="nama_pt_pengirim"
                                    name="nama_pt_pengirim"
                                    value="{{ old('nama_pt_pengirim') }}"
                                    class="form-control @error('nama_pt_pengirim') is-invalid @enderror"
                                    placeholder="Contoh: PT Federal Abadi"
                                    required
                                >
                                @error('nama_pt_pengirim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="tanggal_pengiriman" class="form-label">Tanggal Pengiriman</label>
                                <input
                                    type="date"
                                    data-ui-date="single"
                                    id="tanggal_pengiriman"
                                    name="tanggal_pengiriman"
                                    value="{{ old('tanggal_pengiriman', now()->format('Y-m-d')) }}"
                                    class="form-control @error('tanggal_pengiriman') is-invalid @enderror"
                                    required
                                >
                                @error('tanggal_pengiriman')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="tanggal_terima" class="form-label">Tanggal Diterima</label>
                                <input
                                    type="date"
                                    data-ui-date="single"
                                    id="tanggal_terima"
                                    name="tanggal_terima"
                                    value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                    class="form-control @error('tanggal_terima') is-invalid @enderror"
                                >
                                <small class="text-muted">
                                    Dipakai saat penerimaan otomatis aktif. Jika kosong, sistem akan memakai tanggal pengiriman.
                                </small>
                                @error('tanggal_terima')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label d-block">Mode Penerimaan</label>

                                <div class="border rounded p-3 mb-2">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="auto_receive"
                                            id="auto_receive_yes"
                                            value="1"
                                            {{ old('auto_receive', '1') === '1' ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="auto_receive_yes">
                                            Terima barang otomatis
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Nota langsung masuk ke alur penerimaan barang setelah dibuat.
                                    </small>
                                </div>

                                <div class="border rounded p-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="auto_receive"
                                            id="auto_receive_no"
                                            value="0"
                                            {{ old('auto_receive') === '0' ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="auto_receive_no">
                                            Simpan nota saja
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Nota disimpan tanpa proses penerimaan barang otomatis.
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Nota Pemasok
                                </button>
                                <a href="{{ route('admin.procurement.supplier-invoices.index') }}" class="btn btn-light-secondary">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-money-input.js') }}"></script>
    <script>
        window.procurementCreateConfig = {
            lookupEndpoint: @json(route('admin.procurement.products.lookup'))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-procurement-create.js') }}"></script>
@endpush
