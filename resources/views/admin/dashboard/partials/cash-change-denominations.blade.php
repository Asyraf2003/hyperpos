<div class="col-12 col-lg-6">
        <div class="analytics-stage-card h-100">
            <div class="analytics-stage-head">
                <div>
                    <h6 class="section-title mb-1">Pecahan Kembalian Kas</h6>
                    <p class="analytics-stage-range" data-dashboard-analytics-target="cash-change-range">
                        Range:
                        {{ \App\Support\ViewDateFormatter::display($cashChangeRange['date_from'] ?? null) }}
                        s.d.
                        {{ \App\Support\ViewDateFormatter::display($cashChangeRange['date_to'] ?? null) }}
                    </p>
                </div>
                <span class="badge-soft bg-soft-warning" data-dashboard-analytics-target="cash-change-badge">
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
                    <tbody data-dashboard-analytics-target="cash-change-rows">
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
