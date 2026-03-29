@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h5 class="mb-1">Riwayat Nota Admin</h5>
                    <p class="mb-0 text-muted">
                        Halaman ini disiapkan sebagai workspace operasional admin untuk membuka nota,
                        memantau status pembayaran, dan nanti menangani editability sesuai policy.
                        Admin tidak membuat transaksi dari halaman ini.
                    </p>
                </div>

                <div class="text-lg-end">
                    <span class="badge bg-light-secondary text-dark">Workspace Admin</span>
                </div>
            </div>

            <div class="alert alert-light-secondary mt-4 mb-0">
                Skeleton navigasi sudah aktif. Isi tabel, filter tanggal, paginate, status payment,
                dan aksi edit admin akan diisi pada slice berikutnya.
            </div>
        </div>
    </div>
</div>
@endsection
