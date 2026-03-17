@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_heading', 'Admin Dashboard')

@section('sidebar')
    @include('layouts.partials.sidebar-admin')
@endsection

@section('content')
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Total Barang</h6>
                            <h4 class="font-extrabold mb-0">128</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Supplier</h6>
                            <h4 class="font-extrabold mb-0">24</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Nota Hari Ini</h6>
                            <h4 class="font-extrabold mb-0">42</h4>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body py-4 px-4">
                            <h6 class="text-muted font-semibold">Pendapatan</h6>
                            <h4 class="font-extrabold mb-0">Rp 2.450.000</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Menu Dummy Admin</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">Kelola Barang</a>
                        <a href="#" class="list-group-item list-group-item-action">Kelola Supplier</a>
                        <a href="#" class="list-group-item list-group-item-action">Procurement</a>
                        <a href="#" class="list-group-item list-group-item-action">Laporan</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4>Info Role</h4>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Role:</strong> Admin</p>
                    <p class="mb-0"><strong>Status:</strong> Dummy UI only</p>
                </div>
            </div>
        </div>
    </section>
@endsection
