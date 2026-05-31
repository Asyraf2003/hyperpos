@if (is_array($row['package_breakdown'] ?? null))
  <div class="small text-muted mt-2">
    <div>
      Paket total:
      Rp {{ number_format((int) ($row['package_breakdown']['package_total_rupiah'] ?? 0), 0, ',', '.') }}
    </div>
    <div>
      Total sparepart:
      Rp {{ number_format((int) ($row['package_breakdown']['parts_total_rupiah'] ?? 0), 0, ',', '.') }}
    </div>
    <div>
      Sisa jasa:
      Rp {{ number_format((int) ($row['package_breakdown']['service_residual_rupiah'] ?? 0), 0, ',', '.') }}
    </div>

    @foreach (($row['package_breakdown']['parts'] ?? []) as $part)
      <div>
        - {{ $part['product_name'] ?? $part['product_id'] ?? '-' }}
        x{{ (int) ($part['qty'] ?? 0) }}
        =
        Rp {{ number_format((int) ($part['line_total_rupiah'] ?? 0), 0, ',', '.') }}
      </div>
    @endforeach
  </div>
@endif
