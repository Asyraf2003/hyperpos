@extends('layouts.app')

@section('title', 'Jasa')
@section('heading', 'Jasa')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Dipakai untuk lookup kasir dan paket service</h4>
                    </div>

                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                        Tambah Jasa
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr>
                                <th>Nama Jasa</th>
                                <th>Nama Normal</th>
                                <th>Default Harga</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($services as $service)
                                <tr>
                                    <td class="fw-semibold">{{ $service['name'] }}</td>
                                    <td><small class="text-muted">{{ $service['normalized_name'] }}</small></td>
                                    <td>{{ number_format($service['default_price_rupiah'], 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge {{ $service['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $service['is_active'] ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-service-action="open"
                                            data-service-name="{{ $service['name'] }}"
                                            data-service-normalized="{{ $service['normalized_name'] }}"
                                            data-service-status="{{ $service['is_active'] ? 'active' : 'inactive' }}"
                                            data-edit-url="{{ route('admin.services.edit', ['serviceId' => $service['id']]) }}"
                                            data-deactivate-url="{{ route('admin.services.deactivate', ['serviceId' => $service['id']]) }}"
                                            data-activate-url="{{ route('admin.services.activate', ['serviceId' => $service['id']]) }}"
                                        >
                                            Aksi
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada jasa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            class="modal fade"
            id="service-action-modal"
            tabindex="-1"
            aria-labelledby="service-action-modal-title"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div class="w-100">
                            <h3 class="modal-title fw-bold mb-1" id="service-action-modal-title">Aksi Jasa</h3>
                            <p class="mb-0 text-muted fs-6" id="service-action-modal-subtitle">
                                Pilih tindakan untuk jasa.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body px-4 pb-4 pt-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <a href="#" id="service-action-edit-link" class="btn btn-outline-primary w-100 text-start py-3 px-4 h-100">
                                    <div class="fw-bold fs-5 mb-1">Edit Jasa</div>
                                </a>
                            </div>

                            <div class="col-12 col-md-6">
                                <form id="service-action-status-form" method="post" class="h-100">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        id="service-action-status-button"
                                        class="btn btn-outline-warning w-100 text-start py-3 px-4 h-100"
                                    >
                                        <div class="fw-bold fs-5 mb-1" id="service-action-status-title">Nonaktifkan</div>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/static/js/pages/admin-service-actions.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
