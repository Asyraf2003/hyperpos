@if ($note['can_add_rows'])
    <div class="card mt-3">
        <div class="card-body">
            <div class="fw-bold mb-2">Tambah Baris Nota</div>

            <form method="POST" action="{{ $addRowsAction }}" id="note-add-rows-form">
                @csrf

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary" id="detail-add-service-row">Tambah Servis</button>
                    <button type="button" class="btn btn-outline-secondary" id="detail-add-product-row">Tambah Produk</button>
                </div>

                <div id="detail-note-rows"></div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Simpan Baris Baru</button>
                </div>
            </form>

            <script id="note-add-rows-config" type="application/json">@json(['oldRows' => $oldRows, 'productOptions' => $productOptions])</script>
        </div>
    </div>
@elseif ($note['correction_notice'] !== null)
    <div class="alert alert-warning mt-3 mb-0">{{ $note['correction_notice'] }}</div>
@endif
