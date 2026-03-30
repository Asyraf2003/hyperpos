@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="alert alert-warning">Prototype UI saja. Gaya POS panel samping tanpa modal penuh.</div>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Prototype C</h4>
                <p class="mb-0 text-muted">Side sheet ala POS. Terasa kasir, tapi lebih invasif ke layout workspace.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $prototypeLinks['a'] }}" class="btn btn-outline-primary">A</a>
                <a href="{{ $prototypeLinks['b'] }}" class="btn btn-outline-primary">B</a>
                <a href="{{ $prototypeLinks['c'] }}" class="btn btn-primary">C</a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Workspace Ringkas</h5>
                    <div class="border rounded p-3 mb-3">Rincian 1 · Produk</div>
                    <div class="border rounded p-3 mb-3">Rincian 2 · Servis</div>
                    <div class="border rounded p-3">Rincian 3 · Servis + Sparepart Toko</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="fw-semibold">Panel Pembayaran POS</div>
                    <small>Lebih cocok kalau pembayaran selalu jadi fokus utama.</small>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 mb-3">
                        <div class="small text-muted">Grand Total</div>
                        <div class="fs-4 fw-bold">250.000</div>
                    </div>

                    <div class="btn-group w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary">Skip</button>
                        <button type="button" class="btn btn-primary">Penuh</button>
                        <button type="button" class="btn btn-outline-primary">Parsial</button>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Aksi Cepat</div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary">TF</button>
                            <button type="button" class="btn btn-primary">Cash</button>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Kalkulator Cash</div>
                        <label class="form-label">Tagihan</label>
                        <input type="text" class="form-control mb-2" value="250.000" readonly>
                        <label class="form-label">Uang Pelanggan</label>
                        <input type="text" class="form-control mb-2" value="300.000">
                        <label class="form-label">Kembalian</label>
                        <input type="text" class="form-control" value="50.000" readonly>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success">OK</button>
                        <button type="button" class="btn btn-light">Simpan Nota</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
