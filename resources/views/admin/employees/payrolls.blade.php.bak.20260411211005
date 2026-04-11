@extends('layouts.app')

@section('title', 'Detail Gaji Karyawan')
@section('heading', 'Detail Gaji Karyawan')

@section('content')
    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $employee['employee_name'] }}</h4>
                <p class="text-muted mb-0">{{ $page['subtitle'] }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a
                    href="{{ route('admin.employees.show', ['employeeId' => $detail['summary']['id']]) }}"
                    class="btn btn-light-secondary"
                >
                    Detail Karyawan
                </a>
                <a
                    href="{{ route('admin.payrolls.create') }}"
                    class="btn btn-primary"
                >
                    Catat Gaji
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Basis Gaji</h5>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ $employee['salary_basis_label'] }}</div>
                        <div class="small text-muted mt-1">Status: {{ $employee['employment_status_label'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Default Gaji</h5>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ $employee['default_salary_amount_label'] }}</div>
                        <div class="small text-muted mt-1">Telepon: {{ $employee['phone'] ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Total Riwayat</h5>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">{{ $payrollSummary['total_payroll_records'] }}</div>
                        <div class="small text-muted mt-1">Payroll aktif karyawan ini</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Total Dicairkan</h5>
                    </div>
                    <div class="card-body">
                        <div class="fw-semibold">Rp{{ $payrollSummary['total_disbursed_amount_formatted'] }}</div>
                        <div class="small text-muted mt-1">
                            Terakhir: {{ $payrollSummary['latest_disbursement_date'] ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-light-warning mb-0">
                    <div class="fw-semibold mb-1">Kebijakan Koreksi Payroll</div>
                    <div class="small">
                        Edit langsung payroll tidak tersedia. Koreksi akan dipusatkan di halaman detail gaji karyawan ini.
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Riwayat Gaji Karyawan</h5>
                        <p class="mb-0 text-muted">Daftar riwayat gaji khusus {{ $employee['employee_name'] }}.</p>
                    </div>
                    <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">Catat Gaji</a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="employee-payroll-history-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Mode Pencairan</th>
                                <th>Catatan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employee-payroll-history-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Sedang memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="employee-payroll-history-summary" class="text-muted">Total: -</small>
                    <div id="employee-payroll-history-pagination"></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.employeePayrollHistoryConfig = {
            endpoint: @json(route('admin.employees.payroll-table', ['employeeId' => $detail['summary']['id']])),
            employeeDetailUrl: @json(route('admin.employees.show', ['employeeId' => $detail['summary']['id']]))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-employee-payroll-history.js') }}"></script>
@endpush
