@extends('layouts.app')

@section('title', 'Template Jasa + Produk')
@section('heading', 'Template Jasa + Produk')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Template fast entry untuk servis + sparepart toko</h4>
                        <p class="text-muted mb-0">Produk tetap memakai harga jual murni. Template hanya mengisi jasa dan default total paket.</p>
                    </div>

                    <a href="{{ route('admin.service-product-templates.create') }}" class="btn btn-primary">
                        Tambah Template
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

                @error('product_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jasa</th>
                                <th>Default Jasa</th>
                                <th>Total Paket</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th style="width: 240px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $template['nama_barang'] }}</div>
                                        <small class="text-muted">
                                            {{ $template['kode_barang'] ?: '-' }} · harga jual {{ number_format($template['harga_jual'], 0, ',', '.') }}
                                        </small>
                                    </td>
                                    <td>{{ $template['service_name'] }}</td>
                                    <td>{{ number_format($template['default_service_price_rupiah'], 0, ',', '.') }}</td>
                                    <td>
                                        {{ $template['default_package_total_rupiah'] !== null ? number_format($template['default_package_total_rupiah'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $template['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $template['is_active'] ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>{{ $template['sort_order'] }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.service-product-templates.edit', ['templateId' => $template['id']]) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>

                                            @if ($template['is_active'])
                                                <form action="{{ route('admin.service-product-templates.deactivate', ['templateId' => $template['id']]) }}" method="post" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.service-product-templates.reactivate', ['templateId' => $template['id']]) }}" method="post" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        Aktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada template jasa + produk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
