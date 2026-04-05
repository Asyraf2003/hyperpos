<div class="card">
    <div class="card-body">
        <div class="fw-bold mb-1">Kelola Nota</div>
        <div class="text-muted small mb-3">
            Perubahan rincian dilakukan dari workspace agar posisi edit tetap sama seperti halaman buat transaksi.
        </div>

        @if (($note['can_edit_workspace'] ?? false) && $note['can_add_rows'])
            <div class="d-grid gap-2">
                <a
                    href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id']]) }}"
                    class="btn btn-primary"
                >
                    Edit Nota
                </a>

                <div class="small text-muted">
                    Tambah servis, produk, dan ubah rincian dilakukan dari halaman edit workspace.
                </div>
            </div>
        @else
            <div class="border rounded p-3 bg-light">
                <div class="fw-semibold mb-1">Nota tidak bisa diedit bebas</div>

                @if ($note['is_closed'])
                    <div class="text-muted small">
                        Nota sudah ditutup. Perubahan hanya bisa dilanjutkan setelah dibuka ulang oleh admin.
                    </div>
                @elseif ($note['correction_notice'] !== null)
                    <div class="text-muted small">{{ $note['correction_notice'] }}</div>
                @else
                    <div class="text-muted small">
                        Gunakan alur koreksi atau pembayaran sesuai status transaksi saat ini.
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
