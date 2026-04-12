@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('heading', 'Detail Karyawan')

@section('content')
    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $page['heading'] }}</h4>
                <p class="text-muted mb-0">{{ $page['subtitle'] }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a
                    href="{{ route('admin.employees.edit', ['employeeId' => $detail['summary']['id']]) }}"
                    class="btn btn-primary"
                >
                    Edit Karyawan
                </a>
                <a
                    href="{{ route('admin.payrolls.create', ['employee_id' => $detail['summary']['id']]) }}"
                    class="btn btn-light-primary"
                >
                    Catat Gaji
                </a>
                <a
                    @if (($detail['summary']['latest_unpaid_debt_id'] ?? null) !== null)
                        href="{{ route('admin.employee-debts.show', ['debtId' => $detail['summary']['latest_unpaid_debt_id']]) }}"
                    @else
                        href="{{ route('admin.employee-debts.index', ['employee_id' => $detail['summary']['id']]) }}"
                    @endif
                    class="btn btn-light-secondary"
                >
                    Lihat Hutang Karyawan
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Karyawan</h5>
                        <p class="text-muted mb-0 mt-1">Identitas Saat Ini</p>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Karyawan</small>
                            <div class="fw-semibold">{{ $page['current_identity']['employee_name'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Telepon</small>
                            <div class="fw-semibold">{{ $page['current_identity']['phone'] ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Basis Gaji</small>
                            <div class="fw-semibold">{{ $page['current_identity']['salary_basis_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Default Gaji</small>
                            <div class="fw-semibold">{{ $page['current_identity']['default_salary_amount_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            <div class="fw-semibold">{{ $page['current_identity']['employment_status_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Mulai Kerja</small>
                            <div class="fw-semibold">{{ $page['current_identity']['started_at'] ?? '-' }}</div>
                        </div>

                        <div>
                            <small class="text-muted d-block">Berakhir</small>
                            <div class="fw-semibold">{{ $page['current_identity']['ended_at'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Hutang</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Record</small>
                            <div class="fw-semibold">{{ $debtSummary['total_debt_records'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Hutang Aktif</small>
                            <div class="fw-semibold">{{ $debtSummary['active_debt_count'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Hutang Lunas</small>
                            <div class="fw-semibold">{{ $debtSummary['paid_debt_count'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Total Nominal</small>
                            <div class="fw-semibold">Rp{{ $debtSummary['total_debt_amount_formatted'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Sisa Hutang</small>
                            <div class="fw-semibold">Rp{{ $debtSummary['total_remaining_balance_formatted'] }}</div>
                        </div>

                        <a
                            @if (($detail['summary']['latest_unpaid_debt_id'] ?? null) !== null)
                                href="{{ route('admin.employee-debts.show', ['debtId' => $detail['summary']['latest_unpaid_debt_id']]) }}"
                            @else
                                href="{{ route('admin.employee-debts.index', ['employee_id' => $detail['summary']['id']]) }}"
                            @endif
                            class="btn btn-light-secondary btn-sm"
                        >
                            Lihat Hutang Karyawan
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Gaji</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Payroll Aktif</small>
                            <div class="fw-semibold">{{ $payrollSummary['total_payroll_records'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Total Nominal Cair</small>
                            <div class="fw-semibold">Rp{{ $payrollSummary['total_disbursed_amount_formatted'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Pencairan Terakhir</small>
                            <div class="fw-semibold">{{ $payrollSummary['latest_disbursement_date'] ?? '-' }}</div>
                        </div>

                        <div class="alert alert-light-info mb-3">
                            Edit langsung tidak tersedia. Koreksi payroll dilakukan lewat reversal lalu catat ulang payroll yang benar.
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a
                                href="{{ route('admin.employees.payrolls.show', ['employeeId' => $detail['summary']['id']]) }}"
                                class="btn btn-light-secondary btn-sm"
                            >
                                Buka
                            </a>
                            <a
                                href="{{ route('admin.payrolls.create', ['employee_id' => $detail['summary']['id']]) }}"
                                class="btn btn-primary btn-sm"
                            >
                                Catat Ulang Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($page['initial_identity'] !== null || $page['initial_identity_meta']['note'] !== null)
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">{{ $page['initial_identity_meta']['title'] }}</h5>
                        <span class="badge bg-light-{{ $page['initial_identity_meta']['badge_tone'] }} text-{{ $page['initial_identity_meta']['badge_tone'] }}">
                            {{ $page['initial_identity_meta']['badge_label'] }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @if ($page['initial_identity_meta']['note'] !== null)
                        <div class="alert alert-light-{{ $page['initial_identity_meta']['badge_tone'] }} mb-4">
                            {{ $page['initial_identity_meta']['note'] }}
                        </div>
                    @endif

                    @if ($page['initial_identity_meta']['show_values'] && $page['initial_identity'] !== null)
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Nama Karyawan</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['employee_name'] }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Telepon</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['phone'] ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Basis Gaji</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['salary_basis_label'] }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Default Gaji</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['default_salary_amount_label'] }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Status</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['employment_status_label'] }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Mulai Kerja</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['started_at'] ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Berakhir</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['ended_at'] ?? '-' }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Tercatat Pada</small>
                                <div class="fw-semibold">{{ $page['initial_identity']['changed_at'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h5 class="card-title mb-1">Riwayat Gaji Karyawan</h5>
                        <p class="mb-0 text-muted">
                            Daftar riwayat gaji khusus {{ $page['current_identity']['employee_name'] }}.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.employees.payrolls.show', ['employeeId' => $detail['summary']['id']]) }}"
                            class="btn btn-light-secondary"
                        >
                            Buka
                        </a>
                        <a
                            href="{{ route('admin.payrolls.create', ['employee_id' => $detail['summary']['id']]) }}"
                            class="btn btn-primary"
                        >
                            Catat Ulang Payroll
                        </a>
                    </div>
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
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employee-payroll-history-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td>
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

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat Versi Karyawan</h5>
            </div>

            <div class="card-body">
                @if (count($page['timeline']) === 0)
                    <p class="text-muted mb-0">Belum ada riwayat versi karyawan.</p>
                @else
                    <div class="timeline">
                        @foreach ($page['timeline'] as $entry)
                            <div class="timeline-item pb-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $entry['revision_label'] }} · {{ $entry['event_name'] }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $entry['changed_at'] }}
                                            @if ($entry['actor_label'])
                                                · {{ $entry['actor_label'] }}
                                            @endif
                                        </small>
                                    </div>

                                    @if ($entry['reason_label'])
                                        <span class="badge bg-light-info text-info align-self-start">
                                            {{ $entry['reason_label'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="border rounded p-3 bg-light-subtle">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Nama Karyawan</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['employee_name'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Telepon</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['phone'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Basis Gaji</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['salary_basis_label'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Default Gaji</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['default_salary_amount_label'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Status</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['employment_status_label'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Mulai Kerja</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['started_at'] }}</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <small class="text-muted d-block">Berakhir</small>
                                            <div class="fw-semibold">{{ $entry['snapshot']['ended_at'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div
            class="modal fade"
            id="employee-payroll-reversal-modal"
            tabindex="-1"
            aria-labelledby="employee-payroll-reversal-modal-title"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <form id="employee-payroll-reversal-form" method="POST" action="#">
                        @csrf

                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                            <div class="w-100">
                                <h3 class="modal-title fw-bold mb-1" id="employee-payroll-reversal-modal-title">Reversal Gaji</h3>
                                <p class="mb-0 text-muted fs-6" id="employee-payroll-reversal-modal-subtitle">
                                    Isi alasan pembatalan pencairan gaji.
                                </p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>

                        <div class="modal-body px-4 pb-3 pt-3">
                            <div class="mb-3">
                                <label for="employee-payroll-reversal-reason" class="form-label">Alasan</label>
                                <textarea
                                    name="reason"
                                    id="employee-payroll-reversal-reason"
                                    class="form-control"
                                    rows="4"
                                    required
                                    placeholder="Tulis alasan pembatalan pencairan gaji"
                                >{{ old('reason') }}</textarea>
                                <div class="form-text">Alasan wajib diisi untuk kebutuhan audit koreksi payroll.</div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger">Simpan Reversal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.employeePayrollHistoryConfig = {
            endpoint: @json(route('admin.employees.payroll-table', ['employeeId' => $detail['summary']['id']])),
            reverseStoreBaseUrl: @json(route('admin.payrolls.reverse.store', ['payrollId' => '__ID__'])),
            employeeDetailUrl: @json(route('admin.employees.show', ['employeeId' => $detail['summary']['id']]))
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-employee-payroll-history.js') }}"></script>
@endpush
