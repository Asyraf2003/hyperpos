@extends('layouts.app')

@section('title', 'Pengeluaran Operasional')
@section('heading', 'Pengeluaran Operasional')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Daftar Pengeluaran Operasional</h4>
                        <p class="mb-0 text-muted">
                            Daftar pengeluaran operasional bengkel.
                        </p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <a href="{{ route('admin.expenses.categories.index') }}" class="btn btn-light-secondary">
                            Kelola Kategori
                        </a>
                        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                            Catat Pengeluaran
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if ($rows === [])
                    <div class="text-center text-muted py-5">
                        Belum ada pengeluaran operasional tercatat.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                                <tr class="text-nowrap">
                                    <th style="width: 64px;">No</th>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Nominal</th>
                                    <th>Metode Bayar</th>
                                    <th>Referensi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['expense_date'] }}</td>
                                        <td>{{ $row['category_name'] }} ({{ $row['category_code'] }})</td>
                                        <td>{{ $row['description'] }}</td>
                                        <td>Rp {{ number_format($row['amount_rupiah'], 0, ',', '.') }}</td>
                                        <td>{{ $row['payment_method'] }}</td>
                                        <td>{{ $row['reference_no'] ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $row['status_badge_class'] }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
