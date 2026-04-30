@php
    $_uiDateDisplay = static function ($value, bool $withTime = false): string {
        if ($value === null || $value === '') {
            return '-';
        }

        $text = (string) $value;

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $text) === 1) {
            return $text;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (\Throwable) {
            return $text;
        }
    };
@endphp

<div class="col-12 col-lg-6">
        <div class="analytics-stage-card h-100">
            <div class="analytics-stage-head">
                <div>
                    <h6 class="section-title mb-1">Pecahan Kembalian Kas</h6>
                    <p class="analytics-stage-range">
                        Range:
                        {{ $_uiDateDisplay($cashChangeRange['date_from'] ?? null) }}
                        s.d.
                        {{ $_uiDateDisplay($cashChangeRange['date_to'] ?? null) }}
                    </p>
                </div>
                <span class="badge-soft bg-soft-warning">
                    <i class="bi bi-cash-stack"></i>
                    {{ count($cashChangeDenominations ?? []) }} Pecahan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Pecahan</th>
                            <th>Jumlah</th>
                            <th>Total Kembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cashChangeDenominations ?? [] as $row)
                            <tr>
                                <td>Rp {{ number_format($row['denomination'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($row['count'] ?? 0, 0, ',', '.') }} Lembar/Koin</td>
                                <td>Rp {{ number_format($row['total_rupiah'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Belum ada data kembalian cash pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
