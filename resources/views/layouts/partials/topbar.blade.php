<header class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>

        @if (($appShell['user_email'] ?? null) !== null)
            <div class="d-flex align-items-center gap-2 ms-auto">
                @if (($appShell['actor_label'] ?? null) !== null)
                    <span class="badge bg-light-secondary">{{ $appShell['actor_label'] }}</span>
                @endif

                <span class="text-muted small">{{ $appShell['user_email'] }}</span>

                <form action="{{ route('logout') }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Logout
                    </button>
                </form>
            </div>
        @endif
    </div>
</header>