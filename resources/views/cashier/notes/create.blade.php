@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 bg-light-primary">
                <div class="card-body py-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h5 class="mb-1">Buat Nota Baru</h5>
                            <p class="mb-0 text-muted">
                                Isi header nota dan tambahkan rincian produk atau servis. Setelah nota tersimpan,
                                pembayaran dilakukan di halaman detail nota.
                            </p>
                        </div>

                        <div class="text-lg-end">
                            <div class="small text-muted">Alur kerja</div>
                            <div class="fw-semibold">Simpan Nota → Buka Detail → Bayar / Skip</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <form method="POST" action="{{ $formAction }}" id="note-create-form">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Header Nota</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Nama Customer</label>
                                <input
                                    id="customer_name"
                                    name="customer_name"
                                    type="text"
                                    class="form-control"
                                    value="{{ old('customer_name') }}"
                                    placeholder="Contoh: Budi Santoso"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="col-md-4">
                                <label for="customer_phone" class="form-label">No Telp <span class="text-muted">(Opsional)</span></label>
                                <input
                                    id="customer_phone"
                                    name="customer_phone"
                                    type="text"
                                    class="form-control"
                                    value="{{ old('customer_phone') }}"
                                    placeholder="Contoh: 08123456789"
                                    inputmode="numeric"
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="col-md-4">
                                <label for="transaction_date" class="form-label">Tanggal Nota</label>
                                <input
                                    id="transaction_date"
                                    name="transaction_date"
                                    type="date"
                                    class="form-control"
                                    value="{{ old('transaction_date', $transactionDateDefault) }}"
                                    data-ui-date="single"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="card-title mb-0">Rincian Nota</h5>
                            <small class="text-muted">Tambahkan produk atau servis sesuai kebutuhan customer.</small>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary" id="add-service-row">
                                <i class="bi bi-tools me-1"></i> Tambah Servis
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="add-product-row">
                                <i class="bi bi-box-seam me-1"></i> Tambah Produk
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-muted">
                                Setiap rincian tetap dapat dipilih tipenya di dalam baris. Produk memakai harga master,
                                sedangkan servis diisi manual.
                            </div>
                        </div>

                        <div id="note-rows"></div>
                    </div>
                </div>

                <div class="card mt-4 border border-primary">
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <div class="col-md-8">
                                <div class="text-muted small">Grand Total Nota</div>
                                <div class="fs-3 fw-bold text-primary" id="grand-total-text">0</div>
                                <div class="small text-muted mt-1">
                                    Total ini berasal dari seluruh subtotal rincian yang ada di nota.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Simpan Nota
                                    </button>
                                </div>
                                <div class="small text-muted mt-2 text-md-end">
                                    Setelah simpan, Anda akan diarahkan ke detail nota.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script id="note-create-config" type="application/json">@json(['oldRows' => $oldRows, 'productOptions' => $productOptions])</script>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-create.js') }}"></script>
@endpush
