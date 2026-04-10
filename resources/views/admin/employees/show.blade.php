@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('heading', 'Detail Karyawan')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Karyawan</h4>
                                <p class="mb-0 text-muted">Profil dasar karyawan untuk operasional admin.</p>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <a
                                    href="{{ route('admin.employees.edit', ['employeeId' => $detail['summary']['id']]) }}"
                                    class="btn btn-primary"
                                >
                                    Edit Karyawan
                                </a>
                                <a
                                    href="{{ route('admin.employee-debts.index') }}"
                                    class="btn btn-light-secondary"
                                >
                                    Lihat Hutang Karyawan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nama Karyawan</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['employee_name'] }}</dd>

                            <dt class="col-sm-4">Telepon</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['phone'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Basis Gaji</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['salary_basis_label'] }}</dd>

                            <dt class="col-sm-4">Default Gaji</dt>
                            <dd class="col-sm-8">
                                @if ($detail['summary']['default_salary_amount_formatted'] !== null)
                                    Rp{{ $detail['summary']['default_salary_amount_formatted'] }}
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['employment_status_label'] }}</dd>

                            <dt class="col-sm-4">Mulai Kerja</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['started_at'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Berakhir</dt>
                            <dd class="col-sm-8">{{ $detail['summary']['ended_at'] ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
