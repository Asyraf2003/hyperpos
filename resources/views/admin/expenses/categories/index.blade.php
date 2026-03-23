@extends('layouts.app')

@section('title', 'Kategori Pengeluaran')
@section('heading', 'Kategori Pengeluaran')

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Master Kategori Pengeluaran</h4>
                        <p class="mb-0 text-muted">Kategori biaya operasional yang dipakai saat pencatatan expense.</p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-light-secondary">Kembali ke Pengeluaran</a>
                        <a href="{{ route('admin.expenses.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if ($rows === [])
                    <div class="text-center text-muted py-5">Belum ada kategori pengeluaran.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-lg">
                            <thead>
                                <tr class="text-nowrap">
                                    <th style="width: 64px;">No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['code'] }}</td>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['description'] ?? '-' }}</td>
                                        <td>
                                            @if ($row['is_active'])
                                                <span class="badge bg-light-success text-success">Aktif</span>
                                            @else
                                                <span class="badge bg-light-secondary text-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('admin.expenses.categories.edit', ['categoryId' => $row['id']]) }}" class="btn btn-sm btn-light-primary">
                                                    Edit
                                                </a>

                                                @if ($row['is_active'])
                                                    <form action="{{ route('admin.expenses.categories.deactivate', ['categoryId' => $row['id']]) }}" method="post">
                                                        @csrf
                                                        @method('patch')
                                                        <button type="submit" class="btn btn-sm btn-light-danger">Nonaktifkan</button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.expenses.categories.activate', ['categoryId' => $row['id']]) }}" method="post">
                                                        @csrf
                                                        @method('patch')
                                                        <button type="submit" class="btn btn-sm btn-light-success">Aktifkan</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
