@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    @include('cashier.notes.partials.note-overview')
    @include('cashier.notes.partials.correction-history')
    @include('cashier.notes.partials.add-rows-form')
    @include('cashier.notes.partials.payment-form')
    @include('cashier.notes.partials.note-rows-table')
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-add-rows.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}"></script>
@endpush
