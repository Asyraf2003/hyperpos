@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('cashier.notes.index'))

@section('content')
<div class="page-content">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                @include('cashier.notes.partials.note-overview')
                @include('cashier.notes.partials.note-rows-table')
                @include('cashier.notes.partials.correction-history')
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-4">
                @include('cashier.notes.partials.add-rows-form')

                @if ($note['is_open'])
                    @include('cashier.notes.partials.payment-form')
                @endif

                @if ($note['can_show_refund_form'] ?? false)
                    @include('cashier.notes.partials.refund-form')
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-payment.js')) }}"></script>
@endpush
