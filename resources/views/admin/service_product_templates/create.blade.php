@extends('layouts.app')

@section('title', 'Tambah Template Jasa + Produk')
@section('heading', 'Tambah Template Jasa + Produk')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Tambah Template Jasa + Produk</h4>
                        <p class="text-muted mb-0">Template ini dipakai untuk autofill kasir saat memilih produk pada mode servis + sparepart toko.</p>
                    </div>

                    <div class="card-body">
                        @include('admin.service_product_templates.partials.form', [
                            'action' => route('admin.service-product-templates.store'),
                            'method' => 'POST',
                            'submitLabel' => 'Simpan Template',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
