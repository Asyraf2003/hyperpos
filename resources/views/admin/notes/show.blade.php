@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('admin.notes.index'))

@section('content')
<div class="page-content">
    <div class="mb-4">
        <div class="small text-muted text-uppercase fw-semibold">Admin Nota Overview</div>
        <h3 class="mb-1">Detail Nota Admin</h3>
        <div class="text-muted">
            Admin membaca nota dari ringkasan line dan status pembayaran aktual, tanpa memakai aksi kerja kasir.
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h4 class="card-title mb-1">Identitas Nota Admin</h4>
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

                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Daftar Line Nota</h4>
                        <p class="mb-0 text-muted">
                            Tampilan read-only untuk melihat posisi line Open, Close, dan Refund tanpa aksi kasir.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Line</th>
                                        <th>Tipe</th>
                                        <th>Status Line</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-end">Sudah Dibayar</th>
                                        <th class="text-end">Refund</th>
                                        <th class="text-end">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($note['rows'] as $row)
                                        <tr>
                                            <td>{{ $row['line_no'] }}</td>
                                            <td>{{ $row['type_label'] }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border text-uppercase">
                                                    {{ $row['line_status'] ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format((int) ($row['refunded_rupiah'] ?? 0), 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Belum ada line pada nota ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

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

                <div class="card">
                    <div class="card-body">
                        <div class="fw-bold mb-1">Status Operasional</div>

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
                                        Buka Ulang Note
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
