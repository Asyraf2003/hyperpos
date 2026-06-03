<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title>{{ $pageTitle }} - {{ $variants[$activeVariant] }}</title>
    @include('cashier.notes.workspace.mobile-ui-lab.partials.styles')
</head>
<body class="lab-page lab-v{{ $activeVariant }}">
    <main class="lab-shell" data-demo-root>
        <header class="lab-top">
            <small>UI Lab / Variant {{ $activeVariant }}</small>
            <h1>{{ $variants[$activeVariant] }}</h1>
            <p>Dummy UI only. Semua klik dan total hanya simulasi lokal untuk review kenyamanan client.</p>
        </header>

        <nav class="lab-nav" aria-label="UI variant navigation">
            @foreach ($variants as $variantNumber => $variantLabel)
                <a
                    class="{{ $activeVariant === $variantNumber ? 'is-active' : '' }}"
                    href="{{ route('cashier.notes.workspace.mobile-ui-lab', ['variant' => $variantNumber]) }}"
                >
                    {{ $variantNumber }}
                </a>
            @endforeach
        </nav>

        @include("cashier.notes.workspace.mobile-ui-lab.variants.variant-{$activeVariant}")
    </main>

    @include('cashier.notes.workspace.mobile-ui-lab.partials.scripts')
</body>
</html>
