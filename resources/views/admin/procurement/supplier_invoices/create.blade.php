@extends('layouts.app')

@section('title', 'Buat Nota Supplier')
@section('heading', 'Buat Nota Supplier')

@section('content')
    @php
        $oldLines = old('lines', [
            ['product_id' => '', 'qty_pcs' => '1', 'line_total_rupiah' => ''],
        ]);

        $buildProductLabel = static function ($product): string {
            $parts = [
                $product->namaBarang(),
                $product->merek(),
            ];

            if ($product->ukuran() !== null) {
                $parts[] = (string) $product->ukuran();
            }

            $label = implode(' - ', $parts);

            if ($product->kodeBarang() !== null) {
                $label .= ' (' . $product->kodeBarang() . ')';
            }

            return $label;
        };
    @endphp

    <section class="section">
        <form action="{{ route('admin.procurement.supplier-invoices.store') }}" method="post" novalidate>
            @csrf

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <h4 class="card-title mb-1">Line Nota</h4>
                                    <p class="mb-0 text-muted">
                                        Cari product dengan mengetik nama, merek, ukuran, atau kode.
                                    </p>
                                </div>

                                <button type="button" id="add-procurement-line" class="btn btn-primary">
                                    Tambah Line
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @error('supplier_invoice')
                                <div class="alert alert-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div id="procurement-line-items" data-next-index="{{ count($oldLines) }}">
                                @foreach ($oldLines as $index => $line)
                                    @php
                                        $selectedProductId = (string) ($line['product_id'] ?? '');
                                        $selectedProduct = collect($products)->first(
                                            fn ($product) => $product->id() === $selectedProductId
                                        );

                                        $selectedLabel = $selectedProduct ? $buildProductLabel($selectedProduct) : '';
                                        $lineTotalDisplay = isset($line['line_total_rupiah']) && $line['line_total_rupiah'] !== ''
                                            ? number_format((int) $line['line_total_rupiah'], 0, ',', '.')
                                            : '';
                                    @endphp

                                    <div class="border rounded p-3 mb-3" data-line-item>
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                            <div>
                                                <h6 class="mb-0">Line {{ $loop->iteration }}</h6>
                                                <small class="text-muted">Isi product, qty, dan nilai line.</small>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-light-danger" data-remove-line>
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group mb-3 position-relative">
                                                    <label class="form-label">Product</label>

                                                    <input
                                                        type="hidden"
                                                        name="lines[{{ $index }}][product_id]"
                                                        value="{{ $selectedProductId }}"
                                                        data-product-id
                                                    >

                                                    <input
                                                        type="text"
                                                        value="{{ $selectedLabel }}"
                                                        class="form-control @error('lines.' . $index . '.product_id') is-invalid @enderror"
                                                        placeholder="Ketik minimal 2 huruf untuk cari product"
                                                        autocomplete="off"
                                                        data-product-search
                                                    >

                                                    <div
                                                        class="list-group position-absolute w-100 shadow-sm d-none"
                                                        style="z-index: 20;"
                                                        data-product-results
                                                    ></div>

                                                    @error('lines.' . $index . '.product_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Qty PCS</label>
                                                    <input
                                                        type="text"
                                                        inputmode="numeric"
                                                        name="lines[{{ $index }}][qty_pcs]"
                                                        value="{{ (string) ($line['qty_pcs'] ?? '1') }}"
                                                        class="form-control @error('lines.' . $index . '.qty_pcs') is-invalid @enderror"
                                                        placeholder="Contoh: 2"
                                                        data-qty-input
                                                        required
                                                    >
                                                    @error('lines.' . $index . '.qty_pcs')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-8">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Line Total Rupiah</label>

                                                    <input
                                                        type="hidden"
                                                        name="lines[{{ $index }}][line_total_rupiah]"
                                                        value="{{ (string) ($line['line_total_rupiah'] ?? '') }}"
                                                        data-money-raw
                                                    >

                                                    <input
                                                        type="text"
                                                        inputmode="numeric"
                                                        value="{{ $lineTotalDisplay }}"
                                                        class="form-control @error('lines.' . $index . '.line_total_rupiah') is-invalid @enderror"
                                                        placeholder="Contoh: 150.000"
                                                        data-money-display
                                                        required
                                                    >

                                                    @error('lines.' . $index . '.line_total_rupiah')
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
                                            <h6 class="mb-0">Line Baru</h6>
                                            <small class="text-muted">Isi product, qty, dan nilai line.</small>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-light-danger" data-remove-line>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3 position-relative">
                                                <label class="form-label">Product</label>

                                                <input type="hidden" name="lines[__INDEX__][product_id]" value="" data-product-id>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Ketik minimal 2 huruf untuk cari product"
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
                                                <label class="form-label">Qty PCS</label>
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
                                                <label class="form-label">Line Total Rupiah</label>

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
                            <h4 class="card-title mb-1">Metadata Nota</h4>
                            <p class="mb-0 text-muted">Informasi pemasok dan tanggal transaksi.</p>
                        </div>

                        <div class="card-body">
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
                                    id="tanggal_pengiriman"
                                    name="tanggal_pengiriman"
                                    value="{{ old('tanggal_pengiriman') }}"
                                    class="form-control @error('tanggal_pengiriman') is-invalid @enderror"
                                    required
                                >
                                @error('tanggal_pengiriman')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="tanggal_terima" class="form-label">Tanggal Terima</label>
                                <input
                                    type="date"
                                    id="tanggal_terima"
                                    name="tanggal_terima"
                                    value="{{ old('tanggal_terima') }}"
                                    class="form-control @error('tanggal_terima') is-invalid @enderror"
                                >
                                <small class="text-muted">
                                    Dipakai saat auto receive aktif. Jika kosong, backend akan memakai tanggal pengiriman.
                                </small>
                                @error('tanggal_terima')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label d-block">Mode Receive</label>

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
                                            Auto receive
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Nota langsung masuk flow terima barang setelah dibuat.
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
                                        Nota disimpan tanpa auto receive.
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Nota Supplier
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
    <script>
        window.procurementCreateConfig = {
            lookupEndpoint: @json(route('admin.procurement.products.lookup'))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-procurement-create.js') }}"></script>
@endpush
