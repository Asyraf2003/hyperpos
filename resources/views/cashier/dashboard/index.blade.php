@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('heading', 'Dashboard Kasir')

@section('content')
<section class="section">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <h4 class="mb-1">Area Kerja Kasir</h4>
                        <p class="text-muted mb-0">
                            Gunakan dashboard ini sebagai pintu masuk cepat ke alur kerja kasir.
                            Fokus utama ada di pembuatan nota baru dan riwayat nota aktif.
                        </p>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="fw-semibold mb-2">Buat Nota Baru</div>
                                <div class="text-muted small mb-3">
                                    Mulai transaksi baru dari workspace kasir.
                                </div>

                                <a href="{{ route('cashier.notes.workspace.create') }}" class="btn btn-primary w-100">
                                    Buat Nota
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="border rounded p-4 h-100">
                                <div class="fw-semibold mb-2">Buka Riwayat Nota</div>
                                <div class="text-muted small mb-3">
                                    Lihat nota open pada window kasir hari ini dan kemarin.
                                </div>

                                <a href="{{ route('cashier.notes.index') }}" class="btn btn-outline-primary w-100">
                                    Riwayat Nota
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light mt-4 mb-0">
                        Dashboard kasir sengaja dibuat ringan supaya navigasi utama tidak berisik.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl">
                            <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Foto Pengguna">
                        </div>

                        <div class="ms-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="font-bold mb-0">{{ $appShell['actor_label'] ?? 'Pengguna' }}</h5>
                                <span class="text-primary" title="Terverifikasi" style="font-size: 18px;">
                                    <i class="bi bi-patch-check-fill"></i>
                                </span>
                            </div>
                            <div class="text-muted">{{ $appShell['user_email'] ?? '-' }}</div>
                        </div>
                    </div>

                    <hr>

                    <form action="{{ route('logout') }}" method="post" class="d-grid">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Keluar Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
