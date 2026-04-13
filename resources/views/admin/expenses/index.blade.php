@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Pengeluaran Operasional')
@section('heading', 'Pengeluaran Operasional')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="card-title mb-1">Daftar Pengeluaran Operasional</h4>
                            <p class="mb-0 text-muted">Daftar pengeluaran operasional bengkel.</p>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-2">
                            <form id="expense-search-form" class="d-flex flex-column gap-1">
                                <input
                                    type="text"
                                    id="expense-search-input"
                                    class="form-control"
                                    placeholder="Cari kategori, kode kategori, deskripsi, metode bayar"
                                    autocomplete="off"
                                >
                            </form>

                            <button type="button" id="open-expense-filter" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.expenses.categories.index') }}" class="btn btn-primary">Kelola Kategori</a>
                            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">Catat Pengeluaran</a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body">
                @include('admin.expenses.partials.filter_drawer')

                <div class="table-responsive">
                    <table class="table table-lg" id="expense-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="expense_date">
                                        Tanggal
                                        <span class="ms-1 text-muted" data-sort-indicator="expense_date">↕</span>
                                    </button>
                                </th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-decoration-none w-100 text-end"
                                        data-sort-by="amount_rupiah"
                                    >
                                        Nominal
                                        <span class="ms-1 text-muted" data-sort-indicator="amount_rupiah">↕</span>
                                    </button>
                                </th>
                                <th>Metode Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="expense-table-summary" class="text-muted">Total: -</small>
                    <div id="expense-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.expenseTableConfig = {
            endpoint: @json(route('admin.expenses.table')),
            deleteUrlTemplate: @json(route('admin.expenses.delete', ['expenseId' => '__EXPENSE_ID__'])),
            csrfToken: @json(csrf_token())
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-expenses-table.js') }}"></script>
@endpush
