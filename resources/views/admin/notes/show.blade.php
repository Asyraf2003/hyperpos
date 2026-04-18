@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('admin.notes.index'))

@section('content')
<div class="page-content">
    <div class="mb-4">
        <div class="small text-muted text-uppercase fw-semibold">Admin Nota Workspace</div>
        <h3 class="mb-1">Detail Nota Admin</h3>
        <div class="text-muted">
            Admin membaca dan menangani nota dari status line aktual. Perbedaan admin dengan kasir hanya pada cakupan data, bukan pada kejelasan aksi.
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h4 class="card-title mb-1">Header Nota</h4>
                                <p class="mb-0 text-muted">
                                    Ringkasan identitas nota dan komposisi line untuk pembacaan admin.
                                </p>
                            </div>

                            <span class="badge bg-light text-dark border">
                                {{ count($note['rows']) }} Line
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">No. Nota</div>
                            <div class="text-end fw-semibold">{{ $note['id'] }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">Customer</div>
                            <div class="text-end fw-semibold">{{ $note['customer_name'] }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">No. Telp</div>
                            <div class="text-end fw-semibold">
                                {{ !empty($note['customer_phone']) ? $note['customer_phone'] : '-' }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">Tanggal Nota</div>
                            <div class="text-end fw-semibold">{{ $note['transaction_date'] }}</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">Status Pembayaran</div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark text-uppercase">{{ $note['payment_status'] }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2">
                            <div class="text-muted">Ringkasan Line</div>
                            <div class="text-end fw-semibold">
                                {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
                            </div>
                        </div>

                        <div class="border rounded p-3 mt-3">
                            <div class="small text-muted mb-2">Komposisi Status Line</div>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark border">
                                    Open: {{ (int) ($note['line_summary']['open_count'] ?? 0) }}
                                </span>
                                <span class="badge bg-light text-dark border">
                                    Close: {{ (int) ($note['line_summary']['close_count'] ?? 0) }}
                                </span>
                                <span class="badge bg-light text-dark border">
                                    Refund: {{ (int) ($note['line_summary']['refund_count'] ?? 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @include('cashier.notes.partials.note-rows-table')
                @include('cashier.notes.partials.correction-history')
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Ringkasan Pembayaran</h4>
                        <p class="mb-0 text-muted">Ringkasan angka nota untuk pembacaan admin.</p>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Grand Total</span>
                            <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Total Dialokasikan</span>
                            <strong>{{ number_format($note['total_allocated_rupiah'], 0, ',', '.') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted">Total Refund</span>
                            <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-3">
                            <span class="fw-semibold">Sisa Tagihan</span>
                            <strong class="fs-5">{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                @include('cashier.notes.partials.add-rows-form')

                @if ($note['can_show_payment_form'])
                    @include('cashier.notes.partials.payment-form')
                @endif

                @if ($note['can_show_refund_form'] ?? false)
                    @include('cashier.notes.partials.refund-form')
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="fw-bold mb-1">Status Operasional Admin</div>

                        @if ($note['note_state'] === 'closed')
                            <div class="text-muted small mb-3">
                                Nota ini sedang ditutup. Admin wajib memberi alasan sebelum membuka ulang nota.
                            </div>

                            <form method="POST" action="{{ route('admin.notes.reopen', ['noteId' => $note['id']]) }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="reopen-reason" class="form-label">Alasan Reopen</label>
                                    <textarea
                                        id="reopen-reason"
                                        name="reason"
                                        rows="3"
                                        class="form-control"
                                        required
                                    >{{ old('reason') }}</textarea>
                                </div>

                                <div class="d-grid d-sm-flex">
                                    <button type="submit" class="btn btn-warning">
                                        Buka Ulang Nota
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-muted small">
                                Reopen tidak diperlukan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-payment.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-refund.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-refund.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/note-line-actions.js') }}?v={{ filemtime(public_path('assets/static/js/pages/note-line-actions.js')) }}"></script>
@endpush
