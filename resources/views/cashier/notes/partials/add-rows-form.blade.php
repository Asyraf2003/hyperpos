<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">Workspace Existing</h5>

        @if (($note['can_edit_workspace'] ?? false) && $note['can_add_rows'])
            <div class="border rounded p-3 bg-light mb-3">
                <div class="fw-semibold mb-1">Mode edit yang tersedia saat ini</div>
                <div class="text-muted small">
                    Phase ini belum membuka true note revision flow. Perubahan isi nota masih memakai workspace existing, sedangkan pembacaan histori memakai pseudo-versioning read model.
                </div>
            </div>

            <div class="ui-form-actions">
                <a
                    href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id']]) }}"
                    class="btn btn-light-primary"
                >
                    Lanjut Edit Workspace Existing
                </a>
            </div>
        @else
            <div class="border rounded p-3 bg-light">
                <div class="fw-semibold mb-1">Workspace tidak aktif</div>

                @if ($note['is_closed'])
                    <div class="text-muted small">
                        Workspace existing tidak dipakai untuk nota yang sudah close. Gunakan billing projection untuk pembayaran dan refund flow untuk line yang dibatalkan.
                    </div>
                @elseif ($note['correction_notice'] !== null)
                    <div class="text-muted small">{{ $note['correction_notice'] }}</div>
                @else
                    <div class="text-muted small">
                        Fokuskan tindakan dari layer hybrid yang tersedia pada detail note ini. True revision note tetap di luar scope phase sekarang.
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
