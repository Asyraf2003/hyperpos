<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">List Line Nota</h4>
        <p class="mb-0 text-muted">Pilih line untuk refund. Edit membuka draft revision baru tanpa langsung mengganti nota aktif.</p>
      </div>

      <div class="d-flex flex-wrap gap-2">
        @if ($note['can_edit_workspace'] ?? false)
          <a
            href="{{ route($detailConfig['workspace_edit_route'], ['noteId' => $note['id']]) }}"
            class="btn btn-primary"
          >
            Edit
          </a>
        @endif

        @if ($note['can_show_refund_form'] ?? false)
          <button
            type="button"
            class="btn btn-outline-warning opacity-50 disabled"
            data-bs-toggle="modal"
            data-bs-target="#note-refund-modal"
            id="note-refund-open-button"
            disabled
            aria-disabled="true"
          >
            Refund
          </button>
        @endif
      </div>
    </div>
  </div>

  <div class="card-body">
    @include('cashier.notes.partials.note-rows-table')
    @include('cashier.notes.partials.billing-table')
  </div>
</div>
