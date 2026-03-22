@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('heading', 'Data Karyawan')

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
                        <h4 class="card-title mb-1">Data Karyawan</h4>
                        <p class="mb-0 text-muted">Master data karyawan untuk admin.</p>
                    </div>

                    <div>
                        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Tambah Data Karyawan</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 64px;">No</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Gaji Pokok</th>
                                <th>Periode Gaji</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $employee['name'] }}</td>
                                    <td>{{ $employee['phone'] ?? '-' }}</td>
                                    <td>Rp{{ $employee['base_salary_formatted'] }}</td>
                                    <td>{{ $employee['pay_period_label'] }}</td>
                                    <td>{{ $employee['status_label'] }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.employees.edit', ['employeeId' => $employee['id']]) }}" class="btn btn-sm btn-light-primary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
