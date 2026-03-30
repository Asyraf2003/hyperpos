<div class="col-12 col-xl-8">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="card-title mb-1">Rincian Nota</h4>
                    <p class="mb-0 text-muted">Tambah rincian sesuai kasus yang sedang terjadi.</p>
                </div>

                <div class="position-relative">
                    <button type="button" class="btn btn-primary" id="workspace-add-button">Tambah Rincian</button>
                    @include('cashier.notes.workspace.partials.item-type-menu')
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="workspace-line-items" data-next-index="{{ count($oldItems) }}"></div>

            <div id="workspace-empty-state" class="border rounded p-4 text-center text-muted">
                Belum ada rincian. Tekan tombol tambah dan pilih jenis rincian yang sesuai.
            </div>
        </div>
    </div>

    @include('cashier.notes.workspace.partials.templates.product')
    @include('cashier.notes.workspace.partials.templates.service')
    @include('cashier.notes.workspace.partials.templates.service-store-stock')
    @include('cashier.notes.workspace.partials.templates.service-external')
</div>
