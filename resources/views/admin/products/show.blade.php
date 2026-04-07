@extends('layouts.app')

@section('title', 'Detail Produk')
@section('heading', 'Detail Produk')

@section('content')
    @php
        $product = $detail['product'];
        $initialIdentity = $detail['initial_identity'];
        $hasIdentityChanges = $detail['has_identity_changes'];
    @endphp

    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $product['nama_barang'] }}</h4>
                <p class="text-muted mb-0">
                    Kode: {{ $product['kode_barang'] ?: '-' }} ·
                    Merek: {{ $product['merek'] }} ·
                    Ukuran: {{ $product['ukuran'] ?? '-' }}
                </p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-light-secondary">Kembali</a>
                <a href="{{ route('admin.products.edit', ['productId' => $product['id']]) }}#product-master-form" class="btn btn-primary">
                    Edit Identitas
                </a>
                <a href="{{ route('admin.products.edit', ['productId' => $product['id']]) }}#product-stock-adjustment-form" class="btn btn-warning">
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
                            <div class="fw-semibold">{{ $product['kode_barang'] ?: '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Barang</small>
                            <div class="fw-semibold">{{ $product['nama_barang'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Merek</small>
                            <div class="fw-semibold">{{ $product['merek'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Ukuran</small>
                            <div class="fw-semibold">{{ $product['ukuran'] ?? '-' }}</div>
                        </div>

                        <div>
                            <small class="text-muted d-block">Harga Jual</small>
                            <div class="fw-semibold">Rp {{ number_format((int) $product['harga_jual'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                @if ($initialIdentity !== null)
                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title mb-0">Identitas Awal</h5>
                                @if ($hasIdentityChanges)
                                    <span class="badge bg-light-warning text-warning">Pernah berubah</span>
                                @else
                                    <span class="badge bg-light-secondary text-secondary">Belum berubah</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Kode Barang Awal</small>
                                <div class="fw-semibold">{{ $initialIdentity['kode_barang'] ?: '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Nama Barang Awal</small>
                                <div class="fw-semibold">{{ $initialIdentity['nama_barang'] }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Merek Awal</small>
                                <div class="fw-semibold">{{ $initialIdentity['merek'] }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Ukuran Awal</small>
                                <div class="fw-semibold">{{ $initialIdentity['ukuran'] ?? '-' }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Harga Jual Awal</small>
                                <div class="fw-semibold">Rp {{ number_format((int) $initialIdentity['harga_jual'], 0, ',', '.') }}</div>
                            </div>

                            <div>
                                <small class="text-muted d-block">Tercatat Pada</small>
                                <div class="fw-semibold">{{ $initialIdentity['changed_at'] }}</div>
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
                                    @php
                                        $snapshot = $entry['snapshot'];
                                    @endphp

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
                                                    <div class="fw-semibold">{{ $snapshot['kode_barang'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Nama Barang</small>
                                                    <div class="fw-semibold">{{ $snapshot['nama_barang'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Merek</small>
                                                    <div class="fw-semibold">{{ $snapshot['merek'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Ukuran</small>
                                                    <div class="fw-semibold">{{ $snapshot['ukuran'] ?? '-' }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Harga Jual</small>
                                                    <div class="fw-semibold">
                                                        Rp {{ number_format((int) ($snapshot['harga_jual'] ?? 0), 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                @if (array_key_exists('deleted_at', $snapshot))
                                                    <div class="col-12 col-md-6">
                                                        <small class="text-muted d-block">Deleted At</small>
                                                        <div class="fw-semibold">{{ $snapshot['deleted_at'] ?: '-' }}</div>
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
