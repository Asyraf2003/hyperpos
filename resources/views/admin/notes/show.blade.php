@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Identitas Nota Admin</h4>
                        <p class="mb-0 text-muted">
                            Ringkasan operasional note untuk admin tanpa memakai action kasir.
                        </p>
                    </div>

                    <div class="card-body">
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
                            <div class="text-muted">Status Nota</div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark text-uppercase">
                                    {{ $note['is_closed'] ? 'closed' : 'open' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="text-muted">Status Pembayaran</div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark text-uppercase">{{ $note['payment_status'] }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-start py-2">
                            <div class="text-muted">Jumlah Rincian</div>
                            <div class="text-end fw-semibold">{{ count($note['rows']) }}</div>
                        </div>
                    </div>
                </div>

                @if ($note['is_closed'])
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-bold mb-1">Status Operasional</div>
                            <div class="text-muted small">
                                Nota ini sedang ditutup. Step berikutnya akan menambahkan affordance admin untuk membuka ulang note dari halaman ini.
                            </div>
                        </div>
                    </div>
                @endif

                @include('cashier.notes.partials.correction-history')
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-1">Ringkasan Pembayaran</h4>
                    <p class="mb-0 text-muted">Ringkasan angka note untuk admin.</p>
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
        </div>
    </div>
</div>
@endsection
