@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('heading', 'Dashboard Kasir')

@section('content')
<section class="section">
    <style>
        .cashier-dashboard {
            --cashier-radius-lg: 1rem;
            --cashier-radius-md: .875rem;
            --cashier-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .08);
            --cashier-shadow-hover: 0 1rem 2rem rgba(0, 0, 0, .12);
            --cashier-primary-soft: rgba(var(--bs-primary-rgb), .10);
            --cashier-primary-soft-2: rgba(var(--bs-primary-rgb), .16);
            --cashier-primary-border: rgba(var(--bs-primary-rgb), .22);
            --cashier-muted-bg: var(--bs-secondary-bg);
            --cashier-card-bg: var(--bs-body-bg);
            --cashier-text: var(--bs-body-color);
            --cashier-text-muted: var(--bs-secondary-color);
            --cashier-border: var(--bs-border-color);
        }

        .cashier-hero-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--cashier-border);
            border-radius: var(--cashier-radius-lg);
            background:
                radial-gradient(circle at top right, rgba(var(--bs-primary-rgb), .14), transparent 30%),
                linear-gradient(135deg, var(--cashier-card-bg) 0%, var(--cashier-muted-bg) 100%);
            box-shadow: var(--cashier-shadow);
        }

        .cashier-hero-card::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, transparent 0%, rgba(var(--bs-primary-rgb), .04) 100%);
        }

        .cashier-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem .9rem;
            border-radius: 999px;
            background: var(--cashier-primary-soft);
            color: var(--bs-primary);
            font-size: .875rem;
            font-weight: 600;
            border: 1px solid var(--cashier-primary-border);
        }

        .cashier-action-card {
            height: 100%;
            border: 1px solid var(--cashier-border);
            border-radius: var(--cashier-radius-lg);
            background: var(--cashier-card-bg);
            padding: 1.25rem;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .04);
        }

        .cashier-action-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--cashier-shadow-hover);
            border-color: var(--cashier-primary-border);
        }

        .cashier-action-icon {
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            border: 1px solid transparent;
        }

        .cashier-action-icon.primary {
            background: var(--cashier-primary-soft);
            color: var(--bs-primary);
            border-color: var(--cashier-primary-border);
        }

        .cashier-action-icon.secondary {
            background: rgba(var(--bs-secondary-color-rgb, 108, 117, 125), .10);
            color: var(--cashier-text);
            border-color: var(--cashier-border);
        }

        .cashier-main-btn {
            min-height: 52px;
            border-radius: .85rem;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .cashier-info-box {
            border: 1px dashed var(--cashier-primary-border);
            border-radius: var(--cashier-radius-lg);
            background: var(--cashier-primary-soft);
        }

        .cashier-profile-card {
            overflow: hidden;
            border: 1px solid var(--cashier-border);
            border-radius: var(--cashier-radius-lg);
            background: var(--cashier-card-bg);
            box-shadow: var(--cashier-shadow);
        }

        .cashier-profile-top {
            background: linear-gradient(
                135deg,
                rgba(var(--bs-primary-rgb), .95) 0%,
                rgba(var(--bs-primary-rgb), .75) 100%
            );
            padding: 1.25rem;
        }

        .cashier-avatar-wrap {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .55);
            background: rgba(255, 255, 255, .15);
        }

        .cashier-avatar-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cashier-user-name {
            margin: 0;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .cashier-user-email {
            color: rgba(255, 255, 255, .82);
            font-size: .9rem;
            word-break: break-word;
        }

        .cashier-mini-stat {
            height: 100%;
            border: 1px solid var(--cashier-border);
            border-radius: var(--cashier-radius-md);
            background: var(--cashier-muted-bg);
            padding: .9rem 1rem;
        }

        .cashier-mini-stat-label {
            margin-bottom: .2rem;
            color: var(--cashier-text-muted);
            font-size: .8rem;
        }

        .cashier-mini-stat-value {
            margin: 0;
            color: var(--cashier-text);
            font-size: 1rem;
            font-weight: 700;
        }

        .cashier-section-title {
            color: var(--cashier-text);
        }

        .cashier-section-desc {
            color: var(--cashier-text-muted);
        }

        .cashier-tip-icon {
            color: var(--bs-primary);
            font-size: 1.25rem;
            line-height: 1;
        }
    </style>

    <div class="cashier-dashboard">
        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card cashier-hero-card mb-0">
                    <div class="card-body p-4 p-lg-5">
                        <div class="cashier-hero-badge mb-3">
                            <i class="bi bi-lightning-charge-fill"></i>
                            Workspace Kasir
                        </div>

                        <div class="mb-4">
                            <h3 class="cashier-section-title fw-bold mb-2">
                                Mulai pekerjaan kasir dengan lebih cepat
                            </h3>
                            <p class="cashier-section-desc mb-0">
                                Gunakan dashboard ini sebagai pusat kerja kasir untuk membuat nota baru,
                                membuka nota aktif, dan menjaga alur transaksi tetap cepat, jelas, dan rapi.
                            </p>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="cashier-action-card">
                                    <div class="cashier-action-icon primary">
                                        <i class="bi bi-receipt-cutoff"></i>
                                    </div>

                                    <h5 class="fw-bold mb-2">Buat Nota Baru</h5>
                                    <p class="cashier-section-desc small mb-4">
                                        Gunakan menu ini untuk memulai transaksi baru dari workspace kasir.
                                        Ini adalah aksi utama yang paling sering dipakai.
                                    </p>

                                    <a href="{{ route('cashier.notes.workspace.create') }}" class="btn btn-primary cashier-main-btn w-100">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Mulai Buat Nota
                                    </a>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="cashier-action-card">
                                    <div class="cashier-action-icon secondary">
                                        <i class="bi bi-clock-history"></i>
                                    </div>

                                    <h5 class="fw-bold mb-2">Riwayat Nota Aktif</h5>
                                    <p class="cashier-section-desc small mb-4">
                                        Buka kembali nota open pada window kasir hari ini dan kemarin untuk
                                        melanjutkan transaksi yang belum selesai.
                                    </p>

                                    <a href="{{ route('cashier.notes.index') }}" class="btn btn-outline-primary cashier-main-btn w-100">
                                        <i class="bi bi-journal-text me-2"></i>
                                        Buka Riwayat Nota
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="cashier-info-box p-3 p-lg-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="cashier-tip-icon">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>

                                <div>
                                    <div class="fw-semibold mb-1">Panduan cepat</div>
                                    <div class="cashier-section-desc small mb-0">
                                        Pilih <strong>Buat Nota Baru</strong> untuk transaksi baru.
                                        Pilih <strong>Riwayat Nota Aktif</strong> untuk membuka transaksi
                                        yang masih berjalan.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.col -->

            <div class="col-12 col-xl-4">
                <div class="cashier-profile-card">
                    <div class="cashier-profile-top">
                        <div class="d-flex align-items-center gap-3">
                            <div class="cashier-avatar-wrap">
                                <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" alt="Foto Pengguna">
                            </div>

                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h5 class="cashier-user-name">
                                        {{ $appShell['actor_label'] ?? 'Pengguna' }}
                                    </h5>

                                    <span class="badge bg-light text-primary border border-light-subtle">
                                        <i class="bi bi-patch-check-fill me-1"></i>
                                        Aktif
                                    </span>
                                </div>

                                <div class="cashier-user-email">
                                    {{ $appShell['user_email'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="cashier-mini-stat">
                                    <div class="cashier-mini-stat-label">Role</div>
                                    <p class="cashier-mini-stat-value">Kasir</p>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="cashier-mini-stat">
                                    <div class="cashier-mini-stat-label">Status</div>
                                    <p class="cashier-mini-stat-value">Online</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-1">Akun sedang digunakan</div>
                            <div class="cashier-section-desc small">
                                Pastikan keluar dari akun setelah selesai menggunakan dashboard kasir,
                                terutama bila perangkat dipakai bergantian.
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
            </div><!-- /.col -->
        </div>
    </div>
</section>
@endsection