<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AsyrafCloud')</title>

    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>

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
                        <h3 class="mb-0">@yield('heading')</h3>

                        @if (!request()->routeIs('admin.dashboard') && !request()->routeIs('cashier.dashboard'))
                            <button
                                type="button"
                                class="btn btn-light-secondary"
                                onclick="window.history.back()"
                            >
                                Kembali
                            </button>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>

            @include('layouts.partials.footer')
        </div>
    </div>

    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>