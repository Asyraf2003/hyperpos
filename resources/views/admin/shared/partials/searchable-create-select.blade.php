<div class="form-group mb-4" data-searchable-create-select>
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="form-select @error($name) is-invalid @enderror"
        required
        data-searchable-create-select-native
        data-placeholder="{{ $placeholder ?? 'Cari / pilih data' }}"
        data-empty-message="{{ $emptyMessage ?? 'Data tidak ditemukan.' }}"
        data-create-url="{{ $createUrl }}"
        data-create-label="{{ $createLabel ?? 'Buat data baru' }}"
    >
        <option value="">{{ $placeholder ?? 'Cari / pilih data' }}</option>
        @foreach ($options as $option)
            <option
                value="{{ $option['id'] }}"
                @selected((string) ($selected ?? '') === (string) $option['id'])
            >
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>

    <div class="searchable-create-select" data-searchable-create-select-ui hidden>
        <input
            type="text"
            class="form-control"
            autocomplete="off"
            placeholder="{{ $placeholder ?? 'Cari / pilih data' }}"
            data-searchable-create-select-search
        >

        <div
            class="list-group mt-2 shadow-sm d-none"
            data-searchable-create-select-results
        ></div>

        <div
            class="alert alert-warning mt-2 mb-0 py-2 px-3 d-none"
            data-searchable-create-select-empty
        >
            <div class="fw-semibold">{{ $emptyMessage ?? 'Data tidak ditemukan.' }}</div>
            <a href="{{ $createUrl }}" class="alert-link">
                {{ $createLabel ?? 'Buat data baru' }}
            </a>
        </div>
    </div>

    @if ($help ?? null)
        <small class="text-muted">{{ $help }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
