@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Catat Pencairan Gaji')
@section('heading', 'Catat Pencairan Gaji')

@section('content')
    <section class="section">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title mb-1">Form Pencairan Gaji</h4>
                            <p class="mb-0 text-muted">Pencairan gaji manual dicatat satu per satu. Koreksi dilakukan lewat reversal lalu catat ulang.</p>
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

                            <div class="form-group mb-4">
                                <label for="employee_id" class="form-label">Pilih Karyawan</label>
                                <select
                                    id="employee_id"
                                    name="employee_id"
                                    class="form-select @error('employee_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Pilih karyawan --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee['id'] }}" @selected(old('employee_id') === $employee['id'])>
                                            {{ $employee['employee_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
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
                                            placeholder="Contoh: 1.500.000"
                                            inputmode="numeric"
                                            data-money-display
                                            required
                                        >
                                        @error('amount')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="disbursement_date_string" class="form-label">Tanggal Pencairan</label>
                                        <input
                                            type="date"
                                            data-ui-date="single"
                                            id="disbursement_date_string"
                                            name="disbursement_date_string"
                                            value="{{ old('disbursement_date_string', now()->format('Y-m-d')) }}"
                                            class="form-control @error('disbursement_date_string') is-invalid @enderror"
                                            required
                                        >
                                        @error('disbursement_date_string')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

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
                                @error('mode_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Opsional"
                                >{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
        window.AdminMoneyInput?.bindBySelector(document);
    </script>
@endpush
