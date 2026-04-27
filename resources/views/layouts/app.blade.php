<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AsyrafCloud')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}?v={{ filemtime(public_path('assets/compiled/css/app.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}?v={{ filemtime(public_path('assets/compiled/css/app-dark.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}?v={{ filemtime(public_path('assets/compiled/css/iconly.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/static/css/ui-foundation.css') }}?v={{ filemtime(public_path('assets/static/css/ui-foundation.css')) }}">
    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}?v={{ filemtime(public_path('assets/static/js/initTheme.js')) }}"></script>

    <div id="app">
        @if (request()->routeIs('admin.*'))
            @include('layouts.partials.sidebar-admin')
        @elseif (request()->routeIs('cashier.*'))
            @include('layouts.partials.sidebar-cashier')
        @endif

        <div id="main">

            <div class="page-content">
                @include('layouts.partials.alerts')

                @hasSection('heading')
                    <div class="page-heading d-flex justify-content-between align-items-center gap-3">
                        <a href="#" class="burger-btn d-block d-xl-none">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                        <h3 class="mb-0">@yield('heading')</h3>

                        @if (!request()->routeIs('admin.dashboard') && !request()->routeIs('cashier.dashboard'))
                            <a
                                href="@yield('back_url', url()->previous())"
                                class="btn btn-light-secondary"
                            >
                                Kembali
                            </a>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>

            @include('layouts.partials.footer')
        </div>
    </div>

    <script src="{{ asset('assets/static/js/components/dark.js') }}?v={{ filemtime(public_path('assets/static/js/components/dark.js')) }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}?v={{ filemtime(public_path('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js')) }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}?v={{ filemtime(public_path('assets/compiled/js/app.js')) }}"></script>
    <script src="{{ asset('assets/static/js/shared/page-freshness.js') }}?v={{ filemtime(public_path('assets/static/js/shared/page-freshness.js')) }}"></script>
    <script
        id="push-notification-config"
        type="application/json"
        data-service-worker-url="{{ asset('service-worker.js') }}"
        data-service-worker-scope="/"
        data-subscribe-url="{{ route('push-notifications.subscriptions.store') }}"
        data-unsubscribe-url="{{ route('push-notifications.subscriptions.destroy') }}"
        data-vapid-public-key="{{ config('services.webpush.vapid_public_key', '') }}"
        data-default-icon="{{ asset('assets/compiled/svg/favicon.svg') }}"
        data-default-url="{{ route('admin.notes.index') }}"
    ></script>
    <script src="{{ asset('assets/static/js/shared/push-notifications.js') }}?v={{ filemtime(public_path('assets/static/js/shared/push-notifications.js')) }}"></script>
    @stack('scripts')
</body>
</html>
