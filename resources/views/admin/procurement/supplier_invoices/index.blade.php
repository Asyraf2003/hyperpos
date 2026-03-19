@extends('layouts.app')

@section('title', 'Procurement')
@section('heading', 'Procurement')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="card-title mb-1">Daftar nota supplier</h4>
                            <p class="mb-0 text-muted">Interactive table procurement untuk admin.</p>
                        </div>

                        <form id="procurement-search-form" class="d-flex flex-column gap-1">
                            <input
                                type="text"
                                id="procurement-search-input"
                                class="form-control"
                                placeholder="Cari nomor nota atau nama PT"
                                autocomplete="off"
                            >
                        </form>
                    </div>

                    <form id="procurement-filter-form" class="row g-2">
                        <div class="col-12 col-md-3">
                            <label for="shipment_date_from" class="form-label mb-1">Tgl Kirim Dari</label>
                            <input type="date" id="shipment_date_from" name="shipment_date_from" class="form-control">
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="shipment_date_to" class="form-label mb-1">Tgl Kirim Sampai</label>
                            <input type="date" id="shipment_date_to" name="shipment_date_to" class="form-control">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Terapkan</button>
                            <button type="button" id="reset-procurement-filter" class="btn btn-light-secondary">Reset</button>
                        </div>
                    </form>
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
                            </tr>
                        </thead>
                        <tbody id="procurement-invoice-table-body">
                            <tr><td colspan="10" class="text-center text-muted py-4">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="procurement-invoice-table-summary" class="text-muted">Total: -</small>
                    <div id="procurement-invoice-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.procurementInvoiceTableConfig = {
            endpoint: @json(route('admin.procurement.supplier-invoices.table'))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-procurement-invoices-table.js') }}"></script>
@endpush
