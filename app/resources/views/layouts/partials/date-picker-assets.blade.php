@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/flatpickr/flatpickr.css') }}?v={{ filemtime(public_path('assets/extensions/flatpickr/flatpickr.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/extensions/flatpickr/flatpickr.js') }}?v={{ filemtime(public_path('assets/extensions/flatpickr/flatpickr.js')) }}"></script>
    <script src="{{ asset('assets/extensions/flatpickr/l10n/id.js') }}?v={{ filemtime(public_path('assets/extensions/flatpickr/l10n/id.js')) }}"></script>
    <script src="{{ asset('assets/static/js/shared/admin-date-input.js') }}?v={{ filemtime(public_path('assets/static/js/shared/admin-date-input.js')) }}"></script>
@endpush
