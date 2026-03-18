@extends('layouts.app')

@section('title', 'Product')
@section('heading', 'Product')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Master barang bengkel</h4>
                        <p class="mb-0 text-muted">Interactive table product untuk admin.</p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="product-search-form" class="d-flex gap-2">
                            <input type="text" id="product-search-input" class="form-control" placeholder="Cari kode, nama, atau merek">
                            <button type="submit" class="btn btn-outline-primary">Cari</button>
                        </form>

                        <button type="button" id="open-product-filter" class="btn btn-light-secondary">Filter</button>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Tambah Product</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="product-table">
                        <thead>
                            <tr>
                                <th style="width: 64px;">#</th>
                                <th>Kode Barang</th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="nama_barang">Nama Barang</button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="merek">Merek</button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="ukuran">Ukuran</button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="harga_jual">Harga Jual</button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="stok_saat_ini">Stok Saat Ini</button></th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            <tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="product-table-summary" class="text-muted">Total: -</small>
                    <div id="product-table-pagination"></div>
                </div>
            </div>
        </div>

        @include('admin.products.partials.filter_drawer')
    </section>
@endsection

@push('scripts')
    <script>
        window.productTableConfig = {
            endpoint: @json(route('admin.products.table')),
            editBaseUrl: @json(route('admin.products.edit', ['productId' => '__ID__']))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-products-table.js') }}"></script>
@endpush
