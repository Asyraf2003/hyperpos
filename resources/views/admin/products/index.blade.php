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
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <input type="text" class="form-control" placeholder="Pencarian product menyusul" disabled>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-lg">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Merek</th>
                                <th>Ukuran</th>
                                <th>Harga Jual</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->kodeBarang() ?? '-' }}</td>
                                    <td>{{ $product->namaBarang() }}</td>
                                    <td>{{ $product->merek() }}</td>
                                    <td>{{ $product->ukuran() ?? '-' }}</td>
                                    <td>Rp {{ number_format($product->hargaJual()->amount(), 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada product master.
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
