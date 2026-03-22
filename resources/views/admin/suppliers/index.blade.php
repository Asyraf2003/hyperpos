@extends('layouts.app')

@section('title', 'Pemasok')
@section('heading', 'Pemasok')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Ringkasan supplier</h4>
                        <p class="mb-0 text-muted">Supplier Summary List untuk admin.</p>
                    </div>

                    <form id="supplier-search-form" class="d-flex flex-column gap-1">
                        <input
                            type="text"
                            id="supplier-search-input"
                            class="form-control"
                            placeholder="Cari nama supplier"
                            autocomplete="off"
                        >
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="supplier-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="nama_pt_pengirim">
                                        Nama PT Pengirim
                                        <span class="ms-1 text-muted" data-sort-indicator="nama_pt_pengirim">↕</span>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="invoice_count">
                                        Jumlah Invoice
                                        <span class="ms-1 text-muted" data-sort-indicator="invoice_count">↕</span>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="outstanding_rupiah">
                                        Outstanding
                                        <span class="ms-1 text-muted" data-sort-indicator="outstanding_rupiah">↕</span>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="invoice_unpaid_count">
                                        Invoice Belum Lunas
                                        <span class="ms-1 text-muted" data-sort-indicator="invoice_unpaid_count">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="last_shipment_date">
                                        Pengiriman Terakhir
                                        <span class="ms-1 text-muted" data-sort-indicator="last_shipment_date">↕</span>
                                    </button>
                                </th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="supplier-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="supplier-table-summary" class="text-muted">Total: -</small>
                    <div id="supplier-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.supplierTableConfig = {
            endpoint: @json(route('admin.suppliers.table')),
            editBaseUrl: @json(url('/admin/suppliers'))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-suppliers-table.js') }}"></script>
@endpush