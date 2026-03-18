@extends('layouts.app')

@section('title', 'Kasir Dashboard')
@section('heading', 'Kasir Dashboard')

@section('content')
    <section class="row">
        <div class="col-12 col-lg-8">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Nota Aktif</h6>
                            <h4 class="font-extrabold mb-0">6</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Transaksi Hari Ini</h6>
                            <h4 class="font-extrabold mb-0">19</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Kas Masuk</h6>
                            <h4 class="font-extrabold mb-0">Rp 875.000</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Menu Dummy Kasir</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">Buat Nota</a>
                        <a href="#" class="list-group-item list-group-item-action">Cari Pelanggan</a>
                        <a href="#" class="list-group-item list-group-item-action">Daftar Transaksi</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Info Halaman</h4>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Area:</strong> Kasir</p>
                    <p class="mb-0"><strong>Status:</strong> Dummy UI only</p>
                </div>
            </div>
        </div>
    </section>
@endsection