@extends('layouts.app')

@section('title', 'Edit Data Karyawan')
@section('heading', 'Edit Data Karyawan')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Edit Data Karyawan</h4>
                                <p class="mb-0 text-muted">
                                    Perubahan master karyawan wajib menyertakan catatan koreksi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($errors->has('employee'))
                            <div class="alert alert-danger">
                                {{ $errors->first('employee') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.employees.update', ['employeeId' => $employee->getId()]) }}" method="post">
                            @csrf
                            @method('put')

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="name" class="form-label">Nama Karyawan</label>
                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            value="{{ old('name', $employee->getName()) }}"
                                            class="form-control @error('name') is-invalid @enderror"
                                            required
                                        >
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="phone" class="form-label">Nomor Telepon</label>
                                        <input
                                            type="text"
                                            id="phone"
                                            name="phone"
                                            value="{{ old('phone', $employee->getPhone()) }}"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            placeholder="Opsional"
                                        >
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4" data-money-input-group>
                                        <label for="base_salary_amount_display" class="form-label">Gaji Pokok</label>

                                        <input
                                            type="hidden"
                                            id="base_salary_amount"
                                            name="base_salary_amount"
                                            value="{{ old('base_salary_amount', $employee->getBaseSalary()->amount()) }}"
                                            data-money-raw
                                        >

                                        <input
                                            type="text"
                                            id="base_salary_amount_display"
                                            value="{{ old('base_salary_amount', $employee->getBaseSalary()->amount()) }}"
                                            class="form-control @error('base_salary_amount') is-invalid @enderror"
                                            inputmode="numeric"
                                            data-money-display
                                            required
                                        >

                                        @error('base_salary_amount')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="pay_period_value" class="form-label">Periode Gaji</label>
                                        <select
                                            id="pay_period_value"
                                            name="pay_period_value"
                                            class="form-select @error('pay_period_value') is-invalid @enderror"
                                            required
                                        >
                                            <option value="monthly" @selected(old('pay_period_value', $employee->getPayPeriod()->value) === 'monthly')>Bulanan</option>
                                            <option value="weekly" @selected(old('pay_period_value', $employee->getPayPeriod()->value) === 'weekly')>Mingguan</option>
                                            <option value="daily" @selected(old('pay_period_value', $employee->getPayPeriod()->value) === 'daily')>Harian</option>
                                        </select>
                                        @error('pay_period_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="status_value" class="form-label">Status</label>
                                        <select
                                            id="status_value"
                                            name="status_value"
                                            class="form-select @error('status_value') is-invalid @enderror"
                                            required
                                        >
                                            <option value="active" @selected(old('status_value', $employee->getStatus()->value) === 'active')>Aktif</option>
                                            <option value="inactive" @selected(old('status_value', $employee->getStatus()->value) === 'inactive')>Nonaktif</option>
                                        </select>
                                        @error('status_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="change_reason" class="form-label">Catatan Perubahan</label>
                                        <textarea
                                            id="change_reason"
                                            name="change_reason"
                                            rows="3"
                                            class="form-control @error('change_reason') is-invalid @enderror"
                                            placeholder="Wajib diisi. Contoh: cuti 2 minggu, koreksi nomor telepon, penyesuaian data."
                                            required
                                        >{{ old('change_reason') }}</textarea>
                                        @error('change_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="{{ route('admin.employees.index') }}" class="btn btn-light-secondary">Batal</a>
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
        window.AdminMoneyInput?.bindBySelector(document);
    </script>
@endpush
