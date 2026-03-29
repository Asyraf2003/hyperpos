@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h5 class="mb-1">Riwayat Nota Kasir</h5>
                    <p class="mb-0 text-muted">
                        Halaman ini disiapkan untuk operasional kasir: menampilkan nota hari ini
                        dan carry-over nota open atau belum selesai dari kemarin.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light-primary text-primary">Workspace Kasir</span>
                    <a href="{{ route('cashier.notes.create') }}" class="btn btn-primary">
                        Buat Nota
                    </a>
                </div>
            </div>

            <div class="alert alert-light-secondary mt-4 mb-0">
                Skeleton navigasi sudah aktif. Slice berikutnya akan mengisi daftar nota, filter tanggal,
                paginate, status pembayaran, dan status operasional kasir dengan pola UI repo yang seragam.
            </div>
        </div>
    </div>
</div>
@endsection
