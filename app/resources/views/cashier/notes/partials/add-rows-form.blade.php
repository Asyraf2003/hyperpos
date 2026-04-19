<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-1">Aksi Workspace</h5>

        @if (($note['can_edit_workspace'] ?? false) && $note['can_add_rows'])
            <div class="ui-form-actions">
                <a
                    href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id']]) }}"
                    class="btn btn-light-primary"
                >
                    Edit Workspace
                </a>
            </div>
        @else
            <div class="border rounded p-3 bg-light">
                <div class="fw-semibold mb-1">Workspace tidak aktif</div>

                @if ($note['is_closed'])
                    <div class="text-muted small">
                        Workspace tidak dipakai untuk nota yang sudah close. Gunakan aksi line dan alur refund sesuai status line.
                    </div>
                @elseif ($note['correction_notice'] !== null)
                    <div class="text-muted small">{{ $note['correction_notice'] }}</div>
                @else
                    <div class="text-muted small">
                        Fokuskan tindakan dari daftar line. Workspace hanya dipakai saat perubahan besar memang masih diperbolehkan.
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
