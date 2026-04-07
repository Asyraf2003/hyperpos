@extends('layouts.app')

@section('title', 'Detail Produk')
@section('heading', 'Detail Produk')

@section('content')
    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $detail['product']['nama_barang'] }}</h4>
                <p class="text-muted mb-0">
                    Kode: {{ $detail['product']['kode_barang'] ?: '-' }} ·
                    Merek: {{ $detail['product']['merek'] }} ·
                    Ukuran: {{ $detail['product']['ukuran'] ?? '-' }}
                </p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-light-secondary">Kembali</a>
                <a href="{{ route('admin.products.edit', ['productId' => $detail['product']['id']]) }}#product-master-form" class="btn btn-primary">
                    Edit Identitas
                </a>
                <a href="{{ route('admin.products.edit', ['productId' => $detail['product']['id']]) }}#product-stock-adjustment-form" class="btn btn-warning">
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
                            <div class="fw-semibold">{{ $detail['product']['kode_barang'] ?: '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Barang</small>
                            <div class="fw-semibold">{{ $detail['product']['nama_barang'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Merek</small>
                            <div class="fw-semibold">{{ $detail['product']['merek'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Ukuran</small>
                            <div class="fw-semibold">{{ $detail['product']['ukuran'] ?? '-' }}</div>
                        </div>

                        <div>
                            <small class="text-muted d-block">Harga Jual</small>
                            <div class="fw-semibold">Rp {{ number_format((int) $detail['product']['harga_jual'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                @if ($detail['initial_identity'] !== null)
                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title mb-0">Identitas Awal</h5>
                                @if ($detail['has_identity_changes'])
                                    <span class="badge bg-light-warning text-warning">Pernah berubah</span>
                                @else
                                    <span class="badge bg-light-secondary text-secondary">Belum berubah</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Kode Barang Awal</small>
                                <div class="fw-semibold">{{ $detail['initial_identity']['kode_barang'] ?: '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Nama Barang Awal</small>
                                <div class="fw-semibold">{{ $detail['initial_identity']['nama_barang'] }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Merek Awal</small>
                                <div class="fw-semibold">{{ $detail['initial_identity']['merek'] }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Ukuran Awal</small>
                                <div class="fw-semibold">{{ $detail['initial_identity']['ukuran'] ?? '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Harga Jual Awal</small>
                                <div class="fw-semibold">Rp {{ number_format((int) $detail['initial_identity']['harga_jual'], 0, ',', '.') }}</div>
                            </div>

                            <div>
                                <small class="text-muted d-block">Tercatat Pada</small>
                                <div class="fw-semibold">{{ $detail['initial_identity']['changed_at'] }}</div>
                            </div>
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
                        @if (count($timeline) === 0)
                            <p class="text-muted mb-0">Belum ada riwayat versi produk.</p>
                        @else
                            <div class="timeline">
                                @foreach ($timeline as $entry)
                                    <div class="timeline-item pb-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    Rev {{ $entry['revision_no'] }} · {{ $entry['event_name'] }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $entry['changed_at'] }}
                                                    @if ($entry['changed_by_actor_id'])
                                                        · Actor: {{ $entry['changed_by_actor_id'] }}
                                                    @endif
                                                </small>
                                            </div>

                                            @if ($entry['change_reason'])
                                                <span class="badge bg-light-info text-info align-self-start">
                                                    {{ $entry['change_reason'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="border rounded p-3 bg-light-subtle">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Kode Barang</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['kode_barang'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Nama Barang</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['nama_barang'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Merek</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['merek'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Ukuran</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['ukuran'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Harga Jual</small>
                                                    <div class="fw-semibold">
                                                        Rp {{ number_format((int) ($entry['snapshot']['harga_jual'] ?? 0), 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                @if (array_key_exists('deleted_at', $entry['snapshot']))
                                                    <div class="col-12 col-md-6">
                                                        <small class="text-muted d-block">Deleted At</small>
                                                        <div class="fw-semibold">{{ $entry['snapshot']['deleted_at'] ?: '-' }}</div>
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
