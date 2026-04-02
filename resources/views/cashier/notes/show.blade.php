@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                @include('cashier.notes.partials.note-overview')
                @include('cashier.notes.partials.note-rows-table')
                @include('cashier.notes.partials.correction-actions')
                @include('cashier.notes.partials.correction-history')
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-4">
                @include('cashier.notes.partials.add-rows-form')
                @include('cashier.notes.partials.payment-form')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}"></script>
@endpush
