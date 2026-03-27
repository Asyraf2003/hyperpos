@if ($note['outstanding_rupiah'] > 0)
    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ $paymentAction }}" id="note-payment-form">
                @csrf

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Pilih</th><th>Baris</th><th>Tipe</th><th>Status</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($note['rows'] as $row)
                                <tr>
                                    <td><input type="checkbox" name="selected_row_ids[]" value="{{ $row['id'] }}" data-payment-row data-subtotal="{{ $row['subtotal_rupiah'] }}"></td>
                                    <td>{{ $row['line_no'] }}</td>
                                    <td>{{ $row['type_label'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-end">{{ number_format($row['subtotal_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3"><label class="form-label">Metode</label><select class="form-select" name="payment_method" id="payment-method"><option value="cash">Cash</option><option value="tf">TF</option></select></div>
                    <div class="col-md-3"><label class="form-label">Tanggal Bayar</label><input type="date" class="form-control" name="paid_at" value="{{ old('paid_at', $paymentDateDefault) }}" required></div>
                    <div class="col-md-3"><label class="form-label">Uang Masuk</label><input type="number" min="1" class="form-control" name="amount_received" id="amount-received" value="{{ old('amount_received') }}"></div>
                    <div class="col-md-3"><label class="form-label">Dipilih Dibayar</label><input type="text" class="form-control" id="selected-payment-total" value="0" readonly></div>
                </div>

                <div class="mt-3">
                    <div class="small text-muted">Kembalian cash</div>
                    <div class="fw-bold" id="payment-change-text">0</div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="card mt-3"><div class="card-body"><span class="fw-bold">Nota sudah lunas.</span></div></div>
@endif
