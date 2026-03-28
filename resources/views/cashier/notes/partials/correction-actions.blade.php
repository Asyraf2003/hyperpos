@if ($note['payment_status'] === 'paid')
    <div class="card mt-3">
        <div class="card-body">
            <div class="fw-bold mb-3">Correction Paid Note</div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <form method="POST" action="{{ $statusCorrectionAction }}">
                        @csrf
                        <div class="fw-bold mb-2">Correction Status</div>
                        <div class="mb-2">
                            <label class="form-label">Baris</label>
                            <select class="form-select" name="line_no">
                                @foreach ($note['rows'] as $row)
                                    <option value="{{ $row['line_no'] }}">Baris {{ $row['line_no'] }} - {{ $row['type_label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Target Status</label>
                            <select class="form-select" name="target_status">
                                @foreach ($statusOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Alasan Correction</label>
                            <textarea class="form-control" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">Simpan Correction Status</button>
                    </form>
                </div>

                <div class="col-lg-6">
                    <form method="POST" action="{{ $serviceOnlyCorrectionAction }}">
                        @csrf
                        <div class="fw-bold mb-2">Correction Nominal Service Only</div>
                        <div class="mb-2">
                            <label class="form-label">Baris Service</label>
                            <select class="form-select" name="line_no">
                                @foreach ($note['rows'] as $row)
                                    @if ($row['can_correct_service_only'])
                                        <option value="{{ $row['line_no'] }}">Baris {{ $row['line_no'] }} - {{ $row['type_label'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Nama Servis</label><input class="form-control" name="service_name" value="{{ old('service_name') }}" required></div>
                        <div class="mb-2"><label class="form-label">Harga Servis</label><input type="number" min="1" class="form-control" name="service_price_rupiah" value="{{ old('service_price_rupiah') }}" required></div>
                        <div class="mb-2">
                            <label class="form-label">Part Source</label>
                            <select class="form-select" name="part_source">
                                @foreach ($partSourceOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Alasan Correction</label><textarea class="form-control" name="reason" rows="3" required>{{ old('reason') }}</textarea></div>
                        <button type="submit" class="btn btn-warning">Simpan Correction Nominal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
