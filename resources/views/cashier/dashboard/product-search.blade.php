@extends('layouts.app')

@section('title', 'Cari Barang')
@section('heading', 'Cari Barang')
@section('back_url', route('cashier.dashboard'))

@section('content')
<section class="section">
    <style>
        .cashier-product-search {
            max-width: 720px;
            margin: 0 auto;
        }

        .cashier-product-card,
        .cashier-result-item,
        .cashier-empty-state {
            border: 1px solid var(--cashier-border);
            border-radius: 1rem;
            background: var(--cashier-surface);
            box-shadow: var(--cashier-shadow);
        }

        .cashier-product-card {
            padding: 1rem;
        }

        .cashier-product-search .form-control {
            min-height: 3rem;
            border-radius: .85rem;
        }

        .cashier-results-wrap {
            display: grid;
            gap: .75rem;
            margin-top: 1rem;
        }

        .cashier-result-item,
        .cashier-empty-state {
            padding: 1rem;
            box-shadow: none;
        }

        .cashier-result-title {
            margin: 0;
            color: var(--cashier-text);
            font-size: .98rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .cashier-result-meta,
        .cashier-section-desc,
        .cashier-search-status {
            color: var(--cashier-muted);
            font-size: .9rem;
            line-height: 1.55;
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
            background: rgba(var(--bs-success-rgb), .12);
            color: var(--bs-success);
            border-color: rgba(var(--bs-success-rgb), .22);
        }

        .cashier-result-stock.is-low {
            background: rgba(var(--bs-warning-rgb), .14);
            color: var(--bs-warning-text-emphasis, var(--bs-warning));
            border-color: rgba(var(--bs-warning-rgb), .25);
        }

        .cashier-result-stock.is-empty {
            background: rgba(var(--bs-danger-rgb), .12);
            color: var(--bs-danger);
            border-color: rgba(var(--bs-danger-rgb), .22);
        }

        .cashier-search-spinner {
            width: 1rem;
            height: 1rem;
            margin-right: .35rem;
        }
    </style>

    <div
        class="cashier-product-search"
        data-cashier-dashboard
        data-product-lookup-endpoint="{{ $productLookupEndpoint }}"
    >
        <div class="ui-page-intro">
            <div class="small text-muted text-uppercase fw-semibold">Kasir</div>
            <h4 class="ui-page-intro-title">Cari Barang</h4>
            <p class="ui-page-intro-subtitle">Cek harga jual dan stok sebelum membuat rincian nota.</p>
        </div>

        <div class="cashier-product-card">
            <label for="cashier-product-search" class="form-label fw-semibold">Nama Barang</label>
            <input
                type="search"
                id="cashier-product-search"
                class="form-control"
                placeholder="Contoh: oli, kampas, federal, 10W"
                autocomplete="off"
                data-product-search-input
            >

            <div class="cashier-search-status mt-3" data-search-status>
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
</section>

<script src="{{ asset('assets/static/js/pages/cashier-dashboard.js') }}?v={{ config('app.asset_version') }}"></script>
@endsection
