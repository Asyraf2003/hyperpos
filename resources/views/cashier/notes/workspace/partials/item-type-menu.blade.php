<div
    id="workspace-item-type-menu"
    class="card shadow-sm d-none position-absolute end-0 mt-2"
    style="z-index: 30; min-width: 320px;"
>
    <div class="card-body p-2">
        @foreach ($itemTypeOptions as $option)
            <button
                type="button"
                class="btn btn-light w-100 text-start mb-2 text-dark"
                data-add-item-type="{{ $option['type'] }}"
            >
                <span class="fw-semibold d-block text-dark">{{ $option['label'] }}</span>
                <small class="text-black-50">{{ $option['help'] }}</small>
            </button>
        @endforeach
    </div>
</div>