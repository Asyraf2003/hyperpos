<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-1">Versioning Nota</h4>
        <p class="mb-0 text-muted">Versi aktif dan 3 revisi terakhir dari nota yang sama.</p>
      </div>
      <span class="badge bg-light text-dark border">{{ $revisionCount }} Revision</span>
    </div>
  </div>

  <div class="card-body">
    <div class="border rounded p-3 mb-3 bg-light">
      <div class="small text-muted mb-1">Current Revision</div>
      <div class="fw-bold">R{{ (int) ($currentRevision['revision_number'] ?? 0) }}</div>
      <div class="small text-muted">{{ $currentRevision['created_at'] ?? '-' }}</div>
    </div>

    @if ($timelineRevisions === [])
      <div class="text-muted">Belum ada riwayat revisi.</div>
    @else
      <div class="d-flex flex-column gap-3">
        @foreach ($timelineRevisions as $entry)
          <div class="border rounded p-3">
            <div class="d-flex justify-content-between gap-2 mb-1">
              <div class="fw-semibold">R{{ (int) ($entry['revision_number'] ?? 0) }}</div>
              <div class="small text-muted">{{ $entry['created_at'] ?? '-' }}</div>
            </div>
            <div class="small text-muted mb-1">{{ $entry['customer_name'] ?? '-' }}</div>
            @if (!empty($entry['reason']))
              <div class="small">{{ $entry['reason'] }}</div>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
