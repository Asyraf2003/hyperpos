@extends('layouts.app')

@section('title', 'Preferensi Akun')
@section('heading', 'Preferensi Akun')
@section('back_url', route('cashier.dashboard'))

@section('content')
<section class="section">
    <style>
        .cashier-account {
            max-width: 720px;
            margin: 0 auto;
        }

        .cashier-account-card {
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 .85rem 1.8rem rgba(15, 23, 42, .06);
        }

        .cashier-account-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem 0;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
        }

        .cashier-account-row:last-child {
            border-bottom: 0;
        }

        .cashier-account-label {
            color: #64748b;
            font-size: .9rem;
        }

        .cashier-account-value {
            color: #0f172a;
            font-weight: 800;
            text-align: right;
            word-break: break-word;
        }

        .cashier-account .btn {
            min-height: 2.75rem;
            border-radius: .85rem;
            font-weight: 800;
        }
    </style>

    <div class="cashier-account">
        <div class="ui-page-intro">
            <div class="small text-muted text-uppercase fw-semibold">Kasir</div>
            <h4 class="ui-page-intro-title">Preferensi Akun</h4>
            <p class="ui-page-intro-subtitle">Informasi akun yang sedang digunakan.</p>
        </div>

        <div class="cashier-account-card">
            <div class="cashier-account-row">
                <div class="cashier-account-label">Nama</div>
                <div class="cashier-account-value">{{ $appShell['actor_label'] ?? 'Pengguna' }}</div>
            </div>

            <div class="cashier-account-row">
                <div class="cashier-account-label">Email</div>
                <div class="cashier-account-value">{{ $appShell['user_email'] ?? '-' }}</div>
            </div>

            <div class="cashier-account-row">
                <div class="cashier-account-label">Role</div>
                <div class="cashier-account-value">Kasir</div>
            </div>

            <form action="{{ route('logout') }}" method="post" class="d-grid mt-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
