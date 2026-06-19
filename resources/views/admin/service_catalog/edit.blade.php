@extends('layouts.app')

@section('title', 'Edit Jasa')
@section('heading', 'Edit Jasa')

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Perubahan tidak mengubah nota historis</h4>
                    </div>

                    <div class="card-body">
                        @include('admin.service_catalog.partials.form', [
                            'action' => route('admin.services.update', ['serviceId' => $service['id']]),
                            'method' => 'PUT',
                            'submitLabel' => 'Update Jasa',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
