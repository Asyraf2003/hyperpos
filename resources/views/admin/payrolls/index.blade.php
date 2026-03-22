@extends('layouts.app')

@section('title', 'Gaji')
@section('heading', 'Gaji')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Pencairan Gaji</h4>
                        <p class="mb-0 text-muted">Interactive table riwayat pencairan gaji manual.</p>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="payroll-search-form" class="d-flex flex-column gap-1">
                            <input type="text" id="payroll-search-input" class="form-control" placeholder="Cari nama, catatan, mode, atau tanggal" autocomplete="off">
                        </form>
                        <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">Catat Pencairan Gaji</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="payroll-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="disbursement_date">Tanggal <span class="ms-1 text-muted" data-sort-indicator="disbursement_date">↕</span></button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="employee_name">Nama Karyawan <span class="ms-1 text-muted" data-sort-indicator="employee_name">↕</span></button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="amount">Nominal <span class="ms-1 text-muted" data-sort-indicator="amount">↕</span></button></th>
                                <th><button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="mode">Mode Pencairan <span class="ms-1 text-muted" data-sort-indicator="mode">↕</span></button></th>
                                <th>Catatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="payroll-table-body"><tr><td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td></tr></tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="payroll-table-summary" class="text-muted">Total: -</small>
                    <div id="payroll-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.payrollTableConfig = {
            endpoint: @json(route('admin.payrolls.table')),
            detailBaseUrl: @json(route('admin.employees.show', ['employeeId' => '__ID__']))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-payrolls-table.js') }}"></script>
@endpush
