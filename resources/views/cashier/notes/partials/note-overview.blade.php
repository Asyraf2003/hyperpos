<div class="row g-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Customer</div><div class="fw-bold">{{ $note['customer_name'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Tanggal</div><div class="fw-bold">{{ $note['transaction_date'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Payment Status</div><div class="fw-bold text-uppercase">{{ $note['payment_status'] }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Sisa Tagihan</div><div class="fw-bold">{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</div></div></div></div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Grand Total</div><div class="fw-bold">{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Total Dialokasikan</div><div class="fw-bold">{{ number_format($note['total_allocated_rupiah'], 0, ',', '.') }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Total Refund</div><div class="fw-bold">{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</div></div>
        </div>
    </div>
</div>
