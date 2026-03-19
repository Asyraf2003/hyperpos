@extends('layouts.app')

@section('title', 'Buat Nota Supplier')
@section('heading', 'Buat Nota Supplier')

@section('content')
    @php
        $oldLines = old('lines', [
            ['product_id' => '', 'qty_pcs' => 1, 'line_total_rupiah' => ''],
        ]);
    @endphp

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <h4 class="card-title mb-1">Form nota supplier</h4>
                                <p class="mb-0 text-muted">
                                    Buat nota supplier baru dan kirim ke flow procurement resmi.
                                </p>
                            </div>

                            <a href="{{ route('admin.procurement.supplier-invoices.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @error('supplier_invoice')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                        @enderror

                        <form action="{{ route('admin.procurement.supplier-invoices.store') }}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-12 col-lg-6">
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
                                </div>

                                <div class="col-12 col-lg-3">
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
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="form-group mb-4">
                                        <label for="tanggal_terima" class="form-label">Tanggal Terima</label>
                                        <input
                                            type="date"
                                            id="tanggal_terima"
                                            name="tanggal_terima"
                                            value="{{ old('tanggal_terima') }}"
                                            class="form-control @error('tanggal_terima') is-invalid @enderror"
                                        >
                                        @error('tanggal_terima')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <div class="form-check">
                                            <input type="hidden" name="auto_receive" value="0">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="auto_receive"
                                                name="auto_receive"
                                                value="1"
                                                {{ old('auto_receive', '1') ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="auto_receive">
                                                Auto receive setelah nota dibuat
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h5 class="mb-1">Line Nota</h5>
                                    <p class="mb-0 text-muted">Pilih product, isi qty, dan total per line.</p>
                                </div>

                                <button type="button" id="add-procurement-line" class="btn btn-primary">
                                    Tambah Line
                                </button>
                            </div>

                            <div id="procurement-line-items" data-next-index="{{ count($oldLines) }}">
                                @foreach ($oldLines as $index => $line)
                                    <div class="border rounded p-3 mb-3" data-line-item>
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                            <h6 class="mb-0">Line {{ $loop->iteration }}</h6>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light-danger"
                                                data-remove-line
                                            >
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-lg-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Product</label>
                                                    <select
                                                        name="lines[{{ $index }}][product_id]"
                                                        class="form-select @error('lines.' . $index . '.product_id') is-invalid @enderror"
                                                        required
                                                    >
                                                        <option value="">Pilih product</option>
                                                        @foreach ($products as $product)
                                                            <option
                                                                value="{{ $product->id() }}"
                                                                {{ (string) ($line['product_id'] ?? '') === $product->id() ? 'selected' : '' }}
                                                            >
                                                                {{ $product->namaBarang() }} — {{ $product->merek() }}@if($product->kodeBarang()) ({{ $product->kodeBarang() }})@endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('lines.' . $index . '.product_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Qty PCS</label>
                                                    <input
                                                        type="number"
                                                        name="lines[{{ $index }}][qty_pcs]"
                                                        value="{{ $line['qty_pcs'] ?? 1 }}"
                                                        class="form-control @error('lines.' . $index . '.qty_pcs') is-invalid @enderror"
                                                        min="1"
                                                        step="1"
                                                        required
                                                    >
                                                    @error('lines.' . $index . '.qty_pcs')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Line Total Rupiah</label>
                                                    <input
                                                        type="number"
                                                        name="lines[{{ $index }}][line_total_rupiah]"
                                                        value="{{ $line['line_total_rupiah'] ?? '' }}"
                                                        class="form-control @error('lines.' . $index . '.line_total_rupiah') is-invalid @enderror"
                                                        min="1"
                                                        step="1"
                                                        placeholder="Contoh: 150000"
                                                        required
                                                    >
                                                    @error('lines.' . $index . '.line_total_rupiah')
                                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <h6 class="mb-0">Line Baru</h6>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light-danger"
                                            data-remove-line
                                        >
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Product</label>
                                                <select name="lines[__INDEX__][product_id]" class="form-select" required>
                                                    <option value="">Pilih product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id() }}">
                                                            {{ $product->namaBarang() }} — {{ $product->merek() }}@if($product->kodeBarang()) ({{ $product->kodeBarang() }})@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Qty PCS</label>
                                                <input
                                                    type="number"
                                                    name="lines[__INDEX__][qty_pcs]"
                                                    value="1"
                                                    class="form-control"
                                                    min="1"
                                                    step="1"
                                                    required
                                                >
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Line Total Rupiah</label>
                                                <input
                                                    type="number"
                                                    name="lines[__INDEX__][line_total_rupiah]"
                                                    class="form-control"
                                                    min="1"
                                                    step="1"
                                                    placeholder="Contoh: 150000"
                                                    required
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div class="d-flex justify-content-start gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Nota Supplier
                                </button>
                                <a href="{{ route('admin.procurement.supplier-invoices.index') }}" class="btn btn-light-secondary">
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
    <script>
        (() => {
            const container = document.getElementById('procurement-line-items');
            const addButton = document.getElementById('add-procurement-line');
            const template = document.getElementById('procurement-line-template');

            if (!container || !addButton || !template) {
                return;
            }

            let nextIndex = Number.parseInt(container.dataset.nextIndex || '0', 10);

            const updateRemoveButtons = () => {
                const items = container.querySelectorAll('[data-line-item]');
                items.forEach((item) => {
                    const button = item.querySelector('[data-remove-line]');
                    if (!button) {
                        return;
                    }

                    button.disabled = items.length === 1;
                });
            };

            addButton.addEventListener('click', () => {
                const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
                container.insertAdjacentHTML('beforeend', html);
                nextIndex += 1;
                updateRemoveButtons();
            });

            container.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-line]');
                if (!button) {
                    return;
                }

                const item = button.closest('[data-line-item]');
                if (!item) {
                    return;
                }

                const items = container.querySelectorAll('[data-line-item]');
                if (items.length <= 1) {
                    return;
                }

                item.remove();
                updateRemoveButtons();
            });

            updateRemoveButtons();
        })();
    </script>
@endpush
