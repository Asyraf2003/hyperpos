@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('heading', 'Dashboard Kasir')

@section('content')
<section class="section">
    <style>
        .cashier-dashboard {
            --cashier-card: #ffffff;
            --cashier-border: rgba(15, 23, 42, .10);
            --cashier-muted: #64748b;
            --cashier-text: #0f172a;
            --cashier-primary-soft: rgba(var(--bs-primary-rgb), .10);
            --cashier-primary-border: rgba(var(--bs-primary-rgb), .24);
            --cashier-success-soft: rgba(var(--bs-success-rgb), .12);
            --cashier-warning-soft: rgba(var(--bs-warning-rgb), .14);
            --cashier-danger-soft: rgba(var(--bs-danger-rgb), .12);
            --cashier-shadow: 0 .85rem 1.8rem rgba(15, 23, 42, .06);
            max-width: 860px;
            margin: 0 auto;
        }

        .cashier-dashboard,
        .cashier-dashboard input,
        .cashier-dashboard button {
            font-family: "Nunito", "Inter", "Segoe UI", sans-serif;
        }

        .cashier-dashboard-shell {
            display: grid;
            gap: 1rem;
        }

        .cashier-step-card,
        .cashier-result-item,
        .cashier-empty-state {
            border: 1px solid var(--cashier-border);
            border-radius: 1rem;
            background: var(--cashier-card);
            box-shadow: var(--cashier-shadow);
        }

        .cashier-step-header {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
            padding: 1rem 1rem .75rem;
            border-bottom: 1px solid rgba(15, 23, 42, .07);
        }

        .cashier-step-number {
            width: 2.25rem;
            height: 2.25rem;
            flex: 0 0 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: var(--bs-primary);
            background: var(--cashier-primary-soft);
            border: 1px solid var(--cashier-primary-border);
            font-weight: 800;
        }

        .cashier-step-title {
            margin: 0;
            color: var(--cashier-text);
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .cashier-step-help,
        .cashier-section-desc,
        .cashier-result-meta,
        .cashier-search-status {
            color: var(--cashier-muted);
            font-size: .9rem;
            line-height: 1.55;
        }

        .cashier-step-help {
            margin: .18rem 0 0;
        }

        .cashier-step-body {
            padding: 1rem;
        }

        .cashier-dashboard .btn,
        .cashier-dashboard .form-control {
            min-height: 2.75rem;
        }

        .cashier-action-grid,
        .cashier-snapshot-grid {
            display: grid;
            gap: .75rem;
        }

        .cashier-action-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cashier-snapshot-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cashier-action-tile,
        .cashier-snapshot-tile {
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: .85rem;
            background: #fff;
            padding: .9rem;
            min-height: 100%;
        }

        .cashier-action-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: var(--bs-primary);
            background: var(--cashier-primary-soft);
            border: 1px solid var(--cashier-primary-border);
            margin-bottom: .75rem;
        }

        .cashier-main-btn {
            font-weight: 800;
            border-radius: .85rem;
        }

        .cashier-search-input {
            border-radius: .85rem;
            border-color: var(--cashier-border);
        }

        .cashier-search-input:focus {
            border-color: rgba(var(--bs-primary-rgb), .42);
            box-shadow: 0 0 0 .22rem rgba(var(--bs-primary-rgb), .12);
        }

        .cashier-results-wrap {
            display: grid;
            gap: .75rem;
        }

        .cashier-result-item,
        .cashier-empty-state {
            padding: .9rem;
            box-shadow: none;
        }

        .cashier-result-title {
            margin: 0;
            color: var(--cashier-text);
            font-size: .98rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .cashier-result-price {
            color: var(--cashier-text);
            font-weight: 800;
        }

        .cashier-result-stock {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .65rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 800;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .cashier-result-stock.is-good {
            background: var(--cashier-success-soft);
            color: var(--bs-success);
            border-color: rgba(var(--bs-success-rgb), .22);
        }

        .cashier-result-stock.is-low {
            background: var(--cashier-warning-soft);
            color: var(--bs-warning-text-emphasis, var(--bs-warning));
            border-color: rgba(var(--bs-warning-rgb), .25);
        }

        .cashier-result-stock.is-empty {
            background: var(--cashier-danger-soft);
            color: var(--bs-danger);
            border-color: rgba(var(--bs-danger-rgb), .22);
        }

        .cashier-search-spinner {
            width: 1rem;
            height: 1rem;
            margin-right: .35rem;
        }

        .cashier-user-panel {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .cashier-avatar-wrap {
            width: 3rem;
            height: 3rem;
            flex: 0 0 3rem;
            overflow: hidden;
            border-radius: 999px;
            border: 2px solid var(--cashier-primary-border);
            background: var(--cashier-primary-soft);
        }

        .cashier-avatar-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cashier-user-name {
            margin: 0;
            color: var(--cashier-text);
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .cashier-user-email {
            color: var(--cashier-muted);
            font-size: .88rem;
            word-break: break-word;
        }

        @media (max-width: 575.98px) {
            .cashier-dashboard {
                max-width: none;
            }

            .cashier-action-grid,
            .cashier-snapshot-grid {
                grid-template-columns: 1fr;
            }

            .cashier-step-header,
            .cashier-step-body {
                padding-inline: .9rem;
            }
        }
    </style>

    <div
        class="cashier-dashboard"
        data-cashier-dashboard
        data-product-lookup-endpoint="{{ $productLookupEndpoint }}"
    >
        <div class="ui-page-intro">
            <div class="small text-muted text-uppercase fw-semibold">Pusat Kerja Kasir</div>
            <h4 class="ui-page-intro-title">Dashboard Kasir</h4>
            <p class="ui-page-intro-subtitle">
                Mulai nota, buka riwayat, dan cek produk dari satu alur mobile.
            </p>
        </div>

        <div class="cashier-dashboard-shell">
            <div class="cashier-step-card">
                <div class="cashier-step-header">
                    <span class="cashier-step-number">1</span>
                    <div>
                        <h5 class="cashier-step-title">Snapshot Hari Ini</h5>
                        <p class="cashier-step-help">Status kerja kasir pada perangkat ini.</p>
                    </div>
                </div>

                <div class="cashier-step-body">
                    <div class="cashier-snapshot-grid">
                        <div class="cashier-snapshot-tile">
                            <div class="small text-muted mb-1">Role</div>
                            <div class="fw-bold">Kasir</div>
                        </div>

                        <div class="cashier-snapshot-tile">
                            <div class="small text-muted mb-1">Status</div>
                            <div class="fw-bold">Online</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cashier-step-card">
                <div class="cashier-step-header">
                    <span class="cashier-step-number">2</span>
                    <div>
                        <h5 class="cashier-step-title">Aksi Cepat</h5>
                        <p class="cashier-step-help">Pilih pekerjaan utama kasir.</p>
                    </div>
                </div>

                <div class="cashier-step-body">
                    <div class="cashier-action-grid">
                        <div class="cashier-action-tile">
                            <div class="cashier-action-icon">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Buat Nota Baru</h6>
                            <p class="cashier-section-desc small mb-3">
                                Mulai transaksi baru dari workspace nota.
                            </p>
                            <a href="{{ route('cashier.notes.workspace.create') }}" class="btn btn-primary cashier-main-btn w-100">
                                Mulai
                            </a>
                        </div>

                        <div class="cashier-action-tile">
                            <div class="cashier-action-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Riwayat Nota</h6>
                            <p class="cashier-section-desc small mb-3">
                                Buka nota aktif hari ini dan kemarin.
                            </p>
                            <a href="{{ route('cashier.notes.index') }}" class="btn btn-outline-primary cashier-main-btn w-100">
                                Buka
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cashier-step-card">
                <div class="cashier-step-header">
                    <span class="cashier-step-number">3</span>
                    <div>
                        <h5 class="cashier-step-title">Cari Produk Cepat</h5>
                        <p class="cashier-step-help">Cek harga jual dan stok sebelum membuat rincian nota.</p>
                    </div>
                </div>

                <div class="cashier-step-body">
                    <label for="cashier-product-search" class="form-label fw-semibold">
                        Kata kunci produk
                    </label>
                    <input
                        type="search"
                        id="cashier-product-search"
                        class="form-control cashier-search-input mb-3"
                        placeholder="Contoh: oli, kampas, federal, 10W"
                        autocomplete="off"
                        data-product-search-input
                    >

                    <div class="cashier-search-status mb-3" data-search-status>
                        <span>Siap digunakan</span>
                    </div>

                    <div class="cashier-results-wrap" data-product-search-results>
                        <div class="cashier-empty-state">
                            <div class="fw-semibold mb-1">Belum ada pencarian</div>
                            <div class="cashier-section-desc small mb-0">
                                Masukkan kata kunci untuk melihat harga jual dan stok produk yang tersedia.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cashier-step-card">
                <div class="cashier-step-header">
                    <span class="cashier-step-number">4</span>
                    <div>
                        <h5 class="cashier-step-title">Akun</h5>
                        <p class="cashier-step-help">Identitas pengguna yang sedang aktif.</p>
                    </div>
                </div>

                <div class="cashier-step-body">
                    <div class="cashier-user-panel mb-3">
                        <div class="cashier-avatar-wrap">
                            <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Foto Pengguna">
                        </div>

                        <div class="min-w-0">
                            <h5 class="cashier-user-name">
                                {{ $appShell['actor_label'] ?? 'Pengguna' }}
                            </h5>
                            <div class="cashier-user-email">
                                {{ $appShell['user_email'] ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="post" class="d-grid">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger cashier-main-btn">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Keluar Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('assets/static/js/pages/cashier-dashboard.js') }}?v={{ config('app.asset_version') }}"></script>
@endsection
