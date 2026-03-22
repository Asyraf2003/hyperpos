@extends('layouts.app')

@section('title', 'Hutang')
@section('heading', 'Hutang')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Hutang Karyawan</h4>
                        <p class="mb-0 text-muted">Pencatatan hutang manual dan status pelunasannya.</p>
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
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Total Hutang</th>
                                <th>Sisa Hutang</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['recorded_at'] }}</td>
                                    <td>{{ $row['employee_name'] }}</td>
                                    <td>Rp{{ $row['total_debt_formatted'] }}</td>
                                    <td>Rp{{ $row['remaining_balance_formatted'] }}</td>
                                    <td>{{ $row['status_label'] }}</td>
                                    <td>{{ $row['notes'] ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.employee-debts.show', ['debtId' => $row['id']]) }}" class="btn btn-sm btn-light-primary">
                                            Detail
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
