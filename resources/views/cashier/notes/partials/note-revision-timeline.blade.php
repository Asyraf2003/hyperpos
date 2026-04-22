<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">Riwayat Revisi Nota</h4>
        <p class="mb-0 text-muted">Detail note sekarang membaca current revision nyata dari root note. Payment dan refund tetap menempel ke root note yang sama.</p>
      </div>
      <span class="badge bg-light-info text-info border">Root + Revision Chain</span>
    </div>
  </div>
  <div class="card-body">
    @php
      $revision = $note['revision_timeline'] ?? ['current' => [], 'baseline' => [], 'timeline' => []];
      $current = $revision['current'] ?? [];
      $baseline = $revision['baseline'] ?? [];
      $timeline = $revision['timeline'] ?? [];
    @endphp

    <div class="row g-4">
      <div class="col-12 col-xl-5">
        <div class="border rounded p-3 h-100">
          <h5 class="mb-3">Current Revision</h5>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Revision</small>
            <div class="text-end">R{{ (int) ($current['revision_number'] ?? 0) }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Customer</small>
            <div class="text-end">{{ $current['customer_name'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Tanggal Nota</small>
            <div class="text-end">{{ $current['transaction_date'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Jumlah Line</small>
            <div class="text-end">{{ (int) ($current['line_count'] ?? 0) }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Grand Total</small>
            <div class="text-end">{{ number_format((int) ($current['grand_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2">
            <small>Dibuat Pada</small>
            <div class="text-end">{{ $current['created_at'] ?? '-' }}</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-7">
        <div class="border rounded p-3 h-100">
          <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Baseline Revision</h5>
            <span class="badge bg-light text-dark border">
              R{{ (int) ($baseline['revision_number'] ?? 0) }}
            </span>
          </div>

          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Customer</small>
            <div class="text-end">{{ $baseline['customer_name'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Tanggal Nota</small>
            <div class="text-end">{{ $baseline['transaction_date'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Jumlah Line</small>
            <div class="text-end">{{ (int) ($baseline['line_count'] ?? 0) }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Grand Total</small>
            <div class="text-end">{{ number_format((int) ($baseline['grand_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2">
            <small>Dibuat Pada</small>
            <div class="text-end">{{ $baseline['created_at'] ?? '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="border-top mt-4 pt-4">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <div>
          <h5 class="mb-1">Timeline Revision</h5>
          <p class="text-muted mb-0 small">Timeline ini menunjukkan chain revision pada root note yang sama.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ count($timeline) }} Revision</span>
      </div>

      @if ($timeline === [])
        <div class="text-muted">Belum ada revision yang tercatat.</div>
      @else
        <div class="timeline">
          @foreach ($timeline as $entry)
            <div class="timeline-item pb-4">
              <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                <div>
                  <h6 class="mb-1">Revision R{{ (int) ($entry['revision_number'] ?? 0) }}</h6>
                  <small class="text-muted">{{ $entry['created_at'] ?? '-' }}</small>
                </div>
                <span class="badge bg-light-secondary text-secondary align-self-start">
                  {{ $entry['revision_id'] ?? '-' }}
                </span>
              </div>

              @if (!empty($entry['reason']))
                <div class="small text-muted mb-2">{{ $entry['reason'] }}</div>
              @endif

              @if (!empty($entry['created_by_actor_id']))
                <div class="small mb-2">
                  <span class="text-muted">Diproses oleh:</span> {{ $entry['created_by_actor_id'] }}
                </div>
              @endif

              <div class="border rounded p-3 bg-light-subtle">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Customer</small>
                    <div class="fw-semibold">{{ $entry['customer_name'] ?? '-' }}</div>
                  </div>
                  <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Tanggal Nota</small>
                    <div class="fw-semibold">{{ $entry['transaction_date'] ?? '-' }}</div>
                  </div>
                  <div class="col-12 col-md-2">
                    <small class="text-muted d-block">Line</small>
                    <div class="fw-semibold">{{ (int) ($entry['line_count'] ?? 0) }}</div>
                  </div>
                  <div class="col-12 col-md-2">
                    <small class="text-muted d-block">Total</small>
                    <div class="fw-semibold">{{ number_format((int) ($entry['grand_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
