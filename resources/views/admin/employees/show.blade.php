@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('heading', 'Detail Karyawan')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Karyawan</h4>
                                <p class="mb-0 text-muted">Profil dasar karyawan untuk pusat detail operasional.</p>
                            </div>

                            <a href="{{ route('admin.employees.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
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

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Normalisasi Employee Finance</h4>
                        <p class="mb-0 text-muted">Shell awal untuk pusat detail Debt dan Payroll per karyawan.</p>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-light-secondary mb-4">
                            Halaman ini disiapkan sebagai pusat detail karyawan. Slice berikutnya akan memindahkan ringkasan dan histori debt/payroll ke sini.
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <h5 class="mb-1">Section Hutang Karyawan</h5>
                            <p class="mb-0 text-muted">
                                Akan diisi dengan ringkasan hutang aktif, histori pinjam, histori pembayaran, dan action lanjutan yang audit-friendly.
                            </p>
                        </div>

                        <div class="border rounded p-3">
                            <h5 class="mb-1">Section Riwayat Gaji</h5>
                            <p class="mb-0 text-muted">
                                Akan diisi dengan histori pencairan gaji per karyawan dan ringkasan payroll yang relevan untuk admin operasional.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
