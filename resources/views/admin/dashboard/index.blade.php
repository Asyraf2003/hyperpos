@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('heading', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<style>
    /* Custom Tweak untuk Dashboard yang lebih Modern */
    .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 12px; }
    .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .bg-indigo-light { background-color: #f5f3ff; color: #4f46e5; }
    .bg-emerald-light { background-color: #ecfdf5; color: #059669; }
    .bg-amber-light { background-color: #fffbeb; color: #d97706; }
    .text-indigo { color: #4f46e5 !important; }
</style>

<section class="row">
    <div class="col-12 col-lg-9">
        <div class="row">
            <div class="col-6 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="icon-box bg-indigo-light mb-2">
                                    <i class="bi bi-currency-dollar fs-4"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Omzet Hari Ini</h6>
                                <h5 class="font-extrabold mb-0">Rp 4.510.000</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="icon-box bg-emerald-light mb-2">
                                    <i class="bi bi-graph-up-arrow fs-4"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Gross Profit</h6>
                                <h5 class="font-extrabold mb-0 text-success">Rp 2.700.000</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="icon-box bg-amber-light mb-2">
                                    <i class="bi bi-person-fill-exclamation fs-4"></i>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Piutang Aktif</h6>
                                <h5 class="font-extrabold mb-0 text-warning">Rp 1.250.000</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Trend Penjualan</h4>
                        <span class="badge bg-indigo-light text-indigo">14 Hari Terakhir</span>
                    </div>
                    <div class="card-body">
                        <div id="chart-profile-visit"></div>
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
                        <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Foto">
                    </div>
                    <div class="ms-3 name">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="font-bold mb-0">{{ $appShell['actor_label'] ?? 'Asyraf Admin' }}</h5>
                            <span class="text-indigo" title="Terverifikasi">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                        </div>
                        <h6 class="text-muted mb-0" style="font-size: 0.8rem;">{{ $appShell['user_email'] ?? 'admin@asyrafcloud.com' }}</h6>
                    </div>
                </div>
                <hr class="my-4">
                <form action="{{ route('logout') }}" method="post" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent border-0">
                <h6 class="mb-0">System Integrity</h6>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-success p-2 me-3"><i class="bi bi-cpu"></i></div>
                    <div>
                        <small class="text-muted d-block">Database Status</small>
                        <span class="font-bold text-sm">PostgreSQL Connected</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-0">
                    <div class="badge bg-info p-2 me-3"><i class="bi bi-shield-lock"></i></div>
                    <div>
                        <small class="text-muted d-block">Audit Mode</small>
                        <span class="font-bold text-sm">Zero Assumption Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/dashboard.js') }}"></script>
@endpush