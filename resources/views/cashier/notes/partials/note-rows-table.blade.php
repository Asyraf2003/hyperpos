<div class="card h-100">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">Daftar Line Nota</h4>
        <p class="mb-0 text-muted">Tabel ini tetap menjadi layer baca domain line dan basis selection refund. Payment tidak lagi memilih langsung dari tabel ini.</p>
      </div>
      <span class="badge bg-light text-dark border">{{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}</span>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Line</th>
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
            <tr>
              <td>{{ $row['line_no'] }}</td>
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
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada line pada nota ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="small text-muted mt-3">Refund tetap memilih line domain di modal. Payment memakai billing projection agar komponen tagihan tidak bercampur dengan layer baca line.</div>
  </div>
</div>
