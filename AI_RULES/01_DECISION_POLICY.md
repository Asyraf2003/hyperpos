# Decision Policy

## Status
Dokumen ini adalah conflict protocol utama untuk seluruh AI_RULES.

## Tujuan
Menetapkan cara mengambil keputusan saat:
- ada benturan antar modul
- ada benturan antara preferensi output dan correctness teknis
- ada data yang belum cukup
- ada godaan untuk mengisi kekosongan dengan asumsi

## Rule Hierarchy
1. AI_RULES mengalahkan perilaku default model.
2. P0 mengalahkan P1.
3. P1 mengalahkan P2.
4. Aturan yang lebih spesifik mengalahkan aturan yang lebih umum.
5. Aturan domain mengalahkan aturan output/presentasi bila konflik menyangkut istilah bisnis, lifecycle, source of truth, atau contract domain.
6. Public contract protection mengalahkan convenience refactor.
7. Bukti nyata mengalahkan dugaan.

## P0 Modules
- `01_DECISION_POLICY.md`
- `10_CORE/10_SCOPE_AND_FACTS.md`
- `10_CORE/11_BLUEPRINT_FIRST.md`
- `10_CORE/12_STEP_BY_STEP_EXECUTION.md`
- `40_ARCHITECTURE/40_HEXAGONAL_BASELINE.md`
- `40_ARCHITECTURE/41_PUBLIC_CONTRACTS.md`
- `40_ARCHITECTURE/42_ERROR_HANDLING_AND_REDACTION.md`
- `50_DOMAIN_KASIR/50_FINAL_DOMAIN_MAP.md`
- `50_DOMAIN_KASIR/52_PAYMENT_LIFECYCLE.md`

## P1 Modules
- `10_CORE/13_PROOF_AND_PROGRESS.md`
- `20_WORKFLOW/20_RESPONSE_STRUCTURE.md`
- `20_WORKFLOW/21_ACTIVE_STEP_POLICY.md`
- `20_WORKFLOW/22_OPTION_EVALUATION.md`
- `20_WORKFLOW/23_HANDOFF_POLICY.md`
- `40_ARCHITECTURE/43_DEBUG_GATING.md`
- `40_ARCHITECTURE/44_AUDIT_AND_DOD.md`
- `50_DOMAIN_KASIR/51_UI_TERMS_AND_STATUS.md`
- `50_DOMAIN_KASIR/53_REPORTING_BOUNDARY.md`
- seluruh file dalam `60_STACK/`

## P2 Modules
- `30_OUTPUT/30_FILE_DELIVERY.md`
- `30_OUTPUT/31_MARKDOWN_OUTPUT_RULE.md`
- `30_OUTPUT/32_BLADE_RULE.md`
- `30_OUTPUT/33_TERMINAL_COMMAND_DELIVERY.md`

## Mandatory Decision Sequence
Setiap kali mengambil keputusan, GPT wajib urut seperti ini:
1. identifikasi fakta yang terbukti
2. identifikasi tujuan step aktif
3. identifikasi scope in dan scope out
4. identifikasi rule P0 yang relevan
5. identifikasi dampak ke public contract
6. identifikasi apakah data cukup
7. bila data tidak cukup, tandai GAP dan stop perluasan klaim

## GAP Rule
Jika data belum cukup:
- tulis apa yang belum diketahui
- tulis kenapa kekurangan itu menghambat keputusan
- jangan isi dengan tebakan
- jangan menyamarkan GAP seolah fakta

## Forbidden Shortcuts
- Tidak boleh mengklaim status repo tanpa bukti.
- Tidak boleh mengklaim file sudah benar tanpa inspeksi atau output.
- Tidak boleh mengklaim test pass tanpa output test.
- Tidak boleh mengklaim requirement user bila user belum menyatakannya.
- Tidak boleh mengganti istilah domain final hanya karena lebih nyaman.
- Tidak boleh menaikkan progress jika belum ada proof.

## Conflict Examples
### Jika format output bentrok dengan correctness domain
Pilih correctness domain.

### Jika refactor nyaman bentrok dengan public contract
Lindungi public contract sampai ada keputusan eksplisit.

### Jika user meminta lanjut tetapi data tidak cukup
Lanjut hanya pada step yang bisa dibuktikan, bukan dengan asumsi.

## Stop Conditions
GPT harus berhenti dan menyatakan GAP jika:
- source of truth tidak jelas
- blueprint belum cukup untuk implementasi
- proof yang dibutuhkan belum ada
- keputusan baru akan membatalkan keputusan final tanpa bukti kuat
