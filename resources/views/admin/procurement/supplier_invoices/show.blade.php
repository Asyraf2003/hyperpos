@extends('layouts.app')

@section('title', 'Detail Nota Supplier')
@section('heading', 'Detail Nota Supplier')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Nota</h4>
                                <p class="mb-0 text-muted">Data header dan status finansial invoice supplier.</p>
                            </div>

                            <a href="{{ route('admin.procurement.supplier-invoices.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Policy State</small>
                            <span class="badge {{ $policyView['badge_class'] }}">{{ $policyView['label'] }}</span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Allowed Actions</small>

                            @if ($policyView['allowed_actions'] === [])
                                <div class="text-muted">Tidak ada action.</div>
                            @else
                                <ul class="mb-0 ps-3">
                                    @foreach ($policyView['allowed_actions'] as $actionLabel)
                                        <li>{{ $actionLabel }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Lock Reasons</small>

                            @if ($policyView['lock_reasons'] === [])
                                <div class="text-muted">Belum ada efek turunan primer.</div>
                            @else
                                <ul class="mb-0 ps-3">
                                    @foreach ($policyView['lock_reasons'] as $reasonLabel)
                                        <li>{{ $reasonLabel }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nomor Nota</small>
                            <strong>{{ $summaryView['supplier_invoice_id'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama PT</small>
                            <strong>{{ $summaryView['nama_pt_pengirim'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Tanggal Kirim</small>
                            <strong>{{ $summaryView['shipment_date'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Jatuh Tempo</small>
                            <strong>{{ $summaryView['due_date'] }}</strong>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block">Grand Total</small>
                            <strong>{{ $summaryView['grand_total_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Total Paid</small>
                            <strong>{{ $summaryView['total_paid_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Outstanding</small>
                            <strong>{{ $summaryView['outstanding_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Receipt Count</small>
                            <strong>{{ $summaryView['receipt_count'] }}</strong>
                        </div>

                        <div>
                            <small class="text-muted d-block">Total Received Qty</small>
                            <strong>{{ $summaryView['total_received_qty'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Line Invoice</h4>
                        <p class="mb-0 text-muted">Daftar item pembelian yang tercatat pada nota supplier ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Merek</th>
                                        <th>Ukuran</th>
                                        <th>Qty</th>
                                        <th>Unit Cost</th>
                                        <th>Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($linesView as $index => $line)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $line['kode_barang'] ?? '-' }}</td>
                                            <td>{{ $line['nama_barang'] }}</td>
                                            <td>{{ $line['merek'] }}</td>
                                            <td>{{ $line['ukuran'] ?? '-' }}</td>
                                            <td>{{ $line['qty_pcs'] }}</td>
                                            <td>{{ $line['unit_cost_label'] }}</td>
                                            <td>{{ $line['line_total_label'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                Tidak ada line invoice.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
