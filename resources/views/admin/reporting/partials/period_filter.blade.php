<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="get"
                    action="{{ $action }}"
                    class="row g-3"
                    id="{{ $formId }}"
                    data-report-period-filter="1"
                >
                    <div class="col-12 col-lg-3">
                        <label for="{{ $formId }}-period-mode" class="form-label">Mode Periode</label>
                        <select
                            name="period_mode"
                            id="{{ $formId }}-period-mode"
                            class="form-select"
                        >
                            <option value="daily" {{ $filters['period_mode'] === 'daily' ? 'selected' : '' }}>Harian</option>
                            <option value="weekly" {{ $filters['period_mode'] === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                            <option value="monthly" {{ $filters['period_mode'] === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="custom" {{ $filters['period_mode'] === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label for="{{ $formId }}-reference-date" class="form-label">Reference Date</label>
                        <input
                            type="date"
                            name="reference_date"
                            id="{{ $formId }}-reference-date"
                            class="form-control"
                            value="{{ $filters['reference_date'] }}"
                            data-ui-date="single"
                            data-ui-date-placeholder="Pilih tanggal referensi"
                            autocomplete="off"
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="{{ $formId }}-date-range">Rentang Tanggal</label>

                        <div data-report-range-enhanced-wrap class="d-none">
                            <input
                                type="text"
                                id="{{ $formId }}-date-range"
                                class="form-control"
                                data-ui-date="range-single"
                                data-ui-date-placeholder="Pilih rentang tanggal"
                                data-range-start-name="date_from"
                                data-range-end-name="date_to"
                                autocomplete="off"
                            >
                        </div>

                        <div data-report-range-fallback-wrap class="row g-2">
                            <div class="col-6">
                                <label
                                    class="form-label small text-muted"
                                    for="{{ $formId }}-date-from-fallback"
                                >
                                    Dari
                                </label>
                                <input
                                    type="date"
                                    id="{{ $formId }}-date-from-fallback"
                                    class="form-control"
                                    data-report-date-fallback-from
                                    value="{{ $filters['date_from'] }}"
                                    autocomplete="off"
                                >
                            </div>

                            <div class="col-6">
                                <label
                                    class="form-label small text-muted"
                                    for="{{ $formId }}-date-to-fallback"
                                >
                                    Sampai
                                </label>
                                <input
                                    type="date"
                                    id="{{ $formId }}-date-to-fallback"
                                    class="form-control"
                                    data-report-date-fallback-to
                                    value="{{ $filters['date_to'] }}"
                                    autocomplete="off"
                                >
                            </div>

                            <div class="col-12">
                                <small class="text-muted">Fallback tanggal aktif bila kalender tidak tersedia.</small>
                            </div>
                        </div>

                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        <a href="{{ $resetUrl }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="alert alert-light border mt-3 mb-0">
                    @if (!empty($noteText))
                        <div>{{ $noteText }}</div>
                    @endif

                    {{ $rangeLabelText }}: <strong>{{ $filters['range_label'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-report-period-filter.js') }}?v={{ filemtime(public_path('assets/static/js/shared/admin-report-period-filter.js')) }}"></script>
@endpush
