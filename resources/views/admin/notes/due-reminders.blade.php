@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Reminder Jatuh Tempo Nota</h4>
                    <p class="text-muted mb-0">
                        Daftar nota open yang sudah jatuh tempo atau akan jatuh tempo dalam 5 hari.
                    </p>
                </div>

                <div class="text-xl-end">
                    <span class="badge bg-light-primary text-primary">
                        Hari acuan: {{ $today }}
                    </span>
                    <div class="small text-muted mt-1">
                        Maksimal 100 nota
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="alert alert-light-secondary mb-3">
                Halaman ini hanya untuk membaca reminder. Tidak ada aksi edit atau hapus dari daftar ini.
            </div>

            <div class="table-responsive">
                <table class="table table-lg">
                    <thead>
                        <tr class="text-nowrap">
                            <th>Nota / Customer</th>
                            <th>No. HP</th>
                            <th>Tanggal Transaksi</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Sisa Tagihan</th>
                            <th class="text-end">Terlambat</th>
                            <th style="width: 120px;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row->customerName }}</div>
                                    <div class="small text-muted">{{ $row->noteId }}</div>
                                </td>
                                <td>{{ $row->customerPhone ?? '-' }}</td>
                                <td>{{ $row->transactionDate }}</td>
                                <td>{{ $row->dueDate }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($row->outstandingRupiah, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ $row->daysOverdue }} hari
                                </td>
                                <td>
                                    <a
                                        href="{{ route('admin.notes.show', ['noteId' => $row->noteId]) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada nota jatuh tempo yang perlu ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Menampilkan {{ count($rows) }} nota. Pagination belum aktif untuk MVP reminder ini.
            </small>
        </div>
    </div>
</section>
@endsection
