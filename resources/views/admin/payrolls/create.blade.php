@extends('layouts.app')

@section('title', 'Catat Pencairan Gaji')
@section('heading', 'Catat Pencairan Gaji')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Catat Pencairan Gaji</h4>
                                <p class="mb-0 text-muted">Catat pencairan gaji manual untuk satu karyawan.</p>
                            </div>

                            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($errors->has('payroll'))
                            <div class="alert alert-danger">
                                {{ $errors->first('payroll') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.payrolls.store') }}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="payroll-employee-search-input" class="form-label">Cari Karyawan</label>
                                        <input
                                            type="text"
                                            id="payroll-employee-search-input"
                                            class="form-control"
                                            placeholder="Cari nama atau telepon karyawan"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="employee_id" class="form-label">Karyawan</label>
                                        <select
                                            id="employee_id"
                                            name="employee_id"
                                            class="form-select @error('employee_id') is-invalid @enderror"
                                            required
                                        >
                                            <option value="">Pilih karyawan</option>
                                            @foreach ($employees as $employee)
                                                <option
                                                    value="{{ $employee['id'] }}"
                                                    data-name="{{ $employee['name'] }}"
                                                    data-phone="{{ $employee['phone'] ?? '' }}"
                                                    data-status-label="{{ $employee['status_label'] }}"
                                                    data-pay-period-value="{{ $employee['pay_period_value'] }}"
                                                    data-pay-period-label="{{ $employee['pay_period_label'] }}"
                                                    data-base-salary-formatted="{{ $employee['base_salary_formatted'] }}"
                                                    @selected(old('employee_id') === $employee['id'])
                                                >
                                                    {{ $employee['name'] }} - {{ $employee['pay_period_label'] }} - Rp{{ $employee['base_salary_formatted'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Periode gaji karyawan akan menjadi default mode pencairan.</small>
                                        @error('employee_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="card bg-light-subtle border mb-4">
                                        <div class="card-body">
                                            <h6 class="mb-3">Informasi Karyawan Terpilih</h6>
                                            <div class="row mb-2">
                                                <div class="col-sm-4 text-muted">Nama</div>
                                                <div class="col-sm-8" id="payroll-selected-employee-name">-</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4 text-muted">Telepon</div>
                                                <div class="col-sm-8" id="payroll-selected-employee-phone">-</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4 text-muted">Status Karyawan</div>
                                                <div class="col-sm-8" id="payroll-selected-employee-status">-</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-sm-4 text-muted">Periode Gaji Karyawan</div>
                                                <div class="col-sm-8" id="payroll-selected-employee-period">-</div>
                                            </div>
                                            <div class="row mb-0">
                                                <div class="col-sm-4 text-muted">Gaji Pokok Referensi</div>
                                                <div class="col-sm-8" id="payroll-selected-employee-salary">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4" data-money-input-group>
                                        <label for="amount_display" class="form-label">Nominal Pencairan</label>

                                        <input
                                            type="hidden"
                                            id="amount"
                                            name="amount"
                                            value="{{ old('amount') }}"
                                            data-money-raw
                                        >

                                        <input
                                            type="text"
                                            id="amount_display"
                                            value="{{ old('amount') }}"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            placeholder="Contoh: 5.000.000"
                                            inputmode="numeric"
                                            data-money-display
                                            required
                                        >

                                        @error('amount')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="disbursement_date_string" class="form-label">Tanggal Pencairan</label>
                                        <input
                                            type="date"
                                            id="disbursement_date_string"
                                            name="disbursement_date_string"
                                            value="{{ old('disbursement_date_string', now()->format('Y-m-d')) }}"
                                            class="form-control @error('disbursement_date_string') is-invalid @enderror"
                                            required
                                        >
                                        @error('disbursement_date_string')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="mode_value" class="form-label">Mode Pencairan</label>
                                        <select
                                            id="mode_value"
                                            name="mode_value"
                                            class="form-select @error('mode_value') is-invalid @enderror"
                                            required
                                        >
                                            <option value="monthly" @selected(old('mode_value', 'monthly') === 'monthly')>Bulanan</option>
                                            <option value="weekly" @selected(old('mode_value') === 'weekly')>Mingguan</option>
                                            <option value="daily" @selected(old('mode_value') === 'daily')>Harian</option>
                                        </select>
                                        <small class="text-muted">Boleh diubah manual jika pencairan ini adalah pengecualian.</small>
                                        @error('mode_value')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="notes" class="form-label">Catatan Pencairan</label>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows="3"
                                            class="form-control @error('notes') is-invalid @enderror"
                                            placeholder="Opsional"
                                        >{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Pencairan Gaji
                                </button>
                                <a href="{{ route('admin.payrolls.index') }}" class="btn btn-light-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-money-input.js') }}"></script>
    <script>
        window.payrollCreateConfig = {
            hasOldMode: @json(old('mode_value') !== null),
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-payroll-create.js') }}"></script>
    <script>
        window.AdminMoneyInput?.bindBySelector(document);
    </script>
@endpush
