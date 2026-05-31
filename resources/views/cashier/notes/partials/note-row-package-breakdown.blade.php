@php
  $packageBreakdown = is_array($row['package_breakdown'] ?? null)
    ? $row['package_breakdown']
    : null;
@endphp

@if ($packageBreakdown !== null)
  <div class="small text-muted mt-2">
    <div>
      Paket total:
      Rp {{ number_format((int) ($packageBreakdown['package_total_rupiah'] ?? 0), 0, ',', '.') }}
    </div>
    <div>
      Total sparepart:
      Rp {{ number_format((int) ($packageBreakdown['parts_total_rupiah'] ?? 0), 0, ',', '.') }}
    </div>
    <div>
      Sisa jasa:
      Rp {{ number_format((int) ($packageBreakdown['service_residual_rupiah'] ?? 0), 0, ',', '.') }}
    </div>

    @foreach (($packageBreakdown['parts'] ?? []) as $part)
      <div>
        - {{ $part['product_name'] ?? $part['product_id'] ?? '-' }}
        x{{ (int) ($part['qty'] ?? 0) }}
        =
        Rp {{ number_format((int) ($part['line_total_rupiah'] ?? 0), 0, ',', '.') }}
      </div>
    @endforeach
  </div>
@endif
