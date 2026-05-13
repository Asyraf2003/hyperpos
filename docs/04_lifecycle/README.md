# 04_lifecycle

Runtime records — rekam jejak operasional sistem yang terus bertambah.

## Subfolder

| Folder | Isi |
|---|---|
| `error_log/` | 29 bug dan security findings individual. Tiap issue satu file. |
| `handoff/` | Session recovery notes sesi aktif. Naming: `NNNN_topic_handoff.md`. |

## Aturan

- `error_log/` tidak boleh dihapus atau diubah statusnya tanpa proof dan owner acceptance.
- `handoff/` adalah untuk sesi terbaru saja — setelah selesai, pindah ke `docs/99_archive/handoff/`.
