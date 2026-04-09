@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Nota Pengadaan')
@section('heading', 'Nota Pengadaan')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Daftar nota pengadaan</h4>
                        <p class="mb-0 text-muted">Tabel pengadaan interaktif untuk admin.</p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="procurement-search-form" class="d-flex flex-column gap-1">
                            <input
                                type="text"
                                id="procurement-search-input"
                                class="form-control"
                                placeholder="Cari nomor nota atau nama PT"
                                autocomplete="off"
                            >
                        </form>

                        <button type="button" id="open-procurement-filter" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.procurement.supplier-invoices.create') }}" class="btn btn-primary">
                            Buat Nota Supplier
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="procurement-invoice-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>Nota</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="nama_pt_pengirim">
                                        Nama PT
                                        <span class="ms-1 text-muted" data-sort-indicator="nama_pt_pengirim">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="shipment_date">
                                        Tanggal Kirim
                                        <span class="ms-1 text-muted" data-sort-indicator="shipment_date">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="due_date">
                                        Jatuh Tempo
                                        <span class="ms-1 text-muted" data-sort-indicator="due_date">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="grand_total_rupiah">
                                        Total Keseluruhan
                                        <span class="ms-1 text-muted" data-sort-indicator="grand_total_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_paid_rupiah">
                                        Total Dibayar
                                        <span class="ms-1 text-muted" data-sort-indicator="total_paid_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="outstanding_rupiah">
                                        Sisa Tagihan
                                        <span class="ms-1 text-muted" data-sort-indicator="outstanding_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="receipt_count">
                                        Penerimaan Barang
                                        <span class="ms-1 text-muted" data-sort-indicator="receipt_count">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_received_qty">
                                        Kuantitas Diterima
                                        <span class="ms-1 text-muted" data-sort-indicator="total_received_qty">↕</span>
                                    </button>
                                </th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="procurement-invoice-table-body">
                            <tr><td colspan="11" class="text-center text-muted py-4">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="procurement-invoice-table-summary" class="text-muted">Total: -</small>
                    <div id="procurement-invoice-table-pagination"></div>
                </div>
            </div>
        </div>

        @include('admin.procurement.supplier_invoices.partials.filter_drawer')

        <div
            class="modal fade"
            id="procurement-action-modal"
            tabindex="-1"
            aria-labelledby="procurement-action-modal-title"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div class="w-100">
                            <h3 class="modal-title fw-bold mb-1" id="procurement-action-modal-title">Aksi Nota Supplier</h3>
                            <p class="mb-0 text-muted fs-6" id="procurement-action-modal-subtitle">
                                Pilih tindakan untuk nota supplier.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body px-4 pb-4 pt-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <a
                                    href="#"
                                    id="procurement-action-detail-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4"
                                >
                                    <div class="fw-bold fs-5 mb-1">Detail Nota</div>
                                    <div class="small opacity-75">Lihat ringkasan nota, pembayaran, dan bukti bayar.</div>
                                </a>
                            </div>

                            <div class="col-12 col-md-4">
                                <a
                                    href="#"
                                    id="procurement-action-payment-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4"
                                >
                                    <div class="fw-bold fs-5 mb-1" id="procurement-action-payment-title">Catat Pembayaran</div>
                                    <div class="small opacity-75" id="procurement-action-payment-description">
                                        Buka bagian pembayaran pada detail nota.
                                    </div>
                                </a>
                            </div>

                            <div class="col-12 col-md-4">
                                <a
                                    href="#"
                                    id="procurement-action-proof-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4"
                                >
                                    <div class="fw-bold fs-5 mb-1" id="procurement-action-proof-title">Unggah Bukti Pembayaran</div>
                                    <div class="small opacity-75" id="procurement-action-proof-description">
                                        Buka bagian bukti pembayaran pada detail nota.
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="alert alert-light-secondary border mt-3 mb-0">
                            <small class="text-muted d-block">
                                Edit nota supplier belum ditampilkan dari index karena route dan policy edit belum dikunci final.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.procurementInvoiceTableConfig = {
            endpoint: @json(route('admin.procurement.supplier-invoices.table')),
            detailBaseUrl: @json(route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => '__ID__']))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-procurement-invoices-table.js') }}"></script>
@endpush
