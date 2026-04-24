<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-start gap-2">
      <div>
        <h4 class="card-title mb-0">Versioning Nota</h4>
      </div>
      <span class="badge bg-light text-dark border">{{ $revisionCount }} Revision</span>
    </div>
  </div>

  <div class="card-body">
    <div class="border rounded p-3 mb-3 bg-light">
      <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
        <div>
          <div class="small text-muted">Revision Aktif</div>
          <div class="fw-bold">R{{ (int) ($currentRevision['revision_number'] ?? 0) }}</div>
        </div>
        <div class="text-end small text-muted">
          <div>{{ $currentRevision['created_at'] ?? '-' }}</div>
          @if (!empty($currentRevision['created_by_actor_id']))
            <div>{{ $currentRevision['created_by_actor_id'] }}</div>
          @endif
        </div>
      </div>

      @if (!empty($currentRevision['change_summary_lines']))
        <div class="d-flex flex-column gap-2">
          @foreach (($currentRevision['change_summary_lines'] ?? []) as $summary)
            <div class="small">{{ $summary }}</div>
          @endforeach
        </div>
      @endif
    </div>

    @if ($timelineRevisions === [])
      <div class="text-muted">Belum ada riwayat revisi.</div>
    @else
      <div class="d-flex flex-column gap-3">
        @foreach ($timelineRevisions as $entry)
          <div class="border rounded p-3">
            <div class="d-flex justify-content-between gap-2 mb-2">
              <div class="fw-semibold">R{{ (int) ($entry['revision_number'] ?? 0) }}</div>
              <div class="text-end small text-muted">
                <div>{{ $entry['created_at'] ?? '-' }}</div>
                @if (!empty($entry['created_by_actor_id']))
                  <div>{{ $entry['created_by_actor_id'] }}</div>
                @endif
              </div>
            </div>

            @if (!empty($entry['change_summary_lines']))
              <div class="d-flex flex-column gap-2 mb-2">
                @foreach (($entry['change_summary_lines'] ?? []) as $summary)
                  <div class="small">{{ $summary }}</div>
                @endforeach
              </div>
            @endif

            @if (!empty($entry['reason']))
              <div class="small text-muted">{{ $entry['reason'] }}</div>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
