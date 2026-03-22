@extends('layouts.app')

@section('title', 'Catat Pencairan Gaji')
@section('heading', 'Catat Pencairan Gaji')

@section('content')
    <section class="section">
        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="card h-100">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title mb-1">Pilih Karyawan</h4>
                            <p class="mb-0 text-muted">Cari lalu tambahkan karyawan ke batch pencairan.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="payroll-batch-employee-search-input" class="form-label">Cari Karyawan</label>
                            <input
                                type="text"
                                id="payroll-batch-employee-search-input"
                                class="form-control"
                                placeholder="Cari nama, telepon, periode, atau status"
                                autocomplete="off"
                            >
                        </div>

                        <div id="payroll-batch-employee-list" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Detail Batch Pencairan</h4>
                                <p class="mb-0 text-muted">Panel detail di kanan, daftar karyawan dipilih dari panel kiri.</p>
                            </div>

                            <a href="{{ route('admin.payrolls.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($errors->has('payroll_batch'))
                            <div class="alert alert-danger">
                                {{ $errors->first('payroll_batch') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.payrolls.batch.store') }}" method="post">
                            @csrf

                            <div class="row">
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
                                        <label for="mode_value" class="form-label">Mode Batch Default</label>
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
                                        <small class="text-muted">
                                            Periode gaji karyawan tetap dipakai sebagai referensi. Override per baris tersedia bila perlu.
                                        </small>
                                        @error('mode_value')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-4">
                                        <label for="notes" class="form-label">Catatan Batch</label>
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

                            <div class="card bg-light-subtle border mb-4">
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                        <div>
                                            <h6 class="mb-1">Karyawan Dipilih</h6>
                                            <p class="mb-0 text-muted" id="payroll-batch-selected-summary">Belum ada karyawan dipilih.</p>
                                        </div>

                                        <button type="button" id="payroll-batch-clear-button" class="btn btn-light-secondary">
                                            Kosongkan Pilihan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="payroll-batch-selected-rows" class="d-flex flex-column gap-3"></div>

                            @error('rows')
                                <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                            @enderror

                            <div class="d-flex justify-content-start gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Simpan Batch Pencairan Gaji
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
            employees: @json($employees),
            oldRows: @json(old('rows', [])),
        };
    </script>
    <script src="{{ asset('assets/static/js/pages/admin-payroll-create.js') }}"></script>
@endpush
