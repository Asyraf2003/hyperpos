<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-1">Panel Tindakan Nota</h4>
    <p class="mb-0 text-muted">Aksi utama hidup di level note. Refund dipilih dari line detail yang diklik.</p>
  </div>
  <div class="card-body">
    <div class="d-grid gap-2">
      <a
        href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id'] ?? ($note['note_header']['id'] ?? null)]) }}"
        class="btn btn-outline-secondary"
      >
        Edit Nota
      </a>

      @if ($note['can_show_payment_form'] ?? false)
        <button
          type="button"
          class="btn btn-primary js-open-payment-intent"
          data-bs-toggle="modal"
          data-bs-target="#note-payment-modal"
          data-payment-intent="pay"
          data-payment-preset="manual"
        >
          Buka Modal Bayar
        </button>

        <button
          type="button"
          class="btn btn-outline-primary js-open-payment-intent"
          data-bs-toggle="modal"
          data-bs-target="#note-payment-modal"
          data-payment-intent="settle"
          data-payment-preset="manual"
        >
          Lunasi Pembayaran
        </button>
      @endif

      @if ($note['can_show_refund_form'] ?? false)
        <button
          type="button"
          class="btn btn-outline-warning opacity-50"
          id="note-refund-open-button"
          disabled
          aria-disabled="true"
          style="pointer-events:none;"
        >
          Refund Line Terpilih
        </button>
      @endif
    </div>

    @if ($note['can_show_payment_form'] ?? false)
      <div class="border rounded p-3 mt-3 bg-light">
        <div class="fw-semibold mb-1">Kontrak Payment</div>
        <div class="small text-muted">DP hidup sebagai preset di modal Bayar. Lunasi akan memilih semua billing row outstanding yang masih sah menurut urutan komponen existing.</div>
      </div>
    @endif

    @if ($note['can_show_refund_form'] ?? false)
      <div class="border rounded p-3 mt-3 bg-light">
        <div class="fw-semibold mb-1">Kontrak Refund</div>
        <div class="small text-muted">Klik line pada tabel untuk memilih refund. Tombol refund tetap burem sampai ada line dipilih.</div>
        <div class="small text-muted mt-2">Hover hanya menandakan row bisa dipilih. Row terpilih akan jauh lebih gelap.</div>
      </div>
    @endif
  </div>
</div>
