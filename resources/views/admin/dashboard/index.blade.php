@extends('layouts.app')

@section('title', 'Dashboard Laporan')
@section('heading', 'Dashboard Laporan')

@section('content')
<style>
    :root {
        --dash-primary: #435ebe;
        --dash-primary-soft: #eef2ff;
        --dash-success: #16a34a;
        --dash-success-soft: #ecfdf3;
        --dash-warning: #f59e0b;
        --dash-warning-soft: #fff8e7;
        --dash-danger: #ef4444;
        --dash-danger-soft: #fef2f2;
        --dash-info: #06b6d4;
        --dash-info-soft: #ecfeff;
        --dash-dark: #111827;
        --dash-muted: #6b7280;
        --dash-border: #e5e7eb;
        --dash-surface: #ffffff;
        --dash-bg: #f7f9fc;
    }

    .dashboard-report * {
        box-sizing: border-box;
    }

    .dashboard-report .card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .dashboard-report .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dash-dark);
        margin-bottom: .25rem;
    }

    .dashboard-report .section-subtitle {
        font-size: .88rem;
        color: var(--dash-muted);
        margin-bottom: 0;
    }

    .dashboard-report .hero-card {
        position: relative;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.22), transparent 32%),
            radial-gradient(circle at bottom left, rgba(255,255,255,.14), transparent 30%),
            linear-gradient(135deg, #435ebe 0%, #5f7df3 55%, #7c94ff 100%);
        color: #fff;
    }

    .dashboard-report .hero-card::after {
        content: "";
        position: absolute;
        right: -40px;
        top: -30px;
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,.08);
        border-radius: 50%;
    }

    .dashboard-report .hero-card::before {
        content: "";
        position: absolute;
        right: 70px;
        bottom: -70px;
        width: 160px;
        height: 160px;
        background: rgba(255,255,255,.07);
        border-radius: 50%;
    }

    .dashboard-report .hero-content {
        position: relative;
        z-index: 2;
    }

    .dashboard-report .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .55rem .9rem;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        color: #fff;
        font-size: .82rem;
        font-weight: 600;
        backdrop-filter: blur(6px);
    }

    .dashboard-report .hero-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .dashboard-report .hero-metric {
        padding: 1rem 1.1rem;
        border-radius: 18px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.12);
        backdrop-filter: blur(6px);
    }

    .dashboard-report .hero-metric-label {
        font-size: .78rem;
        opacity: .88;
        margin-bottom: .25rem;
    }

    .dashboard-report .hero-metric-value {
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: .2px;
    }

    .dashboard-report .hero-actions {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dashboard-report .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .65rem .9rem;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.12);
        color: #fff;
        font-size: .82rem;
        font-weight: 600;
    }

    .dashboard-report .stat-card {
        height: 100%;
        background: var(--dash-surface);
    }

    .dashboard-report .stat-card-body {
        padding: 1.2rem;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .dashboard-report .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .dashboard-report .stat-title {
        font-size: .86rem;
        font-weight: 600;
        color: var(--dash-muted);
        margin-bottom: .3rem;
    }

    .dashboard-report .stat-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--dash-dark);
        line-height: 1.2;
        margin-bottom: .35rem;
    }

    .dashboard-report .stat-meta {
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    .dashboard-report .meta-up {
        color: var(--dash-success);
    }

    .dashboard-report .meta-down {
        color: var(--dash-danger);
    }

    .dashboard-report .meta-flat {
        color: var(--dash-warning);
    }

    .dashboard-report .bg-soft-primary {
        background: var(--dash-primary-soft);
        color: var(--dash-primary);
    }

    .dashboard-report .bg-soft-success {
        background: var(--dash-success-soft);
        color: var(--dash-success);
    }

    .dashboard-report .bg-soft-warning {
        background: var(--dash-warning-soft);
        color: var(--dash-warning);
    }

    .dashboard-report .bg-soft-danger {
        background: var(--dash-danger-soft);
        color: var(--dash-danger);
    }

    .dashboard-report .bg-soft-info {
        background: var(--dash-info-soft);
        color: var(--dash-info);
    }

    .dashboard-report .chart-card-body,
    .dashboard-report .panel-card-body {
        padding: 1.25rem;
    }

    .dashboard-report .card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .dashboard-report .card-toolbar {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .dashboard-report .toolbar-pill {
        padding: .45rem .75rem;
        border-radius: 999px;
        border: 1px solid var(--dash-border);
        background: #fff;
        color: var(--dash-muted);
        font-size: .78rem;
        font-weight: 700;
    }

    .dashboard-report .toolbar-pill.active {
        background: var(--dash-primary-soft);
        color: var(--dash-primary);
        border-color: rgba(67, 94, 190, .18);
    }

    .dashboard-report .chart-shell {
        position: relative;
        min-height: 320px;
        border-radius: 18px;
        background:
            linear-gradient(to top, rgba(67, 94, 190, .04), rgba(67, 94, 190, .01)),
            #fbfcff;
        border: 1px solid #edf1f7;
        padding: 1rem 1rem .75rem;
    }

    .dashboard-report .chart-header-inline {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: .75rem;
    }

    .dashboard-report .legend-item {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .82rem;
        color: var(--dash-muted);
        font-weight: 600;
    }

    .dashboard-report .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dashboard-report .chart-svg {
        width: 100%;
        height: 230px;
        display: block;
    }

    .dashboard-report .axis-labels {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: .25rem;
        font-size: .78rem;
        color: var(--dash-muted);
        font-weight: 600;
        margin-top: .25rem;
        text-align: center;
    }

    .dashboard-report .trend-list {
        display: grid;
        gap: .85rem;
    }

    .dashboard-report .trend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .9rem 1rem;
        border-radius: 16px;
        background: #fbfcff;
        border: 1px solid #edf1f7;
    }

    .dashboard-report .trend-item-left {
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .dashboard-report .trend-badge {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .dashboard-report .trend-label {
        font-size: .86rem;
        color: var(--dash-muted);
        margin-bottom: .15rem;
    }

    .dashboard-report .trend-value {
        font-size: 1rem;
        font-weight: 800;
        color: var(--dash-dark);
        margin: 0;
    }

    .dashboard-report .stock-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 1rem;
        align-items: center;
    }

    .dashboard-report .stock-ring-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .dashboard-report .stock-ring {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background:
            conic-gradient(
                var(--dash-primary) 0 46%,
                var(--dash-success) 46% 73%,
                var(--dash-warning) 73% 88%,
                #dbe3ff 88% 100%
            );
        display: grid;
        place-items: center;
        position: relative;
    }

    .dashboard-report .stock-ring::after {
        content: "";
        width: 118px;
        height: 118px;
        border-radius: 50%;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgba(17, 24, 39, .04);
    }

    .dashboard-report .stock-ring-center {
        position: absolute;
        text-align: center;
        z-index: 2;
    }

    .dashboard-report .stock-ring-center h3 {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--dash-dark);
        margin-bottom: .15rem;
    }

    .dashboard-report .stock-ring-center p {
        margin: 0;
        font-size: .82rem;
        color: var(--dash-muted);
        font-weight: 600;
    }

    .dashboard-report .inventory-list {
        display: grid;
        gap: .85rem;
    }

    .dashboard-report .inventory-item {
        padding: .9rem 1rem;
        border-radius: 16px;
        background: #fbfcff;
        border: 1px solid #edf1f7;
    }

    .dashboard-report .inventory-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .55rem;
    }

    .dashboard-report .inventory-title {
        font-size: .92rem;
        font-weight: 700;
        color: var(--dash-dark);
        margin: 0;
    }

    .dashboard-report .inventory-meta {
        font-size: .78rem;
        color: var(--dash-muted);
        font-weight: 600;
        margin: 0;
    }

    .dashboard-report .progress-slim {
        height: 8px;
        border-radius: 999px;
        background: #e8edf5;
        overflow: hidden;
    }

    .dashboard-report .progress-slim > span {
        display: block;
        height: 100%;
        border-radius: 999px;
    }

    .dashboard-report .table-modern {
        margin-bottom: 0;
        vertical-align: middle;
    }

    .dashboard-report .table-modern thead th {
        border-bottom: 1px solid var(--dash-border);
        color: var(--dash-muted);
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 800;
        padding-top: .85rem;
        padding-bottom: .85rem;
        white-space: nowrap;
    }

    .dashboard-report .table-modern tbody td {
        padding-top: 1rem;
        padding-bottom: 1rem;
        border-color: #f0f2f6;
    }

    .dashboard-report .product-inline {
        display: flex;
        align-items: center;
        gap: .8rem;
    }

    .dashboard-report .product-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .dashboard-report .product-name {
        font-size: .92rem;
        font-weight: 700;
        color: var(--dash-dark);
        margin-bottom: .15rem;
    }

    .dashboard-report .product-sku {
        font-size: .78rem;
        color: var(--dash-muted);
        margin: 0;
    }

    .dashboard-report .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .7rem;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .dashboard-report .finance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .dashboard-report .finance-box {
        padding: 1rem;
        border-radius: 18px;
        border: 1px solid #edf1f7;
        background: #fbfcff;
    }

    .dashboard-report .finance-label {
        font-size: .82rem;
        color: var(--dash-muted);
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .dashboard-report .finance-value {
        font-size: 1.15rem;
        color: var(--dash-dark);
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .dashboard-report .finance-note {
        font-size: .78rem;
        font-weight: 700;
        margin: 0;
    }

    .dashboard-report .rotation-list {
        display: grid;
        gap: .9rem;
    }

    .dashboard-report .rotation-item {
        display: grid;
        grid-template-columns: 132px 1fr 78px;
        align-items: center;
        gap: .85rem;
    }

    .dashboard-report .rotation-label {
        font-size: .83rem;
        font-weight: 700;
        color: var(--dash-dark);
        margin: 0;
    }

    .dashboard-report .rotation-percent {
        font-size: .8rem;
        color: var(--dash-muted);
        font-weight: 800;
        text-align: right;
        margin: 0;
    }

    .dashboard-report .asset-list {
        display: grid;
        gap: .85rem;
    }

    .dashboard-report .asset-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .95rem 1rem;
        border-radius: 16px;
        border: 1px solid #edf1f7;
        background: #fbfcff;
    }

    .dashboard-report .asset-left {
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .dashboard-report .asset-title {
        font-size: .92rem;
        font-weight: 700;
        color: var(--dash-dark);
        margin-bottom: .15rem;
    }

    .dashboard-report .asset-subtitle {
        font-size: .78rem;
        color: var(--dash-muted);
        margin: 0;
    }

    .dashboard-report .asset-value {
        font-size: .95rem;
        font-weight: 800;
        color: var(--dash-dark);
        margin: 0;
    }

    .dashboard-report .profile-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
    }

    .dashboard-report .profile-top {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dashboard-report .profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        object-fit: cover;
        display: block;
        box-shadow: 0 10px 20px rgba(67, 94, 190, .16);
    }

    .dashboard-report .profile-name {
        font-size: 1.02rem;
        font-weight: 800;
        color: var(--dash-dark);
        margin-bottom: .2rem;
    }

    .dashboard-report .profile-mail {
        font-size: .82rem;
        color: var(--dash-muted);
        margin-bottom: .5rem;
    }

    .dashboard-report .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .7rem;
        border-radius: 999px;
        background: var(--dash-primary-soft);
        color: var(--dash-primary);
        font-size: .76rem;
        font-weight: 800;
    }

    .dashboard-report .summary-strip {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
        margin-top: 1rem;
    }

    .dashboard-report .summary-mini {
        padding: .85rem .9rem;
        border-radius: 16px;
        background: #fbfcff;
        border: 1px solid #edf1f7;
    }

    .dashboard-report .summary-mini-label {
        font-size: .78rem;
        color: var(--dash-muted);
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .dashboard-report .summary-mini-value {
        font-size: 1rem;
        font-weight: 800;
        color: var(--dash-dark);
        margin: 0;
    }

    .dashboard-report .helper-note {
        padding: .9rem 1rem;
        border-radius: 16px;
        background: linear-gradient(90deg, rgba(67, 94, 190, .08), rgba(6, 182, 212, .08));
        color: #334155;
        font-size: .83rem;
        font-weight: 600;
        border: 1px solid rgba(67, 94, 190, .08);
    }

    @media (max-width: 1199.98px) {
        .dashboard-report .hero-grid,
        .dashboard-report .finance-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-report .stock-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-report .hero-grid,
        .dashboard-report .finance-grid,
        .dashboard-report .summary-strip {
            grid-template-columns: 1fr;
        }

        .dashboard-report .hero-actions {
            justify-content: flex-start;
        }

        .dashboard-report .rotation-item {
            grid-template-columns: 1fr;
        }

        .dashboard-report .card-head {
            flex-direction: column;
        }
    }
</style>

<div class="dashboard-report">
    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-9">
            <div class="card hero-card h-100">
                <div class="card-body p-4 p-lg-5 hero-content">
                    <div class="row g-4 align-items-start">
                        <div class="col-12 col-lg-8">
                            <span class="hero-badge">
                                <i class="bi bi-stars"></i>
                                Dashboard Insight Bisnis
                            </span>
                            <h2 class="mt-3 mb-2 fw-bold text-white">
                                Laporan stok, aset, penjualan, harga, dan perputaran keuangan dalam satu layar.
                            </h2>
                            <p class="mb-0 text-white" style="opacity:.88;">
                                Tampilan ini fokus ke ringkasan visual yang cepat dibaca. Belum ada query atau logic backend, jadi aman untuk tahap UI-only.
                            </p>

                            <div class="hero-grid">
                                <div class="hero-metric">
                                    <div class="hero-metric-label">Total Penjualan Bulan Ini</div>
                                    <h4 class="hero-metric-value">Rp 124.850.000</h4>
                                </div>
                                <div class="hero-metric">
                                    <div class="hero-metric-label">Barang Terjual</div>
                                    <h4 class="hero-metric-value">1.284 Unit</h4>
                                </div>
                                <div class="hero-metric">
                                    <div class="hero-metric-label">Perputaran Kas</div>
                                    <h4 class="hero-metric-value">4.7x / Bulan</h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="hero-actions">
                                <span class="filter-chip"><i class="bi bi-calendar-day"></i> Hari Ini</span>
                                <span class="filter-chip"><i class="bi bi-calendar-week"></i> Mingguan</span>
                                <span class="filter-chip"><i class="bi bi-bar-chart-line"></i> Bulanan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-3">
            <div class="card profile-card h-100">
                <div class="card-body p-4">
                    <div class="profile-top">
                        <img
                            src="{{ asset('assets/compiled/jpg/1.jpg') }}"
                            alt="Foto profil pengguna"
                            class="profile-avatar"
                            width="64"
                            height="64"
                            loading="lazy"
                            decoding="async"
                        >
                        <div>
                            <div class="profile-name">{{ $appShell['actor_label'] ?? 'Asyraf Admin' }}</div>
                            <div class="profile-mail">{{ $appShell['user_email'] ?? 'admin@asyrafcloud.com' }}</div>
                            <span class="profile-badge">
                                <i class="bi bi-patch-check-fill"></i>
                                Akun Aktif
                            </span>
                        </div>
                    </div>

                    <div class="summary-strip">
                        <div class="summary-mini">
                            <div class="summary-mini-label">Cabang</div>
                            <p class="summary-mini-value">Pusat</p>
                        </div>
                        <div class="summary-mini">
                            <div class="summary-mini-label">Status</div>
                            <p class="summary-mini-value">Online</p>
                        </div>
                    </div>

                    <div class="helper-note mt-3">
                        Fokus utama hari ini: stok aman, harga sehat, kas bergerak, dan produk laris tetap terpantau.
                    </div>

                    <hr class="my-4">

                    <form action="{{ route('logout') }}" method="post" class="d-grid">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="stat-card-body">
                    <div class="stat-icon bg-soft-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="stat-title">Laporan Stok Barang</div>
                        <div class="stat-value">2.480 Item</div>
                        <p class="stat-meta meta-up">
                            <i class="bi bi-arrow-up-right"></i>
                            Naik 8.4% dari minggu lalu
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="stat-card-body">
                    <div class="stat-icon bg-soft-success">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div>
                        <div class="stat-title">Nilai Aset</div>
                        <div class="stat-value">Rp 845.000.000</div>
                        <p class="stat-meta meta-up">
                            <i class="bi bi-arrow-up-right"></i>
                            Aset produktif dominan
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="stat-card-body">
                    <div class="stat-icon bg-soft-warning">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <div class="stat-title">Uang Masuk</div>
                        <div class="stat-value">Rp 38.750.000</div>
                        <p class="stat-meta meta-up">
                            <i class="bi bi-arrow-up-right"></i>
                            Cash in hari ini kuat
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="stat-card-body">
                    <div class="stat-icon bg-soft-info">
                        <i class="bi bi-repeat"></i>
                    </div>
                    <div>
                        <div class="stat-title">Perputaran Keuangan</div>
                        <div class="stat-value">4.7x</div>
                        <p class="stat-meta meta-flat">
                            <i class="bi bi-dash-circle"></i>
                            Stabil dibanding periode lalu
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="chart-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Grafik Penjualan</h5>
                            <p class="section-subtitle">Visual penjualan 7 periode terakhir</p>
                        </div>
                        <div class="card-toolbar">
                            <span class="toolbar-pill active">Revenue</span>
                            <span class="toolbar-pill">Order</span>
                            <span class="toolbar-pill">Unit</span>
                        </div>
                    </div>

                    <div class="chart-shell">
                        <div class="chart-header-inline">
                            <span class="legend-item">
                                <span class="legend-dot" style="background:#435ebe;"></span>
                                Penjualan
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot" style="background:#16a34a;"></span>
                                Target
                            </span>
                        </div>

                        <svg class="chart-svg" viewBox="0 0 900 260" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Grafik penjualan">
                            <line x1="40" y1="220" x2="860" y2="220" stroke="#DCE3F1" stroke-width="1.5"/>
                            <line x1="40" y1="175" x2="860" y2="175" stroke="#EDF1F7" stroke-width="1.2"/>
                            <line x1="40" y1="130" x2="860" y2="130" stroke="#EDF1F7" stroke-width="1.2"/>
                            <line x1="40" y1="85" x2="860" y2="85" stroke="#EDF1F7" stroke-width="1.2"/>
                            <line x1="40" y1="40" x2="860" y2="40" stroke="#EDF1F7" stroke-width="1.2"/>

                            <path d="M70 195 C120 170, 155 120, 200 130 C245 140, 285 85, 330 90 C380 96, 415 150, 460 138 C505 126, 545 68, 590 60 C640 52, 680 110, 725 102 C770 95, 805 38, 850 48 L850 220 L70 220 Z"
                                  fill="url(#areaFill)"/>

                            <path d="M70 195 C120 170, 155 120, 200 130 C245 140, 285 85, 330 90 C380 96, 415 150, 460 138 C505 126, 545 68, 590 60 C640 52, 680 110, 725 102 C770 95, 805 38, 850 48"
                                  stroke="#435ebe" stroke-width="4.5" stroke-linecap="round"/>

                            <path d="M70 180 C120 165, 155 150, 200 145 C245 140, 285 120, 330 118 C380 116, 415 124, 460 115 C505 106, 545 96, 590 92 C640 88, 680 83, 725 78 C770 73, 805 68, 850 66"
                                  stroke="#16a34a" stroke-width="3.2" stroke-dasharray="8 8" stroke-linecap="round"/>

                            <circle cx="70" cy="195" r="6" fill="#435ebe"/>
                            <circle cx="200" cy="130" r="6" fill="#435ebe"/>
                            <circle cx="330" cy="90" r="6" fill="#435ebe"/>
                            <circle cx="460" cy="138" r="6" fill="#435ebe"/>
                            <circle cx="590" cy="60" r="6" fill="#435ebe"/>
                            <circle cx="725" cy="102" r="6" fill="#435ebe"/>
                            <circle cx="850" cy="48" r="6" fill="#435ebe"/>

                            <defs>
                                <linearGradient id="areaFill" x1="460" y1="40" x2="460" y2="220" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#435EBE" stop-opacity="0.28"/>
                                    <stop offset="1" stop-color="#435EBE" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                        </svg>

                        <div class="axis-labels">
                            <span>Sen</span>
                            <span>Sel</span>
                            <span>Rab</span>
                            <span>Kam</span>
                            <span>Jum</span>
                            <span>Sab</span>
                            <span>Min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Trend</h5>
                            <p class="section-subtitle">Arah performa harian yang perlu diperhatikan</p>
                        </div>
                        <span class="badge-soft bg-soft-primary">
                            <i class="bi bi-activity"></i>
                            Live UI
                        </span>
                    </div>

                    <div class="trend-list">
                        <div class="trend-item">
                            <div class="trend-item-left">
                                <span class="trend-badge bg-soft-success">
                                    <i class="bi bi-arrow-up-right"></i>
                                </span>
                                <div>
                                    <div class="trend-label">Penjualan naik</div>
                                    <p class="trend-value">+18.6%</p>
                                </div>
                            </div>
                            <span class="badge-soft bg-soft-success">Positif</span>
                        </div>

                        <div class="trend-item">
                            <div class="trend-item-left">
                                <span class="trend-badge bg-soft-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </span>
                                <div>
                                    <div class="trend-label">Stok tipis kategori cepat laku</div>
                                    <p class="trend-value">12 Item</p>
                                </div>
                            </div>
                            <span class="badge-soft bg-soft-warning">Waspada</span>
                        </div>

                        <div class="trend-item">
                            <div class="trend-item-left">
                                <span class="trend-badge bg-soft-info">
                                    <i class="bi bi-cash-stack"></i>
                                </span>
                                <div>
                                    <div class="trend-label">Perputaran kas</div>
                                    <p class="trend-value">Sehat</p>
                                </div>
                            </div>
                            <span class="badge-soft bg-soft-info">Stabil</span>
                        </div>

                        <div class="trend-item">
                            <div class="trend-item-left">
                                <span class="trend-badge bg-soft-danger">
                                    <i class="bi bi-arrow-down-right"></i>
                                </span>
                                <div>
                                    <div class="trend-label">Margin produk tertentu</div>
                                    <p class="trend-value">-4.2%</p>
                                </div>
                            </div>
                            <span class="badge-soft bg-soft-danger">Turun</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Laporan Stok Barang</h5>
                            <p class="section-subtitle">Ringkasan stok aman, stok menipis, dan stok kritis</p>
                        </div>
                        <span class="badge-soft bg-soft-primary">
                            <i class="bi bi-boxes"></i>
                            Inventory
                        </span>
                    </div>

                    <div class="stock-grid">
                        <div class="stock-ring-wrap">
                            <div class="stock-ring">
                                <div class="stock-ring-center">
                                    <h3>2.480</h3>
                                    <p>Total SKU</p>
                                </div>
                            </div>
                        </div>

                        <div class="inventory-list">
                            <div class="inventory-item">
                                <div class="inventory-top">
                                    <div>
                                        <p class="inventory-title">Stok Aman</p>
                                        <p class="inventory-meta">Barang dengan persediaan sehat</p>
                                    </div>
                                    <span class="badge-soft bg-soft-success">1.140 Item</span>
                                </div>
                                <div class="progress-slim">
                                    <span style="width: 78%; background:#16a34a;"></span>
                                </div>
                            </div>

                            <div class="inventory-item">
                                <div class="inventory-top">
                                    <div>
                                        <p class="inventory-title">Stok Menipis</p>
                                        <p class="inventory-meta">Perlu restock dalam waktu dekat</p>
                                    </div>
                                    <span class="badge-soft bg-soft-warning">286 Item</span>
                                </div>
                                <div class="progress-slim">
                                    <span style="width: 44%; background:#f59e0b;"></span>
                                </div>
                            </div>

                            <div class="inventory-item">
                                <div class="inventory-top">
                                    <div>
                                        <p class="inventory-title">Stok Kritis</p>
                                        <p class="inventory-meta">Prioritas pembelian atau suplai</p>
                                    </div>
                                    <span class="badge-soft bg-soft-danger">74 Item</span>
                                </div>
                                <div class="progress-slim">
                                    <span style="width: 19%; background:#ef4444;"></span>
                                </div>
                            </div>

                            <div class="inventory-item">
                                <div class="inventory-top">
                                    <div>
                                        <p class="inventory-title">Barang Slow Moving</p>
                                        <p class="inventory-meta">Lama tersimpan dan perlu perhatian</p>
                                    </div>
                                    <span class="badge-soft bg-soft-info">132 Item</span>
                                </div>
                                <div class="progress-slim">
                                    <span style="width: 31%; background:#06b6d4;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Barang Paling Laku</h5>
                            <p class="section-subtitle">Produk dengan kontribusi penjualan tertinggi</p>
                        </div>
                        <span class="badge-soft bg-soft-success">
                            <i class="bi bi-trophy"></i>
                            Top Sales
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Terjual</th>
                                    <th>Omzet</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="product-inline">
                                            <span class="product-avatar bg-soft-primary">A</span>
                                            <div>
                                                <div class="product-name">Oli Mesin Racing</div>
                                                <p class="product-sku">SKU-OLI-001</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>328 Unit</td>
                                    <td>Rp 24.600.000</td>
                                    <td><span class="badge-soft bg-soft-success"><i class="bi bi-arrow-up-right"></i> 12%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-inline">
                                            <span class="product-avatar bg-soft-warning">B</span>
                                            <div>
                                                <div class="product-name">Ban Tubeless Sport</div>
                                                <p class="product-sku">SKU-BAN-014</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>241 Unit</td>
                                    <td>Rp 31.330.000</td>
                                    <td><span class="badge-soft bg-soft-success"><i class="bi bi-arrow-up-right"></i> 9%</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-inline">
                                            <span class="product-avatar bg-soft-info">C</span>
                                            <div>
                                                <div class="product-name">Kampas Rem Premium</div>
                                                <p class="product-sku">SKU-REM-032</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>188 Unit</td>
                                    <td>Rp 14.100.000</td>
                                    <td><span class="badge-soft bg-soft-primary"><i class="bi bi-graph-up"></i> Stabil</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-inline">
                                            <span class="product-avatar bg-soft-danger">D</span>
                                            <div>
                                                <div class="product-name">Aki Motor 12V</div>
                                                <p class="product-sku">SKU-AKI-011</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>142 Unit</td>
                                    <td>Rp 22.010.000</td>
                                    <td><span class="badge-soft bg-soft-warning"><i class="bi bi-exclamation-circle"></i> Hot</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-inline">
                                            <span class="product-avatar bg-soft-success">E</span>
                                            <div>
                                                <div class="product-name">Lampu LED White</div>
                                                <p class="product-sku">SKU-LMP-008</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>124 Unit</td>
                                    <td>Rp 8.680.000</td>
                                    <td><span class="badge-soft bg-soft-danger"><i class="bi bi-arrow-down-right"></i> 3%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Harga & Performa Margin</h5>
                            <p class="section-subtitle">Bagian “kemudian harga” yang kamu minta, saya taruh jadi panel sendiri</p>
                        </div>
                        <span class="badge-soft bg-soft-warning">
                            <i class="bi bi-tags"></i>
                            Price Watch
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Oli Mesin Racing</td>
                                    <td>Rp 52.000</td>
                                    <td>Rp 75.000</td>
                                    <td><span class="badge-soft bg-soft-success">30.6%</span></td>
                                </tr>
                                <tr>
                                    <td>Ban Tubeless Sport</td>
                                    <td>Rp 410.000</td>
                                    <td>Rp 520.000</td>
                                    <td><span class="badge-soft bg-soft-success">21.1%</span></td>
                                </tr>
                                <tr>
                                    <td>Kampas Rem Premium</td>
                                    <td>Rp 44.000</td>
                                    <td>Rp 68.000</td>
                                    <td><span class="badge-soft bg-soft-success">35.3%</span></td>
                                </tr>
                                <tr>
                                    <td>Aki Motor 12V</td>
                                    <td>Rp 315.000</td>
                                    <td>Rp 390.000</td>
                                    <td><span class="badge-soft bg-soft-warning">19.2%</span></td>
                                </tr>
                                <tr>
                                    <td>Lampu LED White</td>
                                    <td>Rp 24.000</td>
                                    <td>Rp 35.000</td>
                                    <td><span class="badge-soft bg-soft-danger">31.4%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="helper-note mt-3">
                        Nanti kalau backend sudah siap, bagian ini paling enak diisi dari harga beli, harga jual, dan margin per SKU.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Keuangan & Perputarannya</h5>
                            <p class="section-subtitle">Bagian posisi kas dan seberapa cepat uang berputar</p>
                        </div>
                        <span class="badge-soft bg-soft-info">
                            <i class="bi bi-bank"></i>
                            Cash Flow
                        </span>
                    </div>

                    <div class="finance-grid">
                        <div class="finance-box">
                            <div class="finance-label">Kas Masuk</div>
                            <div class="finance-value">Rp 38.750.000</div>
                            <p class="finance-note meta-up">
                                <i class="bi bi-arrow-up-right"></i>
                                Order dan pembayaran masuk
                            </p>
                        </div>

                        <div class="finance-box">
                            <div class="finance-label">Kas Keluar</div>
                            <div class="finance-value">Rp 21.420.000</div>
                            <p class="finance-note meta-down">
                                <i class="bi bi-arrow-down-right"></i>
                                Pembelian stok dan operasional
                            </p>
                        </div>

                        <div class="finance-box">
                            <div class="finance-label">Perputaran Modal</div>
                            <div class="finance-value">Rp 97.300.000</div>
                            <p class="finance-note meta-flat">
                                <i class="bi bi-arrow-repeat"></i>
                                Terjadi dalam satu periode
                            </p>
                        </div>

                        <div class="finance-box">
                            <div class="finance-label">Net Cash Flow</div>
                            <div class="finance-value">Rp 17.330.000</div>
                            <p class="finance-note meta-up">
                                <i class="bi bi-graph-up-arrow"></i>
                                Posisi kas positif
                            </p>
                        </div>
                    </div>

                    <div class="rotation-list">
                        <div class="rotation-item">
                            <p class="rotation-label">Kas Operasional</p>
                            <div class="progress-slim">
                                <span style="width: 82%; background:#435ebe;"></span>
                            </div>
                            <p class="rotation-percent">82%</p>
                        </div>

                        <div class="rotation-item">
                            <p class="rotation-label">Modal Berputar</p>
                            <div class="progress-slim">
                                <span style="width: 74%; background:#16a34a;"></span>
                            </div>
                            <p class="rotation-percent">74%</p>
                        </div>

                        <div class="rotation-item">
                            <p class="rotation-label">Aset Produktif</p>
                            <div class="progress-slim">
                                <span style="width: 68%; background:#06b6d4;"></span>
                            </div>
                            <p class="rotation-percent">68%</p>
                        </div>

                        <div class="rotation-item">
                            <p class="rotation-label">Piutang Tertagih</p>
                            <div class="progress-slim">
                                <span style="width: 59%; background:#f59e0b;"></span>
                            </div>
                            <p class="rotation-percent">59%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="panel-card-body">
                    <div class="card-head">
                        <div>
                            <h5 class="section-title">Laporan Aset</h5>
                            <p class="section-subtitle">Komposisi aset usaha yang tampil simpel tapi tetap kelihatan berkelas</p>
                        </div>
                        <span class="badge-soft bg-soft-primary">
                            <i class="bi bi-clipboard-data"></i>
                            Asset Overview
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="asset-list">
                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-primary"><i class="bi bi-box2-heart"></i></span>
                                        <div>
                                            <div class="asset-title">Persediaan Barang</div>
                                            <p class="asset-subtitle">Nilai stok yang tersedia</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 320.000.000</p>
                                </div>

                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-success"><i class="bi bi-cash-coin"></i></span>
                                        <div>
                                            <div class="asset-title">Kas & Bank</div>
                                            <p class="asset-subtitle">Saldo likuid usaha</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 145.000.000</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="asset-list">
                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-warning"><i class="bi bi-tools"></i></span>
                                        <div>
                                            <div class="asset-title">Peralatan Operasional</div>
                                            <p class="asset-subtitle">Mesin, tools, workstation</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 210.000.000</p>
                                </div>

                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-info"><i class="bi bi-truck"></i></span>
                                        <div>
                                            <div class="asset-title">Kendaraan Operasional</div>
                                            <p class="asset-subtitle">Distribusi dan support</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 95.000.000</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="asset-list">
                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-danger"><i class="bi bi-receipt-cutoff"></i></span>
                                        <div>
                                            <div class="asset-title">Piutang Dagang</div>
                                            <p class="asset-subtitle">Tagihan yang belum lunas</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 75.000.000</p>
                                </div>

                                <div class="asset-item">
                                    <div class="asset-left">
                                        <span class="product-avatar bg-soft-primary"><i class="bi bi-pie-chart"></i></span>
                                        <div>
                                            <div class="asset-title">Aset Lainnya</div>
                                            <p class="asset-subtitle">Cadangan dan kebutuhan lain</p>
                                        </div>
                                    </div>
                                    <p class="asset-value">Rp 45.000.000</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="helper-note mt-4">
                        Struktur UI ini sudah memisahkan stok, aset, uang masuk, barang paling laku, grafik penjualan, trend, harga, dan perputaran keuangan. Jadi nanti tinggal kamu sambungkan ke data backend tanpa bongkar layout lagi.
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection