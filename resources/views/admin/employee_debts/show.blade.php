@extends('layouts.app')

@section('title', 'Detail Hutang Karyawan')
@section('heading', 'Detail Hutang Karyawan')

@section('content')
    <section class="section">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Hutang</h4>
                                <p class="mb-0 text-muted">Status dan saldo hutang karyawan.</p>
                            </div>

                            <a href="{{ route('admin.employee-debts.index') }}" class="btn btn-light-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Karyawan</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['employee_name'] }}</dd>

                            <dt class="col-sm-5">Tanggal Catat</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['recorded_at'] }}</dd>

                            <dt class="col-sm-5">Total Hutang</dt>
                            <dd class="col-sm-7">Rp{{ $detail['summary']['total_debt_formatted'] }}</dd>

                            <dt class="col-sm-5">Sudah Dibayar</dt>
                            <dd class="col-sm-7">Rp{{ $detail['summary']['total_paid_amount_formatted'] }}</dd>

                            <dt class="col-sm-5">Sisa Hutang</dt>
                            <dd class="col-sm-7">Rp{{ $detail['summary']['remaining_balance_formatted'] }}</dd>

                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['status_label'] }}</dd>

                            <dt class="col-sm-5">Catatan</dt>
                            <dd class="col-sm-7">{{ $detail['summary']['notes'] ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>

                @if ($detail['summary']['status_value'] !== 'paid')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Catat Pembayaran Hutang</h4>
                            <p class="mb-0 text-muted">Tanggal pembayaran mengikuti waktu pencatatan sistem.</p>
                        </div>

                        <div class="card-body">
                            @if ($errors->has('debt_payment'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('debt_payment') }}
                                </div>
                            @endif

                            <form action="{{ route('admin.employee-debts.payments.store', ['debtId' => $detail['summary']['id']]) }}" method="post">
                                @csrf

                                <div class="form-group mb-4" data-money-input-group>
                                    <label for="payment_amount_display" class="form-label">Nominal Pembayaran</label>

                                    <input
                                        type="hidden"
                                        id="payment_amount"
                                        name="payment_amount"
                                        value="{{ old('payment_amount') }}"
                                        data-money-raw
                                    >

                                    <input
                                        type="text"
                                        id="payment_amount_display"
                                        value="{{ old('payment_amount') }}"
                                        class="form-control @error('payment_amount') is-invalid @enderror"
                                        placeholder="Contoh: 250.000"
                                        inputmode="numeric"
                                        data-money-display
                                        required
                                    >

                                    @error('payment_amount')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
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
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-start gap-2">
                                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Pembayaran</h4>
                        <p class="mb-0 text-muted">Riwayat cicilan/pelunasan hutang karyawan.</p>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Nominal</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($detail['payments'] as $payment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $payment['payment_date'] }}</td>
                                            <td>Rp{{ $payment['amount_formatted'] }}</td>
                                            <td>{{ $payment['notes'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada pembayaran hutang.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
