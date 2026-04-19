@extends('layouts.app')

@section('title', 'Kategori Pengeluaran')
@section('heading', 'Kategori Pengeluaran')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Kategori biaya operasional yang dipakai saat pencatatan operasional</h4>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="expense-category-search-form" class="d-flex flex-column gap-1">
                            <input
                                type="text"
                                id="expense-category-search-input"
                                class="form-control"
                                placeholder="Cari kode, nama, atau deskripsi kategori"
                                autocomplete="off"
                            >
                        </form>

                        <button type="button" id="open-expense-category-filter" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.expenses.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('admin.expenses.categories.partials.filter_drawer')

                <div class="table-responsive">
                    <table class="table table-lg" id="expense-category-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="code">
                                        Kode
                                        <span class="ms-1 text-muted" data-sort-indicator="code">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="name">
                                        Nama
                                        <span class="ms-1 text-muted" data-sort-indicator="name">↕</span>
                                    </button>
                                </th>
                                <th>Deskripsi</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="is_active">
                                        Status
                                        <span class="ms-1 text-muted" data-sort-indicator="is_active">↕</span>
                                    </button>
                                </th>
                                <th style="width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="expense-category-table-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Sedang memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="expense-category-table-summary" class="text-muted">Total: -</small>
                    <div id="expense-category-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.expenseCategoryTableConfig = {
            endpoint: @json(route('admin.expenses.categories.table')),
            editUrlTemplate: @json(route('admin.expenses.categories.edit', ['categoryId' => '__CATEGORY_ID__'])),
            activateUrlTemplate: @json(route('admin.expenses.categories.activate', ['categoryId' => '__CATEGORY_ID__'])),
            deactivateUrlTemplate: @json(route('admin.expenses.categories.deactivate', ['categoryId' => '__CATEGORY_ID__'])),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-expense-categories-table.js') }}"></script>
@endpush
