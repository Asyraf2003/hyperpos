@extends('layouts.app')

@section('title', 'Gaji')
@section('heading', 'Gaji')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Pencairan Gaji</h4>
                        <p class="mb-0 text-muted">Pencairan gaji manual yang sudah dicatat oleh admin.</p>
                    </div>

                    <div>
                        <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">Catat Pencairan Gaji</a>
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
                                <th>Nama Karyawan</th>
                                <th>Nominal</th>
                                <th>Mode</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['disbursement_date'] }}</td>
                                    <td>{{ $row['employee_name'] }}</td>
                                    <td>Rp{{ $row['amount_formatted'] }}</td>
                                    <td>{{ $row['mode_label'] }}</td>
                                    <td>{{ $row['notes'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada pencairan gaji.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
