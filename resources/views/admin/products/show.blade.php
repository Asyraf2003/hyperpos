@extends('layouts.app')

@section('title', 'Detail Produk')
@section('heading', 'Detail Produk')
@section('back_url', route('admin.products.index'))

@section('content')
    <section class="section">
        <div class="ui-page-intro">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h4 class="ui-page-intro-title">{{ $page['heading'] }} {{ $page['subtitle'] }}</h4>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ $page['actions']['edit_identity_url'] }}" class="btn btn-primary">
                        Edit Identitas
                    </a>
                    <a href="{{ $page['actions']['stock_adjustment_url'] }}" class="btn btn-light-primary">
                        Ubah Stok
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="ui-card-stack">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Identitas Saat Ini</h5>
                        </div>
                        <div class="card-body">
                            <div class="ui-key-value mb-3">
                                <small>Kode Barang</small>
                                <div>{{ $page['current_identity']['kode_barang'] }}</div>
                            </div>

                            <div class="ui-key-value mb-3">
                                <small>Nama Barang</small>
                                <div>{{ $page['current_identity']['nama_barang'] }}</div>
                            </div>

                            <div class="ui-key-value mb-3">
                                <small>Merek</small>
                                <div>{{ $page['current_identity']['merek'] }}</div>
                            </div>

                            <div class="ui-key-value mb-3">
                                <small>Ukuran</small>
                                <div>{{ $page['current_identity']['ukuran'] }}</div>
                            </div>

                            <div class="ui-key-value mb-3">
                                <small>Harga Jual</small>
                                <div>{{ $page['current_identity']['harga_jual_label'] }}</div>
                            </div>

                            <div class="ui-key-value mb-3">
                                <small>Mulai Perlu Restok (Reorder Point)</small>
                                <div>{{ $page['current_identity']['reorder_point_qty'] }}</div>
                            </div>

                            <div class="ui-key-value">
                                <small>Batas Stok Kritis</small>
                                <div>{{ $page['current_identity']['critical_threshold_qty'] }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($page['initial_identity'] !== null || $page['initial_identity_meta']['note'] !== null)
                        <div class="card">
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

                                @if ($page['initial_identity_meta']['show_values'] && $page['initial_identity'] !== null)
                                    <div class="ui-key-value mb-3">
                                        <small>Kode Barang</small>
                                        <div>{{ $page['initial_identity']['kode_barang'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Nama Barang</small>
                                        <div>{{ $page['initial_identity']['nama_barang'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Merek</small>
                                        <div>{{ $page['initial_identity']['merek'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Ukuran</small>
                                        <div>{{ $page['initial_identity']['ukuran'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Harga Jual</small>
                                        <div>{{ $page['initial_identity']['harga_jual_label'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Mulai Perlu Restok (Reorder Point)</small>
                                        <div>{{ $page['initial_identity']['reorder_point_qty'] }}</div>
                                    </div>

                                    <div class="ui-key-value mb-3">
                                        <small>Batas Stok Kritis</small>
                                        <div>{{ $page['initial_identity']['critical_threshold_qty'] }}</div>
                                    </div>

                                    <div class="ui-key-value">
                                        <small>Tercatat Pada</small>
                                        <div>{{ $page['initial_identity']['changed_at'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
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
                                                    <div class="ui-key-value">
                                                        <small>Kode Barang</small>
                                                        <div>{{ $entry['snapshot']['kode_barang'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Nama Barang</small>
                                                        <div>{{ $entry['snapshot']['nama_barang'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Merek</small>
                                                        <div>{{ $entry['snapshot']['merek'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Ukuran</small>
                                                        <div>{{ $entry['snapshot']['ukuran'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Harga Jual</small>
                                                        <div>{{ $entry['snapshot']['harga_jual_label'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Mulai Perlu Restok (Reorder Point)</small>
                                                        <div>{{ $entry['snapshot']['reorder_point_qty'] }}</div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="ui-key-value">
                                                        <small>Batas Stok Kritis</small>
                                                        <div>{{ $entry['snapshot']['critical_threshold_qty'] }}</div>
                                                    </div>
                                                </div>

                                                @if ($entry['snapshot']['deleted_at'] !== null)
                                                    <div class="col-12 col-md-6">
                                                        <div class="ui-key-value">
                                                            <small>Dihapus Pada</small>
                                                            <div>{{ $entry['snapshot']['deleted_at'] }}</div>
                                                        </div>
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
