@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Detail Nota Pemasok')
@section('heading', 'Detail Nota Pemasok')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Rincian Nota</h4>
                        <p class="mb-0 text-muted">Daftar item pembelian yang tercatat pada nota pemasok ini.</p>
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
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Rincian</th>
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
                                                Belum ada rincian nota.
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
                                <p class="mb-0 text-muted">Data utama dan status keuangan nota pemasok.</p>
                            </div>

                            @if ($linesView !== [] && (int) ($summaryView['receipt_count'] ?? 0) < 1)
                                <a href="#receipt-form-section" class="btn btn-sm btn-primary">
                                    Terima Barang
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Status Kebijakan</small>
                            <span class="badge {{ $policyView['badge_class'] }}">{{ $policyView['label'] }}</span>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Aksi yang Diizinkan</small>

                            @if ($policyView['allowed_actions'] === [])
                                <div class="text-muted">Tidak ada aksi yang tersedia.</div>
                            @else
                                <ul class="mb-0 ps-3">
                                    @foreach ($policyView['allowed_actions'] as $actionLabel)
                                        <li>{{ $actionLabel }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Alasan Penguncian</small>

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
                            <small class="text-muted d-block">Nomor Faktur</small>
                            <strong>{{ $summaryView['nomor_faktur'] !== '' ? $summaryView['nomor_faktur'] : '-' }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">ID Nota Internal</small>
                            <strong>{{ $summaryView['supplier_invoice_id'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Pemasok Saat Ini</small>
                            <strong>{{ $summaryView['supplier_nama_pt_pengirim_current'] !== '' ? $summaryView['supplier_nama_pt_pengirim_current'] : '-' }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Saat Nota Dibuat</small>
                            <strong>{{ $summaryView['supplier_nama_pt_pengirim_snapshot'] !== '' ? $summaryView['supplier_nama_pt_pengirim_snapshot'] : '-' }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Tanggal Pengiriman</small>
                            <strong>{{ $summaryView['shipment_date'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Tanggal Jatuh Tempo</small>
                            <strong>{{ $summaryView['due_date'] }}</strong>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block">Total Nota</small>
                            <strong>{{ $summaryView['grand_total_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Total Dibayar</small>
                            <strong>{{ $summaryView['total_paid_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Sisa Tagihan</small>
                            <strong>{{ $summaryView['outstanding_label'] }}</strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Jumlah Penerimaan</small>
                            <strong>{{ $summaryView['receipt_count'] }}</strong>
                        </div>

                        <div>
                            <small class="text-muted d-block">Total Kuantitas Diterima</small>
                            <strong>{{ $summaryView['total_received_qty'] }}</strong>
                        </div>
                    </div>
                </div>

                @if ($linesView !== [] && (int) ($summaryView['receipt_count'] ?? 0) < 1)
                    <div class="card" id="receipt-form-section">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Terima Barang</h4>
                            <p class="mb-0 text-muted">
                                Gunakan checklist ini jika seluruh barang pada nota sudah datang sesuai jumlah invoice.
                            </p>
                        </div>

                        <div class="card-body">
                            @error('supplier_receipt')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <form
                                action="{{ route('admin.procurement.supplier-invoices.receive', ['supplierInvoiceId' => $summaryView['supplier_invoice_id']]) }}"
                                method="post"
                                id="supplier-receipt-form"
                            >
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="tanggal_terima" class="form-label">Tanggal Terima</label>
                                    <input
                                        type="date"
                                        data-ui-date="single"
                                        id="tanggal_terima"
                                        name="tanggal_terima"
                                        value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                        class="form-control @error('tanggal_terima') is-invalid @enderror"
                                        required
                                    >
                                    @error('tanggal_terima')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="border rounded p-3 bg-light-subtle">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            value="1"
                                            id="confirm_full_receive"
                                            data-confirm-full-receive
                                        >
                                        <label class="form-check-label" for="confirm_full_receive">
                                            Saya konfirmasi seluruh barang pada nota ini sudah diterima lengkap sesuai jumlah invoice.
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-danger d-none mt-3 mb-0" data-receipt-form-error>
                                    Centang konfirmasi penerimaan penuh sebelum menyimpan penerimaan barang.
                                </div>

                                @foreach ($linesView as $index => $line)
                                    <input
                                        type="hidden"
                                        name="lines[{{ $index }}][supplier_invoice_line_id]"
                                        value="{{ $line['supplier_invoice_line_id'] ?? '' }}"
                                        data-receipt-line-id
                                    >
                                    <input
                                        type="hidden"
                                        name="lines[{{ $index }}][qty_diterima]"
                                        value="{{ $line['qty_pcs'] }}"
                                        data-receipt-line-qty
                                    >
                                @endforeach

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        Simpan Penerimaan Barang
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif ((int) ($summaryView['receipt_count'] ?? 0) > 0)
                    <div class="card" id="receipt-form-section">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Status Penerimaan Barang</h4>
                            <p class="mb-0 text-muted">
                                Barang pada nota ini sudah diterima dan dicatat ke stok.
                            </p>
                        </div>

                        <div class="card-body">
                            <div class="alert alert-success mb-0">
                                Penerimaan barang sudah tercatat.
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card" id="payment-form-section">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Catat Pembayaran</h4>
                        <p class="mb-0 text-muted">Pembayaran pemasok dicatat secara eksplisit per nota.</p>
                    </div>

                    <div class="card-body">
                        @error('supplier_payment')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @if ($summaryView['can_record_payment'])
                            <form action="{{ route('admin.procurement.supplier-invoices.payments.store', ['supplierInvoiceId' => $summaryView['supplier_invoice_id']]) }}" method="post">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="payment_date" class="form-label">Tanggal Pembayaran</label>
                                    <input
                                        type="date"
                                        data-ui-date="single"
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

                                <div class="form-group mb-4" data-money-input-group>
                                    <label for="amount_display" class="form-label">Nominal Pembayaran</label>

                                    <input
                                        type="hidden"
                                        id="amount"
                                        name="amount"
                                        value="{{ old('amount', $summaryView['outstanding_amount']) }}"
                                        data-money-raw
                                    >

                                    <input
                                        type="text"
                                        id="amount_display"
                                        value="{{ old('amount', $summaryView['outstanding_amount']) }}"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        placeholder="Contoh: 150.000"
                                        inputmode="numeric"
                                        data-money-display
                                        required
                                    >

                                    @error('amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Simpan Pembayaran
                                </button>
                            </form>
                        @else
                            <div class="text-muted">Nota pemasok ini sudah lunas. Tidak ada pembayaran tambahan yang bisa dicatat.</div>
                        @endif
                    </div>
                </div>

                <div class="card" id="payment-proof-section">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Bukti Pembayaran</h4>
                        <p class="mb-0 text-muted">Unggah bukti ke baris pembayaran yang sudah tercatat. Maksimal 3 file per unggahan dan bisa unggah lagi untuk pembayaran yang sama.</p>
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
                            <div class="text-muted">Belum ada pembayaran pemasok.</div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($paymentsView as $payment)
                                    <div class="border rounded p-3">
                                        <div class="mb-2">
                                            <small class="text-muted d-block">ID Pembayaran</small>
                                            <strong>{{ $payment['id'] }}</strong>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted d-block">Tanggal Pembayaran</small>
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
                                                    Maksimal 3 file per unggahan. Format: JPG, JPEG, PNG, PDF. Maksimal 2 MB per file.
                                                </small>
                                            </div>

                                            <button type="submit" class="btn btn-outline-primary">
                                                Unggah Bukti
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
                                                            @if (str_starts_with($attachment['mime_type'], 'image/'))
                                                                <div class="mb-2">
                                                                    <img
                                                                        src="{{ route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => $attachment['id']]) }}"
                                                                        alt="{{ $attachment['original_filename'] }}"
                                                                        class="img-fluid rounded border"
                                                                        style="max-height: 180px;"
                                                                    >
                                                                </div>
                                                            @endif

                                                            <div><strong>{{ $attachment['original_filename'] }}</strong></div>
                                                            <div class="small text-muted">Tipe Berkas: {{ $attachment['mime_type'] }}</div>
                                                            <div class="small text-muted">Ukuran: {{ number_format($attachment['file_size_bytes']) }} byte</div>
                                                            <div class="small text-muted">Diunggah Pada: {{ $attachment['uploaded_at'] }}</div>
                                                            <div class="small text-muted">Diunggah Oleh: {{ $attachment['uploaded_by_actor_id'] }}</div>

                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                @if ($attachment['mime_type'] === 'application/pdf')
                                                                    <a
                                                                        href="{{ route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => $attachment['id']]) }}"
                                                                        target="_blank"
                                                                        rel="noopener"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                    >
                                                                        Lihat PDF
                                                                    </a>
                                                                @elseif (str_starts_with($attachment['mime_type'], 'image/'))
                                                                    <a
                                                                        href="{{ route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => $attachment['id']]) }}"
                                                                        target="_blank"
                                                                        rel="noopener"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                    >
                                                                        Lihat Gambar
                                                                    </a>
                                                                @else
                                                                    <a
                                                                        href="{{ route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => $attachment['id']]) }}"
                                                                        target="_blank"
                                                                        rel="noopener"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                    >
                                                                        Lihat Berkas
                                                                    </a>
                                                                @endif

                                                                <a
                                                                    href="{{ route('admin.procurement.supplier-payment-proof-attachments.show', ['attachmentId' => $attachment['id'], 'download' => 1]) }}"
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                >
                                                                    Unduh
                                                                </a>
                                                            </div>
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

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-money-input.js') }}"></script>
    <script>
        window.AdminMoneyInput?.bindBySelector(document);

        (() => {
            const form = document.getElementById('supplier-receipt-form');
            if (!form) return;

            const confirmInput = form.querySelector('[data-confirm-full-receive]');
            const errorBox = form.querySelector('[data-receipt-form-error]');

            form.addEventListener('submit', (event) => {
                const confirmed = Boolean(confirmInput?.checked);

                if (!confirmed) {
                    event.preventDefault();
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                    }
                    return;
                }

                if (errorBox) {
                    errorBox.classList.add('d-none');
                }
            });
        })();
    </script>
@endpush
