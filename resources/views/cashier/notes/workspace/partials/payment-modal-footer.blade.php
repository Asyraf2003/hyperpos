<div class="modal-footer">
    <div class="w-100 d-flex justify-content-between align-items-center gap-2" id="workspace-payment-footer-main">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>

        <div class="d-flex gap-2">
            <button
                type="submit"
                class="btn btn-outline-primary"
                id="workspace-payment-submit-transfer"
                @if (($workspaceMode ?? 'create') === 'edit') disabled @endif
            >
                Bayar Transfer
            </button>

            <button
                type="button"
                class="btn btn-primary"
                id="workspace-payment-open-cash"
                @if (($workspaceMode ?? 'create') === 'edit') disabled @endif
            >
                Bayar Cash
            </button>
        </div>
    </div>

    <div class="w-100 d-none justify-content-between align-items-center gap-2" id="workspace-payment-footer-cash">
        <button type="button" class="btn btn-light" id="workspace-payment-back-cash">Kembali</button>

        <button
            type="submit"
            class="btn btn-primary"
            id="workspace-payment-submit-cash"
            @if (($workspaceMode ?? 'create') === 'edit') disabled @endif
        >
            Simpan Pembayaran Cash
        </button>
    </div>
</div>
