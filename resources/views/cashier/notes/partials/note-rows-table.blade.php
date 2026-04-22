<div class="card h-100">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">Daftar Line Nota</h4>
        <p class="mb-0 text-muted">Klik row untuk menandai line refund. Row gelap berarti sedang dipilih.</p>
      </div>
      <span class="badge bg-light text-dark border">{{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}</span>
    </div>
  </div>
  <div class="card-body">
    <style>
      .refund-row-selectable { cursor: pointer; transition: background-color .15s ease, box-shadow .15s ease; }
      .refund-row-selectable.refund-row-selected > td {
        background-color: #dbeafe !important;
        box-shadow: inset 0 0 0 9999px rgba(30, 64, 175, 0.12);
      }
    </style>

    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Line</th>
            <th>Label</th>
            <th>Tipe Domain</th>
            <th>Status Line</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Sudah Dibayar</th>
            <th class="text-end">Refund</th>
            <th class="text-end">Sisa</th>
            <th>Preview Refund</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($note['rows'] as $row)
            @php
              $isRefundable = (bool) ($row['can_refund'] ?? false);
              $isSelected = in_array($row['id'], old('selected_row_ids', []), true);
            @endphp
            <tr
              @if ($isRefundable)
                role="button"
                tabindex="0"
                class="refund-row-selectable {{ $isSelected ? 'refund-row-selected' : '' }}"
                data-refund-row
                data-row-id="{{ $row['id'] }}"
                data-line-no="{{ $row['line_no'] }}"
                data-line-label="{{ $row['line_label'] ?? '-' }}"
                data-type-label="{{ $row['type_label'] }}"
                data-refundable-rupiah="{{ (int) ($row['net_paid_rupiah'] ?? 0) }}"
                data-store-return-count="{{ (int) ($row['refund_stock_return_count'] ?? 0) }}"
                data-external-count="{{ (int) ($row['refund_external_count'] ?? 0) }}"
                data-preview-label="{{ $row['refund_preview_label'] ?? '-' }}"
                data-selected="{{ $isSelected ? '1' : '0' }}"
              @endif
            >
              <td>{{ $row['line_no'] }}</td>
              <td>
                <div class="fw-semibold">{{ $row['line_label'] ?? '-' }}</div>
                @if ($isRefundable)
                  <div class="small text-muted">Klik untuk pilih refund</div>
                @endif
              </td>
              <td>{{ $row['type_label'] }}</td>
              <td><span class="badge bg-light text-dark border text-uppercase">{{ (string) ($row['line_status'] ?? '') !== '' ? $row['line_status'] : '-' }}</span></td>
              <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format((int) ($row['refunded_rupiah'] ?? 0), 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</td>
              <td>
                <div class="small text-muted">{{ $row['refund_preview_label'] ?? '-' }}</div>
                @if ((int) ($row['refund_stock_return_count'] ?? 0) > 0)
                  <div class="small">Stok toko kembali: {{ (int) ($row['refund_stock_return_count'] ?? 0) }}</div>
                @endif
                @if ((int) ($row['refund_external_count'] ?? 0) > 0)
                  <div class="small">External disederhanakan: {{ (int) ($row['refund_external_count'] ?? 0) }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Belum ada line pada nota ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="small text-muted mt-3">Refund dipilih dari tabel line. Payment tetap memakai billing projection agar layer baca line tidak bercampur dengan komponen tagihan.</div>
  </div>
</div>
