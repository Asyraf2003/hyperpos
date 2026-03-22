@extends('layouts.app')

@section('title', 'Kasir Dashboard')
@section('heading', 'Kasir Dashboard')

@section('content')
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Nota Aktif</h6>
                            <h4 class="font-extrabold mb-0">6</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Transaksi Hari Ini</h6>
                            <h4 class="font-extrabold mb-0">19</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Uang Masuk</h6>
                            <h4 class="font-extrabold mb-0">Rp 875.000</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Pelanggan Hari Ini</h6>
                            <h4 class="font-extrabold mb-0">12</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl">
                            <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Foto Pengguna">
                        </div>

                        <div class="ms-3 name">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="font-bold mb-0">{{ $appShell['actor_label'] ?? 'Pengguna' }}</h5>
                                <span class="text-primary" title="Terverifikasi" style="font-size: 18px;">
                                    <i class="bi bi-patch-check-fill"></i>
                                </span>
                            </div>
                            <h6 class="text-muted mb-0">{{ $appShell['user_email'] ?? '-' }}</h6>
                        </div>
                    </div>

                    <br>

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
    </section>
@endsection
