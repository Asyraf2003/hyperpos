@extends('layouts.app')

@section('title', 'Karyawan')
@section('heading', 'Karyawan')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Master Karyawan</h4>
                        <p class="mb-0 text-muted">Tabel karyawan interaktif untuk admin.</p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="employee-search-form" class="d-flex flex-column gap-1">
                            <input
                                type="text"
                                id="employee-search-input"
                                class="form-control"
                                placeholder="Cari nama, telepon, basis gaji, atau status"
                                autocomplete="off"
                            >
                        </form>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Tambah Karyawan</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="employee-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="employee_name">
                                        Nama Karyawan
                                        <span class="ms-1 text-muted" data-sort-indicator="employee_name">↕</span>
                                    </button>
                                </th>
                                <th>Telepon</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="default_salary_amount">
                                        Default Gaji
                                        <span class="ms-1 text-muted" data-sort-indicator="default_salary_amount">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="salary_basis_type">
                                        Basis Gaji
                                        <span class="ms-1 text-muted" data-sort-indicator="salary_basis_type">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="employment_status">
                                        Status
                                        <span class="ms-1 text-muted" data-sort-indicator="employment_status">↕</span>
                                    </button>
                                </th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employee-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="employee-table-summary" class="text-muted">Total: -</small>
                    <div id="employee-table-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.employeeTableConfig = {
            endpoint: @json(route('admin.employees.table')),
            detailBaseUrl: @json(route('admin.employees.show', ['employeeId' => '__ID__'])),
            editBaseUrl: @json(route('admin.employees.edit', ['employeeId' => '__ID__']))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-employees-table.js') }}"></script>
@endpush
