@extends('layouts.app')

@section('title', 'Hutang Karyawan')
@section('heading', 'Hutang Karyawan')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Ringkasan Hutang Karyawan</h4>
                        <p class="mb-0 text-muted">Entry list debt yang mengarah ke pusat detail karyawan.</p>
                    </div>

                    <div>
                        <a href="{{ route('admin.employee-debts.create') }}" class="btn btn-primary">Catat Hutang Karyawan</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>Karyawan</th>
                                <th>Terakhir Dicatat</th>
                                <th>Total Record</th>
                                <th>Total Hutang</th>
                                <th>Total Sisa</th>
                                <th>Status Record</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['employee_name'] }}</td>
                                    <td>{{ $row['latest_recorded_at'] }}</td>
                                    <td>{{ $row['total_debt_records'] }}</td>
                                    <td>Rp{{ $row['total_debt_amount_formatted'] }}</td>
                                    <td>Rp{{ $row['total_remaining_balance_formatted'] }}</td>
                                    <td>{{ $row['active_debt_count'] }} aktif / {{ $row['paid_debt_count'] }} lunas</td>
                                    <td class="text-center">
                                        <a
                                            href="{{ route('admin.employees.show', ['employeeId' => $row['employee_id']]) }}"
                                            class="btn btn-sm btn-light-primary"
                                        >
                                            Buka Detail Karyawan
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data hutang karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
