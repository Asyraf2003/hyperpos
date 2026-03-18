@php
    $authUser = auth()->user();
    $actorLabel = null;

    if ($authUser !== null) {
        $actor = app(\App\Ports\Out\IdentityAccess\ActorAccessReaderPort::class)
            ->findByActorId((string) $authUser->getAuthIdentifier());

        $actorLabel = $actor !== null ? ucfirst($actor->role()->value()) : 'Unknown';
    }
@endphp

<header class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>

        @if ($authUser !== null)
            <div class="d-flex align-items-center gap-2 ms-auto">
                <span class="badge bg-light-secondary">{{ $actorLabel }}</span>
                <span class="text-muted small">{{ $authUser->email }}</span>

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