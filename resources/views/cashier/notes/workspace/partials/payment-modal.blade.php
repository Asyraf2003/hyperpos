<div class="modal fade" id="workspace-payment-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <input type="hidden" id="inline_payment_decision_hidden" name="inline_payment[decision]" value="skip">
            <input type="hidden" id="inline_payment_method_hidden" name="inline_payment[payment_method]" value="">
            <input type="hidden" id="inline_payment_paid_at_hidden" name="inline_payment[paid_at]" value="{{ $oldNote['transaction_date'] }}">
            <input type="hidden" id="inline_payment_amount_paid_rupiah" name="inline_payment[amount_paid_rupiah]" value="">
            <input type="hidden" id="inline_payment_amount_received_rupiah" name="inline_payment[amount_received_rupiah]" value="">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Pembayaran Nota</h5>
                    <p class="mb-0 text-muted small">Pilih metode dan isi nominal pembayaran sesuai transaksi.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">
                    @include('cashier.notes.workspace.partials.payment-modal-left')
                    @include('cashier.notes.workspace.partials.payment-modal-right')
                </div>
            </div>

            @include('cashier.notes.workspace.partials.payment-modal-footer')
        </div>
    </div>
</div>
