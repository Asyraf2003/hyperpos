@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', $backUrl)

@section('content')
<section class="section">
  <div class="ui-page-intro">
    <div class="small text-muted text-uppercase fw-semibold">{{ $pageIntroEyebrow }}</div>
    <h4 class="ui-page-intro-title">{{ $pageIntroTitle }}</h4>
    <p class="ui-page-intro-subtitle">{{ $pageIntroSubtitle }}</p>
  </div>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-xl-8">
      <div class="ui-card-stack">
        @include('shared.notes.partials.line-workspace')
        @include('shared.notes.partials.versioning-compact', [
          'currentRevision' => ($note['revision_timeline']['current'] ?? []),
          'timelineRevisions' => array_slice(($note['revision_timeline']['timeline'] ?? []), 0, 3),
          'revisionCount' => count($note['revision_timeline']['timeline'] ?? []),
        ])
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="ui-card-stack">
        @include('shared.notes.partials.header-summary')
        @include('shared.notes.partials.payment-summary-actions')
      </div>
    </div>
  </div>

  @include('cashier.notes.partials.payment-modal')
  @include('cashier.notes.partials.refund-modal')
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-payment.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-refund.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-refund.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/note-line-actions.js') }}?v={{ filemtime(public_path('assets/static/js/pages/note-line-actions.js')) }}"></script>
@endpush
