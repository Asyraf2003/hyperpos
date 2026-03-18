@extends('layouts.app')

@section('title', 'Product')
@section('heading', 'Product')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Master barang bengkel</h4>
                        <p class="mb-0 text-muted">
                            Daftar product master untuk fondasi operasional admin.
                        </p>
                    </div>

                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        Tambah Product
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.products.index') }}" method="get" class="row g-3 mb-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <input
                            type="text"
                            name="q"
                            value="{{ $query ?? '' }}"
                            class="form-control"
                            placeholder="Cari kode, nama, atau merek"
                        >
                    </div>

                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary">
                            Cari
                        </button>
                    </div>

                    @if (($query ?? '') !== '')
                        <div class="col-12 col-md-auto">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light-secondary">
                                Reset
                            </a>
                        </div>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr>
                                <th style="width: 64px;">#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Merek</th>
                                <th>Ukuran</th>
                                <th>Harga Jual</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $index => $product)
                                <tr>
                                    <td>{{ $products->firstItem() + $index }}</td>
                                    <td>{{ $product->kodeBarang() ?? '-' }}</td>
                                    <td>{{ $product->namaBarang() }}</td>
                                    <td>{{ $product->merek() }}</td>
                                    <td>{{ $product->ukuran() ?? '-' }}</td>
                                    <td>Rp {{ number_format($product->hargaJual()->amount(), 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.products.edit', ['productId' => $product->id()]) }}" class="btn btn-sm btn-outline-secondary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        @if (($query ?? '') !== '')
                                            Tidak ada product yang cocok dengan pencarian.
                                        @else
                                            Belum ada product master.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->total() > 0)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                        <small class="text-muted">
                            Total: {{ $products->total() }} product
                        </small>

                        @include('layouts.partials.pagination', ['paginator' => $products])
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
