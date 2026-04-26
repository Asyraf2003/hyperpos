@extends('layouts.app')

@section('title', 'Audit Log')
@section('heading', 'Audit Log')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Audit Log Sistem</h4>
                        <p class="text-muted mb-0">
                            Halaman ini hanya untuk membaca jejak audit. Data audit tidak bisa diedit atau dihapus.
                        </p>
                    </div>

                    <form method="get" action="{{ route('admin.audit-logs.index') }}" class="m-0 d-flex gap-2">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Cari event atau alasan"
                            autocomplete="off"
                            style="min-height: 48px;"
                        >

                        <button type="submit" class="btn btn-primary px-4" style="min-height: 48px;">
                            Cari
                        </button>

                        @if ($search !== '')
                            <a
                                href="{{ route('admin.audit-logs.index') }}"
                                class="btn btn-light-secondary px-4 d-flex align-items-center"
                                style="min-height: 48px;"
                            >
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-light-primary">
                    Audit log bersifat read-only. Tidak ada tombol edit, hapus, void, atau mutation lain di halaman ini.
                </div>

                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr class="text-nowrap">
                                <th style="width: 80px;">ID</th>
                                <th style="width: 180px;">Waktu</th>
                                <th style="width: 260px;">Event</th>
                                <th style="width: 260px;">Alasan</th>
                                <th>Context</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $entry)
                                <tr>
                                    <td>{{ $entry['id'] }}</td>
                                    <td class="text-nowrap">{{ $entry['created_at'] }}</td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">
                                            {{ $entry['event'] }}
                                        </span>
                                    </td>
                                    <td>{{ $entry['reason'] }}</td>
                                    <td>
                                        <pre class="mb-0 small text-muted" style="white-space: pre-wrap;">{{ $entry['context_json'] }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada audit log yang cocok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                    <small class="text-muted">
                        Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} audit log.
                    </small>

                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
