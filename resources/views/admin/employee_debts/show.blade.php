@extends('layouts.app')

@section('title', 'Detail Hutang Karyawan')
@section('heading', 'Detail Hutang Karyawan')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-row justify-content-between align-items-center gap-2">
                            <div>
                                <h4 class="card-title mb-1">Ringkasan Hutang</h4>
                            </div>
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
            </div>

            <div class="col-12 col-xl-7">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Pembayaran</h4>
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

                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Reversal Pembayaran</h4>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Waktu Reversal</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Nominal</th>
                                        <th>Catatan Pembayaran</th>
                                        <th>Alasan Reversal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($paymentReversals as $reversal)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $reversal['recorded_at'] }}</td>
                                            <td>{{ $reversal['payment_date'] }}</td>
                                            <td>Rp{{ $reversal['amount_formatted'] }}</td>
                                            <td>{{ $reversal['payment_notes'] ?? '-' }}</td>
                                            <td>{{ $reversal['reason'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada reversal pembayaran hutang.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Riwayat Koreksi Hutang</h4>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-lg">
                                <thead>
                                    <tr class="text-nowrap">
                                        <th style="width: 64px;">No</th>
                                        <th>Waktu</th>
                                        <th>Tipe</th>
                                        <th>Nominal</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($adjustments as $adjustment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $adjustment['recorded_at'] }}</td>
                                            <td>{{ $adjustment['adjustment_type_label'] }}</td>
                                            <td>Rp{{ $adjustment['amount_formatted'] }}</td>
                                            <td>{{ $adjustment['reason'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada koreksi hutang.</td>
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
