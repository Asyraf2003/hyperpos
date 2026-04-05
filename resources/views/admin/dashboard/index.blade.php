@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('heading', 'Admin Dashboard')

@section('content')
<style>
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-indigo-light {
        background-color: #f5f3ff;
        color: #4f46e5;
    }

    .bg-emerald-light {
        background-color: #ecfdf5;
        color: #059669;
    }

    .bg-amber-light {
        background-color: #fffbeb;
        color: #d97706;
    }

    .text-indigo {
        color: #4f46e5 !important;
    }

    .dashboard-placeholder {
        border: 1px dashed #dfe3ea;
        border-radius: 14px;
        background: #fbfcfe;
        padding: 1.25rem;
    }

    .dashboard-placeholder-grid {
        display: grid;
        grid-template-columns: repeat(14, minmax(0, 1fr));
        align-items: end;
        gap: 0.5rem;
        min-height: 220px;
        margin-top: 1rem;
    }

    .dashboard-placeholder-bar {
        border-radius: 999px 999px 0 0;
        background: linear-gradient(180deg, rgba(67, 94, 190, 0.9) 0%, rgba(67, 94, 190, 0.35) 100%);
        min-height: 36px;
    }

    .dashboard-placeholder-note {
        font-size: 0.95rem;
    }

    .dashboard-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    .dashboard-email {
        font-size: 0.875rem;
    }
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
                                <h5 class="font-extrabold mb-0">Rp 1.111.111</h5>
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
                                <h5 class="font-extrabold mb-0 text-success">Rp 1.111.111</h5>
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
                                <h5 class="font-extrabold mb-0 text-warning">Rp 1.111.111</h5>
                            </div>
                        </div>
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
                        <img
                            src="{{ asset('assets/compiled/jpg/1.jpg') }}"
                            alt="Foto profil pengguna"
                            class="dashboard-avatar"
                            width="60"
                            height="60"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="ms-3 name">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="font-bold mb-0">{{ $appShell['actor_label'] ?? 'Asyraf Admin' }}</h5>
                            <span class="text-indigo" title="Terverifikasi">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                        </div>
                        <h6 class="text-muted mb-0 dashboard-email">{{ $appShell['user_email'] ?? 'admin@asyrafcloud.com' }}</h6>
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
    </div>
</section>
@endsection
