@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="alert alert-info">
        Workspace baru sudah bisa menyimpan nota dan item secara atomik.
        Inline payment masih dikunci ke Skip pada step ini. Wiring pembayaran menyusul di step berikutnya.
    </div>

    @if ($errors->has('workspace'))
        <div class="alert alert-danger">
            {{ $errors->first('workspace') }}
        </div>
    @endif

    <form
        id="transaction-workspace-form"
        action="{{ route('notes.workspace.store') }}"
        method="POST"
        novalidate
    >
        @csrf

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <h4 class="card-title mb-1">Editor Nota</h4>
                                <p class="mb-0 text-muted">
                                    Jalur baru untuk menyatukan create nota dan keputusan pembayaran di satu workspace.
                                </p>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($quickAddOptions as $option)
                                    <button
                                        type="button"
                                        class="btn {{ $option['button_class'] }}"
                                        data-add-workspace-item
                                        data-entry-mode="{{ $option['entry_mode'] }}"
                                        data-part-source="{{ $option['part_source'] }}"
                                    >
                                        {{ $option['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="note_customer_name" class="form-label">Nama Customer</label>
                                <input
                                    id="note_customer_name"
                                    name="note[customer_name]"
                                    type="text"
                                    class="form-control"
                                    value="{{ $oldNote['customer_name'] ?? '' }}"
                                    placeholder="Contoh: Budi"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="note_customer_phone" class="form-label">No. HP Customer</label>
                                <input
                                    id="note_customer_phone"
                                    name="note[customer_phone]"
                                    type="text"
                                    class="form-control"
                                    value="{{ $oldNote['customer_phone'] ?? '' }}"
                                    placeholder="Contoh: 0812xxxx"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="note_transaction_date" class="form-label">Tanggal Nota</label>
                                <input
                                    id="note_transaction_date"
                                    name="note[transaction_date]"
                                    type="date"
                                    class="form-control"
                                    value="{{ $oldNote['transaction_date'] ?? date('Y-m-d') }}"
                                >
                            </div>
                        </div>

                        <hr class="my-4">

                        <div id="workspace-items" class="d-flex flex-column gap-3"></div>

                        <div id="workspace-empty-state" class="border rounded p-4 text-center text-muted">
                            Belum ada item. Gunakan tombol quick-add di atas untuk mulai menyusun nota.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Ringkasan Nota</h4>
                        <p class="mb-0 text-muted">Angka note-level untuk jalur baru.</p>
                    </div>

                    <div class="card-body">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted small">Grand Total</div>
                            <div class="fs-4 fw-bold" id="workspace-grand-total-text">0</div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted small">Dibayar Sekarang</div>
                            <div class="fs-5 fw-semibold" id="workspace-paid-now-text">0</div>
                        </div>

                        <div class="border rounded p-3">
                            <div class="text-muted small">Sisa Tagihan</div>
                            <div class="fs-5 fw-semibold" id="workspace-outstanding-text">0</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Keputusan Pembayaran</h4>
                        <p class="mb-0 text-muted">Step ini baru mengizinkan Skip. Wiring pay full/partial menyusul.</p>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label d-block">Aksi Setelah Simpan</label>

                            @foreach ($paymentDecisionOptions as $option)
                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="inline_payment[decision]"
                                        id="inline_payment_decision_{{ $option['value'] }}"
                                        value="{{ $option['value'] }}"
                                        {{ ($oldInlinePayment['decision'] ?? 'skip') === $option['value'] ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="inline_payment_decision_{{ $option['value'] }}">
                                        {{ $option['label'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <label for="inline_payment_method" class="form-label">Metode Bayar</label>
                            <select
                                id="inline_payment_method"
                                name="inline_payment[payment_method]"
                                class="form-select"
                            >
                                @foreach ($paymentMethodOptions as $option)
                                    <option
                                        value="{{ $option['value'] }}"
                                        {{ ($oldInlinePayment['payment_method'] ?? 'cash') === $option['value'] ? 'selected' : '' }}
                                    >
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="inline_payment_paid_at" class="form-label">Tanggal Bayar</label>
                            <input
                                id="inline_payment_paid_at"
                                name="inline_payment[paid_at]"
                                type="date"
                                class="form-control"
                                value="{{ $oldInlinePayment['paid_at'] ?? date('Y-m-d') }}"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="inline_payment_amount_paid_rupiah" class="form-label">Nominal Dibayar</label>
                            <input
                                id="inline_payment_amount_paid_rupiah"
                                name="inline_payment[amount_paid_rupiah]"
                                type="text"
                                inputmode="numeric"
                                class="form-control"
                                value="{{ $oldInlinePayment['amount_paid_rupiah'] ?? '' }}"
                                placeholder="Contoh: 150000"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="inline_payment_amount_received_rupiah" class="form-label">Uang Masuk</label>
                            <input
                                id="inline_payment_amount_received_rupiah"
                                name="inline_payment[amount_received_rupiah]"
                                type="text"
                                inputmode="numeric"
                                class="form-control"
                                value="{{ $oldInlinePayment['amount_received_rupiah'] ?? '' }}"
                                placeholder="Contoh: 200000"
                            >
                        </div>

                        <div class="mb-0">
                            <label for="inline_payment_notes" class="form-label">Catatan Pembayaran</label>
                            <textarea
                                id="inline_payment_notes"
                                name="inline_payment[notes]"
                                class="form-control"
                                rows="3"
                                placeholder="Catatan optional"
                            >{{ $oldInlinePayment['notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Nota</button>
                    <button type="button" class="btn btn-outline-primary" disabled>Simpan + Bayar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script id="transaction-workspace-config" type="application/json">
@json([
    'oldItems' => $oldItems,
    'partSourceOptions' => $partSourceOptions,
    'workspaceReadyForSubmit' => $workspaceReadyForSubmit,
])
</script>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-workspace.js') }}"></script>
@endpush
