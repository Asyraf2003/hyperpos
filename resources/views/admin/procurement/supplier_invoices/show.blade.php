@extends('layouts.app')

@section('title', 'Detail Nota Supplier')
@section('heading', 'Detail Nota Supplier')

@section('content')
    <section class="section">
        <div class="row">
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

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Catat Pembayaran</h4>
                        <p class="mb-0 text-muted">Pembayaran supplier dicatat eksplisit per invoice.</p>
                    </div>

                    <div class="card-body">
                        @error('supplier_payment')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @if ($summaryView['can_record_payment'])
                            <form action="{{ route('admin.procurement.supplier-invoices.payments.store', ['supplierInvoiceId' => $summaryView['supplier_invoice_id']]) }}" method="post">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="payment_date" class="form-label">Tanggal Bayar</label>
                                    <input
                                        type="date"
                                        id="payment_date"
                                        name="payment_date"
                                        value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                        class="form-control @error('payment_date') is-invalid @enderror"
                                        required
                                    >
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="amount" class="form-label">Nominal Bayar</label>
                                    <input
                                        type="number"
                                        id="amount"
                                        name="amount"
                                        value="{{ old('amount', $summaryView['outstanding_amount']) }}"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        min="1"
                                        step="1"
                                        required
                                    >
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Simpan Pembayaran
                                </button>
                            </form>
                        @else
                            <div class="text-muted">Invoice supplier ini sudah lunas. Tidak ada pembayaran tambahan yang bisa dicatat.</div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Bukti Pembayaran</h4>
                        <p class="mb-0 text-muted">Upload bukti ke payment row yang sudah tercatat. Maksimal 3 file per upload dan boleh upload lagi untuk payment yang sama.</p>
                    </div>

                    <div class="card-body">
                        @error('supplier_payment_proof')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @error('proof_files')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @error('proof_files.*')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @if ($paymentsView === [])
                            <div class="text-muted">Belum ada pembayaran supplier.</div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($paymentsView as $payment)
                                    <div class="border rounded p-3">
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Payment ID</small>
                                            <strong>{{ $payment['id'] }}</strong>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted d-block">Tanggal Bayar</small>
                                            <strong>{{ $payment['paid_at'] }}</strong>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted d-block">Nominal</small>
                                            <strong>{{ $payment['amount_label'] }}</strong>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted d-block">Status Bukti</small>
                                            <strong>{{ $payment['proof_status_label'] }}</strong>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Jumlah Lampiran</small>
                                            <strong>{{ $payment['attachment_count'] }}</strong>
                                        </div>

                                        <form
                                            action="{{ route('admin.procurement.supplier-payments.proof.store', ['supplierPaymentId' => $payment['id']]) }}"
                                            method="post"
                                            enctype="multipart/form-data"
                                        >
                                            @csrf

                                            <div class="form-group mb-3">
                                                <label class="form-label" for="proof_files_{{ $payment['id'] }}">File Bukti</label>
                                                <input
                                                    type="file"
                                                    id="proof_files_{{ $payment['id'] }}"
                                                    name="proof_files[]"
                                                    class="form-control @error('proof_files') is-invalid @enderror @error('proof_files.*') is-invalid @enderror"
                                                    accept=".jpg,.jpeg,.png,.pdf"
                                                    multiple
                                                    required
                                                >
                                                <small class="text-muted d-block mt-1">
                                                    Maksimal 3 file per upload. Format: JPG, JPEG, PNG, PDF. Maksimal 2 MB per file.
                                                </small>
                                            </div>

                                            <button type="submit" class="btn btn-outline-primary">
                                                Upload Bukti
                                            </button>
                                        </form>

                                        <hr>

                                        <div>
                                            <small class="text-muted d-block mb-2">Riwayat Lampiran</small>

                                            @if ($payment['attachments'] === [])
                                                <div class="text-muted">Belum ada lampiran bukti.</div>
                                            @else
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach ($payment['attachments'] as $attachment)
                                                        <div class="border rounded p-2">
                                                            <div><strong>{{ $attachment['original_filename'] }}</strong></div>
                                                            <div class="small text-muted">Mime: {{ $attachment['mime_type'] }}</div>
                                                            <div class="small text-muted">Ukuran: {{ number_format($attachment['file_size_bytes']) }} byte</div>
                                                            <div class="small text-muted">Uploaded At: {{ $attachment['uploaded_at'] }}</div>
                                                            <div class="small text-muted">Actor: {{ $attachment['uploaded_by_actor_id'] }}</div>
                                                            <div class="small text-muted">Path: {{ $attachment['storage_path'] }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
