<section class="row g-4 mb-4">
    <div class="col-12">
        <div class="analytics-stage-card h-100">
            <div class="analytics-stage-head">
                <div>
                    <h6 class="section-title mb-1">Pecahan Kembalian Kas</h6>
                    <p class="analytics-stage-range">
                        Range:
                        {{ $cashChangeRange['date_from'] ?? '-' }}
                        s.d.
                        {{ $cashChangeRange['date_to'] ?? '-' }}
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

            <p class="helper-note mt-3 mb-0">
                Breakdown ini membaca potensi kembalian cash untuk persiapan laci kas, bukan komponen laba operasional.
            </p>
        </div>
    </div>
</section>
