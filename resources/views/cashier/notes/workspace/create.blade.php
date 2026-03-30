@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    @if ($errors->has('workspace'))
        <div class="alert alert-danger">{{ $errors->first('workspace') }}</div>
    @endif

    <form action="{{ route('notes.workspace.store') }}" method="POST" novalidate id="cashier-note-workspace-form">
        @csrf
        <div class="row">
            @include('cashier.notes.workspace.partials.rincian-card')
            @include('cashier.notes.workspace.partials.info-card')
        </div>
        @include('cashier.notes.workspace.partials.payment-modal')
    </form>

    <script id="cashier-note-workspace-config" type="application/json">{!! $workspaceConfigJson !!}</script>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-money-input.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/cashier-note-workspace/rows.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/cashier-note-workspace/search.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/cashier-note-workspace/summary.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/cashier-note-workspace/payment-flow.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/cashier-note-workspace/boot.js') }}"></script>
@endpush
