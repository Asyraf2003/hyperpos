@extends('layouts.app')

@section('title', 'Edit Nota Supplier')
@section('heading', 'Edit Nota Supplier')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="card-title mb-1">Edit Nota Supplier</h4>
                    <p class="mb-0 text-muted">
                        Halaman edit pre-effect sedang diaktifkan bertahap. Kontrak page dan guard policy sudah hidup dulu.
                    </p>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-light-primary border">
                    <div class="fw-semibold mb-2">Baseline Edit Pre-effect</div>
                    <div><strong>Nomor Faktur:</strong> {{ $summary['nomor_faktur'] ?? '-' }}</div>
                    <div><strong>Supplier:</strong> {{ $summary['supplier_nama_pt_pengirim_snapshot'] ?? '-' }}</div>
                    <div><strong>Tanggal Kirim:</strong> {{ $summary['shipment_date'] ?? '-' }}</div>
                    <div><strong>Jatuh Tempo:</strong> {{ $summary['due_date'] ?? '-' }}</div>
                    <div><strong>Jumlah Line:</strong> {{ count($lines) }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a
                        href="{{ route('admin.procurement.supplier-invoices.show', ['supplierInvoiceId' => $summary['supplier_invoice_id'] ?? '']) }}"
                        class="btn btn-light-secondary"
                    >
                        Kembali ke Detail
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
