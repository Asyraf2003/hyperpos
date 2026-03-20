@extends('layouts.app')

@section('title', 'Procurement')
@section('heading', 'Procurement')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Daftar nota supplier</h4>
                        <p class="mb-0 text-muted">Interactive table procurement untuk admin.</p>
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
                            <tr>
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
                                        Tgl Kirim
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
                                        Grand Total
                                        <span class="ms-1 text-muted" data-sort-indicator="grand_total_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_paid_rupiah">
                                        Total Paid
                                        <span class="ms-1 text-muted" data-sort-indicator="total_paid_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="outstanding_rupiah">
                                        Outstanding
                                        <span class="ms-1 text-muted" data-sort-indicator="outstanding_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="receipt_count">
                                        Receipt
                                        <span class="ms-1 text-muted" data-sort-indicator="receipt_count">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_received_qty">
                                        Qty Diterima
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

        {{-- Asumsi path penempatan file partial Anda, silakan sesuaikan jika letak foldernya berbeda --}}
        @include('admin.procurement.supplier_invoices.partials.filter_drawer')
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
