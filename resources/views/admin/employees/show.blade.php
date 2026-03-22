@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('heading', 'Detail Karyawan')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Ringkasan Hutang Karyawan</h4>
                        <p class="mb-0 text-muted">Posisi hutang aktif dan histori pinjaman untuk karyawan ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Total Record Hutang</small>
                                    <strong>{{ $detail['debt']['summary']['total_debt_records'] }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Total Nilai Hutang</small>
                                    <strong>Rp{{ $detail['debt']['summary']['total_debt_amount_formatted'] }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Total Sisa Hutang</small>
                                    <strong>Rp{{ $detail['debt']['summary']['total_remaining_balance_formatted'] }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Status Record</small>
                                    <strong>
                                        {{ $detail['debt']['summary']['active_debt_count'] }} aktif /
                                        {{ $detail['debt']['summary']['paid_debt_count'] }} lunas
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Hutang</h4>
                        <p class="mb-0 text-muted">Daftar pinjaman yang pernah dicatat untuk karyawan ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Tanggal</th>
                                        <th>Total Hutang</th>
                                        <th>Sisa Hutang</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detail['debt']['records'] as $record)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $record['recorded_at'] }}</td>
                                            <td>Rp{{ $record['total_debt_formatted'] }}</td>
                                            <td>Rp{{ $record['remaining_balance_formatted'] }}</td>
                                            <td>{{ $record['status_label'] }}</td>
                                            <td>{{ $record['notes'] ?? '-' }}</td>
                                            <td class="text-center">
                                                <a
                                                    href="{{ route('admin.employee-debts.show', ['debtId' => $record['id']]) }}"
                                                    class="btn btn-sm btn-light-primary"
                                                >
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Belum ada riwayat hutang untuk karyawan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Pembayaran Hutang</h4>
                        <p class="mb-0 text-muted">Semua pembayaran hutang yang pernah dicatat untuk karyawan ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Nominal</th>
                                        <th>Catatan</th>
                                        <th class="text-center">Hutang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detail['debt']['payments'] as $payment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $payment['payment_date'] }}</td>
                                            <td>Rp{{ $payment['amount_formatted'] }}</td>
                                            <td>{{ $payment['notes'] ?? '-' }}</td>
                                            <td class="text-center">
                                                <a
                                                    href="{{ route('admin.employee-debts.show', ['debtId' => $payment['employee_debt_id']]) }}"
                                                    class="btn btn-sm btn-light-secondary"
                                                >
                                                    Buka Hutang
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Belum ada riwayat pembayaran hutang untuk karyawan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Ringkasan Riwayat Gaji</h4>
                        <p class="mb-0 text-muted">Posisi histori pencairan gaji yang sudah dicatat untuk karyawan ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Total Record Payroll</small>
                                    <strong>{{ $detail['payroll']['summary']['total_payroll_records'] }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Total Pencairan</small>
                                    <strong>Rp{{ $detail['payroll']['summary']['total_disbursed_amount_formatted'] }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <small class="text-muted d-block mb-1">Pencairan Terakhir</small>
                                    <strong>{{ $detail['payroll']['summary']['latest_disbursement_date'] ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Gaji</h4>
                        <p class="mb-0 text-muted">Daftar pencairan gaji yang pernah dicatat untuk karyawan ini.</p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Tanggal Cair</th>
                                        <th>Nominal</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detail['payroll']['records'] as $record)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $record['disbursement_date'] }}</td>
                                            <td>Rp{{ $record['amount_formatted'] }}</td>
                                            <td>{{ $record['mode_label'] }}</td>
                                            <td>
                                                @if ($record['is_reversed'])
                                                    <span class="text-danger">Direversal</span>
                                                @else
                                                    <span class="text-success">Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $record['notes'] ?? '-' }}
                                                @if ($record['is_reversed'] && $record['reversal_reason'] !== null)
                                                    <div class="small text-danger mt-1">
                                                        Reversal: {{ $record['reversal_reason'] }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if (! $record['is_reversed'])
                                                    <form action="{{ route('admin.payrolls.reverse.store', ['payrollId' => $record['id']]) }}" method="post" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="Koreksi payout payroll">
                                                        <button type="submit" class="btn btn-sm btn-light-danger">Reverse</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Belum ada riwayat gaji untuk karyawan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Karyawan</h4>
                                <p class="mb-0 text-muted">Profil dasar karyawan untuk pusat detail operasional.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Nama</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['name'] }}</dd>

                            <dt class="col-sm-5">Telepon</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['phone'] ?? '-' }}</dd>

                            <dt class="col-sm-5">Gaji Pokok</dt>
                            <dd class="col-sm-7">Rp{{ $detail['summary']['base_salary_formatted'] }}</dd>

                            <dt class="col-sm-5">Periode Gaji</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['pay_period_label'] }}</dd>

                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['status_label'] }}</dd>
                        </dl>
                    </div>

                    <div class="card-footer">
                        <a
                            href="{{ route('admin.employees.edit', ['employeeId' => $detail['summary']['id']]) }}"
                            class="btn btn-primary"
                        >
                            Edit Karyawan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
