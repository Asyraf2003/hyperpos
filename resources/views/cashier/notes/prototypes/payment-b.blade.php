@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="alert alert-warning">Prototype UI saja. Fokus ke alur bertahap model wizard.</div>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Prototype B</h4>
                <p class="mb-0 text-muted">Dialog stepper. Lebih terstruktur, tapi klik-nya lebih banyak.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $prototypeLinks['a'] }}" class="btn btn-outline-primary">A</a>
                <a href="{{ $prototypeLinks['b'] }}" class="btn btn-primary">B</a>
                <a href="{{ $prototypeLinks['c'] }}" class="btn btn-outline-primary">C</a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#prototype-b-modal">Buka Prototype</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="prototype-b-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" data-prototype="b">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Pembayaran Nota</h5>
                        <p class="mb-0 text-muted small">Mode step-by-step supaya kasir tidak ditabrak semua opsi sekaligus.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex gap-2 mb-4">
                        <span class="badge bg-primary">1. Pilih Mode</span>
                        <span class="badge bg-light text-dark">2. Pilih Metode</span>
                        <span class="badge bg-light text-dark">3. Kalkulator / Konfirmasi</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Total</div><div class="fs-5 fw-bold">250.000</div></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Bayar Sekarang</div><div class="fs-5 fw-bold">100.000</div></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Sisa</div><div class="fs-5 fw-bold">150.000</div></div></div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Step 1 · Pilih Mode</div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary">Skip</button>
                            <button type="button" class="btn btn-outline-primary">Bayar Penuh</button>
                            <button type="button" class="btn btn-primary">Bayar Sebagian</button>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Step 2 · Metode / Nilai</div>
                        <label class="form-label">Nominal Parsial</label>
                        <input type="text" class="form-control mb-3" value="100.000">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary">Transfer</button>
                            <button type="button" class="btn btn-primary">Cash</button>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="fw-semibold mb-2">Step 3 · Kalkulator Cash</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><input type="text" class="form-control" value="100.000" readonly></div>
                            <div class="col-md-4"><input type="text" class="form-control" value="200.000"></div>
                            <div class="col-md-4"><input type="text" class="form-control" value="100.000" readonly></div>
                        </div>
                        <div class="small text-muted">Tagihan · Uang Pelanggan · Kembalian</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light">Kembali</button>
                    <button type="button" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
