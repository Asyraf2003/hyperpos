@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Edit Nota Supplier')
@section('heading', 'Edit Nota Supplier')

@section('content')
    <section class="section">
        <form
            action="{{ route('admin.procurement.supplier-invoices.update', ['supplierInvoiceId' => $summary['supplier_invoice_id']]) }}"
            method="post"
            novalidate
            id="procurement-edit-form"
            data-procurement-edit-form="1"
            data-procurement-draft-key="admin.procurement.edit-supplier-invoice.{{ $summary['supplier_invoice_id'] }}.draft.v1"
        >
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="card-title mb-1">Informasi Nota</h4>
                            <p class="mb-0 text-muted">
                                Edit pre-effect hanya boleh dilakukan sebelum ada receipt atau payment efektif.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-light-danger"
                                id="procurement-start-new"
                                onclick="window.localStorage.removeItem('admin.procurement.edit-supplier-invoice.{{ $summary['supplier_invoice_id'] }}.draft.v1'); window.location.reload();"
                            >
                                Muat Ulang Draft
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @error('supplier_invoice')
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="row g-3">
                        <div class="col-12 col-xl-4">
                            <div class="form-group">
                                <label for="nomor_faktur" class="form-label">Nomor Faktur</label>
                                <input
                                    type="text"
                                    id="nomor_faktur"
                                    name="nomor_faktur"
                                    value="{{ $formDefaults['nomor_faktur'] }}"
                                    class="form-control @error('nomor_faktur') is-invalid @enderror"
                                    placeholder="Contoh: INV-SUP-2026-0001"
                                    data-procurement-header-field
                                    required
                                >
                                @error('nomor_faktur')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="form-group">
                                <label for="nama_pt_pengirim" class="form-label">Nama PT Pengirim</label>
                                <input
                                    type="text"
                                    id="nama_pt_pengirim"
                                    name="nama_pt_pengirim"
                                    value="{{ $formDefaults['nama_pt_pengirim'] }}"
                                    class="form-control @error('nama_pt_pengirim') is-invalid @enderror"
                                    placeholder="Contoh: PT Federal Abadi"
                                    data-procurement-header-field
                                    required
                                >
                                @error('nama_pt_pengirim')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="form-group">
                                <label for="tanggal_pengiriman" class="form-label">Tanggal Pengiriman</label>
                                <input
                                    type="date"
                                    data-ui-date="single"
                                    id="tanggal_pengiriman"
                                    name="tanggal_pengiriman"
                                    value="{{ $formDefaults['tanggal_pengiriman'] }}"
                                    class="form-control @error('tanggal_pengiriman') is-invalid @enderror"
                                    data-procurement-header-field
                                    required
                                >
                                @error('tanggal_pengiriman')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="card-title mb-1">Rincian Nota</h4>
                            <p class="mb-0 text-muted">
                                Cari produk berdasarkan nama, merek, ukuran, atau kode. Jika produk belum ada, buka form product lewat tombol di atas.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" id="add-procurement-line" class="btn btn-primary">
                                Tambah Rincian
                            </button>

                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan Nota
                            </button>

                            <a
                                href="{{ route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => $summary['supplier_invoice_id']]) }}"
                                class="btn btn-light-secondary"
                            >
                                Batal
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-none d-xl-grid text-muted small fw-semibold border-bottom pb-2 mb-3"
                        style="grid-template-columns: 72px minmax(0, 1.8fr) 160px 220px 96px; gap: 16px;">
                        <div>Baris</div>
                        <div>Produk</div>
                        <div>Qty (Pcs)</div>
                        <div>Total Rincian</div>
                        <div class="text-center">Aksi</div>
                    </div>

                    <div id="procurement-line-items" data-next-index="{{ count($lineItemsView) }}" class="d-flex flex-column gap-3">
                        @foreach ($lineItemsView as $lineView)
                            <div class="border rounded p-3" data-line-item>
                                <input
                                    type="hidden"
                                    name="lines[{{ $lineView['index'] }}][line_no]"
                                    value="{{ $lineView['line_no'] }}"
                                    data-line-no
                                >

                                <div class="d-flex flex-column d-xl-grid gap-3 align-items-start"
                                    style="grid-template-columns: 72px minmax(0, 1.8fr) 160px 220px 96px;">
                                    <div class="w-100">
                                        <label class="form-label d-xl-none">Baris</label>
                                        <div class="border rounded px-3 py-2 bg-light fw-semibold text-center">
                                            <span data-line-label>{{ $lineView['line_no'] }}</span>
                                        </div>
                                    </div>

                                    <div class="w-100 position-relative">
                                        <label class="form-label d-xl-none">Produk</label>

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
                                            class="list-group position-absolute w-100 shadow-sm d-none mt-1"
                                            style="z-index: 20;"
                                            data-product-results
                                        ></div>

                                        @error('lines.' . $lineView['index'] . '.product_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="w-100">
                                        <label class="form-label d-xl-none">Jumlah (Pcs)</label>
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
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="w-100">
                                        <label class="form-label d-xl-none">Total Rincian (Rupiah)</label>

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

                                    <div class="w-100">
                                        <label class="form-label d-xl-none">Aksi</label>
                                        <button
                                            type="button"
                                            class="btn btn-light-danger w-100"
                                            data-remove-line
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="procurement-line-template">
                        <div class="border rounded p-3" data-line-item>
                            <input
                                type="hidden"
                                name="lines[__INDEX__][line_no]"
                                value="__LINE_NO__"
                                data-line-no
                            >

                            <div class="d-flex flex-column d-xl-grid gap-3 align-items-start"
                                style="grid-template-columns: 72px minmax(0, 1.8fr) 160px 220px 96px;">
                                <div class="w-100">
                                    <label class="form-label d-xl-none">Baris</label>
                                    <div class="border rounded px-3 py-2 bg-light fw-semibold text-center">
                                        <span data-line-label>__LINE_NO__</span>
                                    </div>
                                </div>

                                <div class="w-100 position-relative">
                                    <label class="form-label d-xl-none">Produk</label>

                                    <input
                                        type="hidden"
                                        name="lines[__INDEX__][product_id]"
                                        value=""
                                        data-product-id
                                    >

                                    <input
                                        type="text"
                                        value=""
                                        class="form-control"
                                        placeholder="Ketik minimal 2 huruf untuk mencari produk"
                                        autocomplete="off"
                                        data-product-search
                                    >

                                    <div
                                        class="list-group position-absolute w-100 shadow-sm d-none mt-1"
                                        style="z-index: 20;"
                                        data-product-results
                                    ></div>
                                </div>

                                <div class="w-100">
                                    <label class="form-label d-xl-none">Jumlah (Pcs)</label>
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

                                <div class="w-100">
                                    <label class="form-label d-xl-none">Total Rincian (Rupiah)</label>

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

                                <div class="w-100">
                                    <label class="form-label d-xl-none">Aksi</label>
                                    <button
                                        type="button"
                                        class="btn btn-light-danger w-100"
                                        data-remove-line
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
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
    <script src="{{ asset('assets/static/js/pages/admin-procurement-edit.js') }}"></script>
@endpush
