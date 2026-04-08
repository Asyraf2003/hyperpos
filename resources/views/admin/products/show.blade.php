@extends('layouts.app')

@section('title', 'Detail Produk')
@section('heading', 'Detail Produk')

@section('content')
    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $page['heading'] }}</h4>
                <p class="text-muted mb-0">{{ $page['subtitle'] }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a href="{{ $page['actions']['back_url'] }}" class="btn btn-light-secondary">Kembali</a>
                <a href="{{ $page['actions']['edit_identity_url'] }}" class="btn btn-primary">
                    Edit Identitas
                </a>
                <a href="{{ $page['actions']['stock_adjustment_url'] }}" class="btn btn-warning">
                    Ubah Stok
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Identitas Saat Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Kode Barang</small>
                            <div class="fw-semibold">{{ $page['current_identity']['kode_barang'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Barang</small>
                            <div class="fw-semibold">{{ $page['current_identity']['nama_barang'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Merek</small>
                            <div class="fw-semibold">{{ $page['current_identity']['merek'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Ukuran</small>
                            <div class="fw-semibold">{{ $page['current_identity']['ukuran'] }}</div>
                        </div>

                        <div>
                            <small class="text-muted d-block">Harga Jual</small>
                            <div class="fw-semibold">{{ $page['current_identity']['harga_jual_label'] }}</div>
                        </div>
                    </div>
                </div>

                @if ($page['initial_identity'] !== null || $page['initial_identity_meta']['note'] !== null)
                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title mb-0">{{ $page['initial_identity_meta']['title'] }}</h5>
                                <span class="badge bg-light-{{ $page['initial_identity_meta']['badge_tone'] }} text-{{ $page['initial_identity_meta']['badge_tone'] }}">
                                    {{ $page['initial_identity_meta']['badge_label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($page['initial_identity_meta']['note'] !== null)
                                <div class="alert alert-light-{{ $page['initial_identity_meta']['badge_tone'] }} mb-4">
                                    {{ $page['initial_identity_meta']['note'] }}
                                </div>
                            @endif

                            @if ($page['initial_identity'] !== null)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Kode Barang</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['kode_barang'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Nama Barang</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['nama_barang'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Merek</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['merek'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Ukuran</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['ukuran'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Harga Jual</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['harga_jual_label'] }}</div>
                                </div>

                                <div>
                                    <small class="text-muted d-block">Tercatat Pada</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['changed_at'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Riwayat Versi Produk</h5>
                    </div>
                    <div class="card-body">
                        @if (count($page['timeline']) === 0)
                            <p class="text-muted mb-0">Belum ada riwayat versi produk.</p>
                        @else
                            <div class="timeline">
                                @foreach ($page['timeline'] as $entry)
                                    <div class="timeline-item pb-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    {{ $entry['revision_label'] }} · {{ $entry['event_name'] }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $entry['changed_at'] }}
                                                    @if ($entry['actor_label'])
                                                        · {{ $entry['actor_label'] }}
                                                    @endif
                                                </small>
                                            </div>

                                            @if ($entry['reason_label'])
                                                <span class="badge bg-light-info text-info align-self-start">
                                                    {{ $entry['reason_label'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="border rounded p-3 bg-light-subtle">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Kode Barang</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['kode_barang'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Nama Barang</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['nama_barang'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Merek</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['merek'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Ukuran</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['ukuran'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Harga Jual</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['harga_jual_label'] }}</div>
                                                </div>

                                                @if ($entry['snapshot']['deleted_at'] !== null)
                                                    <div class="col-12 col-md-6">
                                                        <small class="text-muted d-block">Deleted At</small>
                                                        <div class="fw-semibold">{{ $entry['snapshot']['deleted_at'] }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
