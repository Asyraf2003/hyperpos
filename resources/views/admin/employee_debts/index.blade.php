@extends('layouts.app')

@section('title', 'Hutang Karyawan')
@section('heading', 'Hutang Karyawan')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Ringkasan Hutang Karyawan</h4>
                        <p class="mb-0 text-muted">Tabel ringkasan hutang interaktif yang mengarah ke detail karyawan.</p>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form id="employee-debt-search-form" class="d-flex flex-column gap-1">
                            <input
                                type="text"
                                id="employee-debt-search-input"
                                class="form-control"
                                placeholder="Cari nama karyawan"
                                autocomplete="off"
                            >
                        </form>
                        <a href="{{ route('admin.employee-debts.create') }}" class="btn btn-primary">Catat Hutang Karyawan</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg" id="employee-debt-table">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="employee_name">
                                        Karyawan
                                        <span class="ms-1 text-muted" data-sort-indicator="employee_name">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="latest_recorded_at">
                                        Terakhir Dicatat
                                        <span class="ms-1 text-muted" data-sort-indicator="latest_recorded_at">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_debt_records">
                                        Total Record
                                        <span class="ms-1 text-muted" data-sort-indicator="total_debt_records">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_debt_amount">
                                        Total Hutang
                                        <span class="ms-1 text-muted" data-sort-indicator="total_debt_amount">↕</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-sort-by="total_remaining_balance">
                                        Total Sisa
                                        <span class="ms-1 text-muted" data-sort-indicator="total_remaining_balance">↕</span>
                                    </button>
                                </th>
                                <th>Status Hutang</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employee-debt-table-body">
                            <tr><td colspan="8" class="text-center text-muted py-4">Sedang memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small id="employee-debt-table-summary" class="text-muted">Total: -</small>
                    <div id="employee-debt-table-pagination"></div>
                </div>
            </div>
        </div>

        <div
            class="modal fade"
            id="employee-debt-action-modal"
            tabindex="-1"
            aria-labelledby="employee-debt-action-modal-title"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div class="w-100">
                            <h3 class="modal-title fw-bold mb-1" id="employee-debt-action-modal-title">Aksi Hutang Karyawan</h3>
                            <p class="mb-0 text-muted fs-6" id="employee-debt-action-modal-subtitle">
                                Pilih tindakan untuk ringkasan hutang karyawan.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body px-4 pb-4 pt-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <a
                                    href="#"
                                    id="employee-debt-action-detail-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4 h-100"
                                >
                                    <div class="fw-bold fs-5 mb-1">Detail Karyawan</div>
                                    <div class="small opacity-75">Lihat profil karyawan beserta riwayat hutang dan gaji.</div>
                                </a>
                            </div>

                            <div class="col-12 col-md-3">
                                <a
                                    href="#"
                                    id="employee-debt-action-create-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4 h-100"
                                >
                                    <div class="fw-bold fs-5 mb-1">Catat Hutang Baru</div>
                                    <div class="small opacity-75">Masuk ke form pencatatan hutang karyawan.</div>
                                </a>
                            </div>

                            <div class="col-12 col-md-3">
                                <a
                                    href="#"
                                    id="employee-debt-action-pay-link"
                                    class="btn btn-outline-success w-100 text-start py-3 px-4 h-100"
                                >
                                    <div class="fw-bold fs-5 mb-1">Bayar Hutang</div>
                                    <div class="small opacity-75">Buka hutang aktif terbaru untuk langsung bayar.</div>
                                </a>
                            </div>

                            <div class="col-12 col-md-3">
                                <a
                                    href="#"
                                    id="employee-debt-action-payroll-link"
                                    class="btn btn-outline-primary w-100 text-start py-3 px-4 h-100"
                                >
                                    <div class="fw-bold fs-5 mb-1">Detail Gaji</div>
                                    <div class="small opacity-75">Masuk ke daftar gaji admin untuk tindak lanjut pencairan.</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.employeeDebtTableConfig = {
            endpoint: @json(route('admin.employee-debts.table')),
            detailBaseUrl: @json(route('admin.employees.show', ['employeeId' => '__ID__'])),
            createDebtUrl: @json(route('admin.employee-debts.create')),
            debtShowBaseUrl: @json(route('admin.employee-debts.show', ['debtId' => '__ID__'])),
            payrollIndexUrl: @json(route('admin.payrolls.index'))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-employee-debts-table.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/admin-employee-debt-table-actions.js') }}"></script>
@endpush
