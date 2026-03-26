# AI_RULES Changelog

## 2026-03-26
- Membuat struktur awal AI_RULES modular.
- Menambahkan Decision Policy sebagai conflict protocol utama.
- Menambahkan modul core, workflow, output, architecture, domain kasir, dan stack.
- Mengunci domain map, UI terms, payment lifecycle, dan reporting boundary dalam aturan terpisah.

## Aturan update changelog
- Setiap perubahan rule penting harus menambah entri baru.
- Jika perubahan dipicu ADR atau handoff, cantumkan referensinya pada update berikutnya.

## 2026-03-26 - harden entrypoint and core P0
- Menguatkan `00_INDEX.md` menjadi entrypoint enforcement.
- Menguatkan `01_DECISION_POLICY.md` dengan mandatory decision sequence, gap rule, forbidden shortcuts, dan stop conditions.
- Menguatkan `10_SCOPE_AND_FACTS.md` dengan classification dan inference rule.
- Menguatkan `11_BLUEPRINT_FIRST.md` dengan implementation gate.

## 2026-03-26 - harden execution and workflow
- Menguatkan `12_STEP_BY_STEP_EXECUTION.md` dengan validation gate dan forbidden behavior.
- Menguatkan `13_PROOF_AND_PROGRESS.md` dengan accepted proof, progress rule, dan larangan klaim tanpa bukti.
- Menguatkan `20_RESPONSE_STRUCTURE.md` sebagai struktur respons kerja default.
- Menguatkan `21_ACTIVE_STEP_POLICY.md` untuk disiplin satu step aktif.
- Menguatkan `22_OPTION_EVALUATION.md` agar evaluasi opsi selalu kontekstual dan punya plus/minus.
- Menguatkan `23_HANDOFF_POLICY.md` agar penutupan slice bisa diteruskan GPT lain tanpa asumsi.
