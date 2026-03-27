@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}" id="note-create-form">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Nama Customer</label>
                        <input
                            type="text"
                            class="form-control"
                            id="customer_name"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="transaction_date" class="form-label">Tanggal Nota</label>
                        <input
                            type="date"
                            class="form-control"
                            id="transaction_date"
                            name="transaction_date"
                            value="{{ old('transaction_date', $transactionDateDefault) }}"
                            required
                        >
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" id="add-service-row">Tambah Servis</button>
                    <button type="button" class="btn btn-outline-secondary" id="add-product-row">Tambah Produk</button>
                </div>

                <div class="mt-3" id="note-rows"></div>

                <div class="mt-4 rounded border p-3">
                    <div class="text-muted small">Grand Total Nota</div>
                    <div class="fs-4 fw-bold" id="grand-total-text">0</div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Nota</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('cashier.notes.partials.create-script')
@endpush
