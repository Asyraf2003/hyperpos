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
                        Halaman ini disiapkan untuk riwayat operasional kasir. Slice berikutnya akan
                        mengisi daftar nota hari ini dan carry-over nota open dari kemarin.
                    </p>
                </div>

                <div>
                    <a href="{{ route('cashier.notes.create') }}" class="btn btn-primary">
                        Buat Nota
                    </a>
                </div>
            </div>

            <div class="alert alert-light-secondary mt-4 mb-0">
                Skeleton navigasi sudah aktif. Daftar nota, filter tanggal, paginate, dan status
                operasional kasir akan diisi pada slice berikutnya.
            </div>
        </div>
    </div>
</div>
@endsection
