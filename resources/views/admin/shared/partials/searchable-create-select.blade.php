@php
    $selectedValue = (string) ($selected ?? '');
    $helpText = $help ?? null;
    $errorName = $errorName ?? $name;
    $placeholderText = $placeholder ?? 'Cari / pilih data';
    $emptyText = $emptyMessage ?? 'Data tidak ditemukan.';
    $createText = $createLabel ?? 'Buat data baru';
@endphp

<div class="form-group mb-4" data-searchable-create-select>
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="form-select @error($errorName) is-invalid @enderror"
        required
        data-searchable-create-select-native
        data-placeholder="{{ $placeholderText }}"
        data-empty-message="{{ $emptyText }}"
        data-create-url="{{ $createUrl }}"
        data-create-label="{{ $createText }}"
    >
        <option value="">{{ $placeholderText }}</option>
        @foreach ($options as $option)
            <option
                value="{{ $option['id'] }}"
                @selected($selectedValue === (string) $option['id'])
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
            placeholder="{{ $placeholderText }}"
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
            <div class="fw-semibold">{{ $emptyText }}</div>
            <a href="{{ $createUrl }}" class="alert-link">
                {{ $createText }}
            </a>
        </div>
    </div>

    @if ($helpText)
        <small class="text-muted">{{ $helpText }}</small>
    @endif

    @error($errorName)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
