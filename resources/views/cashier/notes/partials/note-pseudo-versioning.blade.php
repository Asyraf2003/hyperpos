<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">Riwayat Mutasi Nota</h4>
        <p class="mb-0 text-muted">Fase ini hanya membaca current state, baseline yang memang tersedia, dan timeline mutasi native. True revision persistence belum dibuka.</p>
      </div>
      <span class="badge bg-light-info text-info border">Phase Read Model</span>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-12 col-xl-5">
        <div class="border rounded p-3 h-100">
          <h5 class="mb-3">State Saat Ini</h5>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Status Note</small>
            <div class="text-end text-uppercase">{{ $note['pseudo_versioning']['current']['note_state'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Ringkasan Line</small>
            <div class="text-end">{{ $note['pseudo_versioning']['current']['line_summary_label'] ?? '-' }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Grand Total</small>
            <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['current']['grand_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Sudah Dibayar</small>
            <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['current']['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
            <small>Total Refund</small>
            <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['current']['total_refunded_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
          <div class="ui-key-value d-flex justify-content-between py-2">
            <small>Sisa Tagihan</small>
            <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['current']['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-7">
        <div class="border rounded p-3 h-100">
          <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">Baseline yang Tersedia</h5>
            @if (($note['pseudo_versioning']['baseline'] ?? null) !== null)
              <span class="badge bg-light-{{ $note['pseudo_versioning']['baseline']['badge_tone'] ?? 'info' }} text-{{ $note['pseudo_versioning']['baseline']['badge_tone'] ?? 'info' }}">{{ $note['pseudo_versioning']['baseline']['badge_label'] ?? 'Pseudo Versioning' }}</span>
            @endif
          </div>

          @if (($note['pseudo_versioning']['baseline'] ?? null) === null)
            <div class="text-muted small">Belum ada mutation snapshot yang cukup untuk menampilkan baseline tersedia. Timeline tetap ditampilkan apa adanya.</div>
          @else
            <div class="alert alert-light-info mb-3">{{ $note['pseudo_versioning']['baseline']['note'] ?? '' }}</div>
            <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
              <small>Captured At</small>
              <div class="text-end">{{ $note['pseudo_versioning']['baseline']['captured_at'] ?? '-' }}</div>
            </div>
            <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
              <small>Total Sebelum Mutasi Awal</small>
              <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['baseline']['total_rupiah'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="ui-key-value d-flex justify-content-between py-2 border-bottom">
              <small>Refund Wajib Saat Itu</small>
              <div class="text-end">{{ number_format((int) ($note['pseudo_versioning']['baseline']['refund_required_rupiah'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="ui-key-value d-flex justify-content-between py-2">
              <small>Target Status</small>
              <div class="text-end">{{ $note['pseudo_versioning']['baseline']['target_status'] ?? '-' }}</div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="border-top mt-4 pt-4">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <div>
          <h5 class="mb-1">Timeline Perubahan Nota</h5>
          <p class="text-muted mb-0 small">Riwayat ini berasal dari mutation event native, bukan true note revision entity.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ count($note['pseudo_versioning']['timeline'] ?? []) }} Event</span>
      </div>

      @if (($note['pseudo_versioning']['timeline'] ?? []) === [])
        <div class="text-muted">Belum ada mutasi note yang tercatat.</div>
      @else
        <div class="timeline">
          @foreach (($note['pseudo_versioning']['timeline'] ?? []) as $entry)
            <div class="timeline-item pb-4">
              <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                <div>
                  <h6 class="mb-1">{{ $entry['event_label'] }}</h6>
                  <small class="text-muted">{{ $entry['created_at'] }}</small>
                </div>
                @if (!empty($entry['target_status']))
                  <span class="badge bg-light-secondary text-secondary align-self-start text-uppercase">{{ $entry['target_status'] }}</span>
                @endif
              </div>

              @if (!empty($entry['reason']))
                <div class="small text-muted mb-2">{{ $entry['reason'] }}</div>
              @endif

              @if (!empty($entry['performed_by_actor_id']))
                <div class="small mb-2">
                  <span class="text-muted">Diproses oleh:</span> {{ $entry['performed_by_actor_id'] }}
                </div>
              @endif

              <div class="border rounded p-3 bg-light-subtle">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Total Sebelum</small>
                    <div class="fw-semibold">{{ number_format((int) ($entry['before_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
                  </div>
                  <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Total Sesudah</small>
                    <div class="fw-semibold">{{ number_format((int) ($entry['after_total_rupiah'] ?? 0), 0, ',', '.') }}</div>
                  </div>
                  <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Refund Wajib</small>
                    <div class="fw-semibold">{{ number_format((int) ($entry['refund_required_rupiah'] ?? 0), 0, ',', '.') }}</div>
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
