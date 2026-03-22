@extends('layouts.app')

@section('title', 'Catat Hutang Karyawan')
@section('heading', 'Catat Hutang Karyawan')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Catat Hutang Karyawan</h4>
                                <p class="mb-0 text-muted">
                                    Catat hutang baru untuk karyawan.
                                </p>
                            </div>

                            <a href="{{ route('admin.employee-debts.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($errors->has('employee_debt'))
                            <div class="alert alert-danger">
                                {{ $errors->first('employee_debt') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.employee-debts.store') }}" method="post">
                            @csrf

                            <div class="row">
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
                                                <option value="{{ $employee['id'] }}" @selected(old('employee_id') === $employee['id'])>
                                                    {{ $employee['name'] }} - {{ $employee['pay_period_label'] }} - Rp{{ $employee['base_salary_formatted'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4" data-money-input-group>
                                        <label for="debt_amount_display" class="form-label">Nominal Hutang</label>

                                        <input
                                            type="hidden"
                                            id="debt_amount"
                                            name="debt_amount"
                                            value="{{ old('debt_amount') }}"
                                            data-money-raw
                                        >

                                        <input
                                            type="text"
                                            id="debt_amount_display"
                                            value="{{ old('debt_amount') }}"
                                            class="form-control @error('debt_amount') is-invalid @enderror"
                                            placeholder="Contoh: 1.000.000"
                                            inputmode="numeric"
                                            data-money-display
                                            required
                                        >

                                        @error('debt_amount')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
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
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Data Hutang
                                </button>
                                <a href="{{ route('admin.employee-debts.index') }}" class="btn btn-light-secondary">
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
