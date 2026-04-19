@if (($workspaceMode ?? 'create') === 'edit' && ($canShowRefundModal ?? false))
    <div class="modal fade" id="workspace-refund-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Refund Nota</h5>
                        <p class="mb-0 text-muted small">
                            Pilih line close yang ingin direfund, lalu catat nominal dan alasan refund.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <form method="POST" action="{{ $refundAction }}">
                    @csrf

                    <div class="modal-body">
                        @if ($errors->has('refund') || $errors->has('selected_row_ids') || $errors->has('customer_payment_id') || $errors->has('amount_rupiah') || $errors->has('refunded_at') || $errors->has('reason'))
                            <div class="alert alert-danger py-2 px-3 mb-4">
                                @if ($errors->has('refund'))
                                    <div>{{ $errors->first('refund') }}</div>
                                @endif
                                @if ($errors->has('selected_row_ids'))
                                    <div>{{ $errors->first('selected_row_ids') }}</div>
                                @endif
                                @if ($errors->has('customer_payment_id'))
                                    <div>{{ $errors->first('customer_payment_id') }}</div>
                                @endif
                                @if ($errors->has('amount_rupiah'))
                                    <div>{{ $errors->first('amount_rupiah') }}</div>
                                @endif
                                @if ($errors->has('refunded_at'))
                                    <div>{{ $errors->first('refunded_at') }}</div>
                                @endif
                                @if ($errors->has('reason'))
                                    <div>{{ $errors->first('reason') }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="border rounded p-3 mb-4">
                            <div class="fw-semibold mb-2">Line Close yang Bisa Direfund</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach ($workspaceRefundRows as $row)
                                    <label class="border rounded px-3 py-2 d-flex align-items-start gap-2">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-1"
                                            name="selected_row_ids[]"
                                            value="{{ $row['id'] }}"
                                            {{ in_array($row['id'], old('selected_row_ids', []), true) ? 'checked' : '' }}
                                        >
                                        <span>
                                            <span class="d-block fw-semibold">
                                                Line {{ $row['line_no'] }} · {{ $row['type_label'] }}
                                            </span>
                                            <small class="text-muted d-block">
                                                Status: {{ $row['line_status'] }} ·
                                                Dibayar: {{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }} ·
                                                Subtotal: {{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}
                                            </small>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="workspace_refund_customer_payment_id" class="form-label">Sumber Pembayaran Histori</label>
                            <select
                                id="workspace_refund_customer_payment_id"
                                name="customer_payment_id"
                                class="form-select"
                                required
                            >
                                @foreach (($refundPaymentOptions ?? []) as $option)
                                    <option
                                        value="{{ $option['value'] }}"
                                        {{ old('customer_payment_id') === $option['value'] ? 'selected' : '' }}
                                    >
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="workspace_refund_refunded_at" class="form-label">Tanggal Refund</label>
                            <input
                                type="date"
                                data-ui-date="single"
                                id="workspace_refund_refunded_at"
                                name="refunded_at"
                                value="{{ old('refunded_at', $refundDateDefault) }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="form-group mb-4">
                            <label for="workspace_refund_amount_rupiah" class="form-label">Nominal Refund</label>
                            <input
                                type="number"
                                min="1"
                                id="workspace_refund_amount_rupiah"
                                name="amount_rupiah"
                                value="{{ old('amount_rupiah') }}"
                                class="form-control"
                                placeholder="Isi nominal refund"
                                required
                            >
                        </div>

                        <div class="form-group mb-0">
                            <label for="workspace_refund_reason" class="form-label">Alasan Refund</label>
                            <textarea
                                id="workspace_refund_reason"
                                name="reason"
                                rows="3"
                                class="form-control"
                                placeholder="Jelaskan alasan refund"
                                required
                            >{{ old('reason') }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="ui-form-actions w-100 justify-content-between">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Catat Refund
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
