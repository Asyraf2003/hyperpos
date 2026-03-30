@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="alert alert-warning">Prototype UI saja. Belum terhubung ke flow transaksi final.</div>

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Prototype A</h4>
                <p class="mb-0 text-muted">Modal action-first. Fokus ke keputusan bayar, bukan form panjang.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $prototypeLinks['a'] }}" class="btn btn-primary">A</a>
                <a href="{{ $prototypeLinks['b'] }}" class="btn btn-outline-primary">B</a>
                <a href="{{ $prototypeLinks['c'] }}" class="btn btn-outline-primary">C</a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#prototype-a-modal">Buka Prototype</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="prototype-a-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" data-prototype="a">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Pengaturan Pembayaran</h5>
                        <p class="mb-0 text-muted small">Mode action-centric untuk kasir.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Grand Total</div><div class="fs-4 fw-bold">250.000</div></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Dibayar Sekarang</div><div class="fs-4 fw-bold" data-paid-now>0</div></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Sisa Tagihan</div><div class="fs-4 fw-bold" data-outstanding>250.000</div></div></div>
                    </div>

                    <div class="btn-group mb-4 w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-mode="skip">Skip</button>
                        <button type="button" class="btn btn-outline-primary" data-mode="full">Bayar Penuh</button>
                        <button type="button" class="btn btn-outline-primary" data-mode="partial">Bayar Sebagian</button>
                    </div>

                    <div data-panel="skip" class="border rounded p-3">
                        <div class="fw-semibold mb-1">Simpan tanpa pembayaran</div>
                        <div class="text-muted small">Cocok kalau kasir mau lanjut ke detail/riwayat dulu.</div>
                    </div>

                    <div data-panel="full" class="d-none">
                        <div class="border rounded p-3 mb-3">
                            <div class="small text-muted">Nominal otomatis</div>
                            <div class="fs-5 fw-bold">250.000</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary">TF</button>
                            <button type="button" class="btn btn-primary" data-open-cash>Cash</button>
                        </div>
                    </div>

                    <div data-panel="partial" class="d-none">
                        <label class="form-label">Mau bayar berapa</label>
                        <input type="text" class="form-control mb-3" value="100.000">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary">TF</button>
                            <button type="button" class="btn btn-primary" data-open-cash>Cash</button>
                        </div>
                    </div>

                    <div data-panel="cash" class="d-none">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Tagihan</div><div class="fs-5 fw-bold">100.000</div></div></div>
                            <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Uang Pelanggan</div><div class="fs-5 fw-bold">200.000</div></div></div>
                            <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Kembalian</div><div class="fs-5 fw-bold">100.000</div></div></div>
                        </div>
                        <input type="text" class="form-control mb-3" placeholder="Masukkan uang pelanggan">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-back-cash>Kembali</button>
                            <button type="button" class="btn btn-primary">OK</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" data-footer="default">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-action-label>Simpan Nota</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment-prototypes.js') }}"></script>
@endpush
