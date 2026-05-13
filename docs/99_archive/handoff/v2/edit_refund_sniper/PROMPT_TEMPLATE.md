# Prompt Template - Edit Refund Sniper Session

## Usage

Copy this into a new AI session when continuing the edit/refund/revision chain.

Keep this prompt stable across sessions.

## Prompt

    Kita lanjut HyperPOS untuk rantai edit/refund/revision.

    Mulai dari sniper handoff. Jangan analisis repo umum dulu.

    Baca berurutan:
    1. docs/01_standards/0001_index.md
    2. docs/01_standards/0002_decision_policy.md
    3. docs/99_archive/handoff/v2/edit_refund_sniper/README.md
    4. docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
    5. docs/99_archive/handoff/v2/edit_refund_sniper/PROMPT_TEMPLATE.md
    6. docs/99_archive/handoff/v2/edit_refund_sniper/HANDOFF_TEMPLATE.md
    7. latest handoff yang ditunjuk README

    Baseline kerja:
    - Saya biasanya commit/push setiap aksi.
    - Jangan minta git status/log/diff sebagai ritual awal kalau saya sudah kasih commit/push proof.
    - Pakai commit/push output saya sebagai baseline proof.
    - Minta git check hanya kalau benar-benar dibutuhkan untuk dirty state, changed-file inventory, source/docs conflict, test failure, atau final closure proof.

    Jangan create/edit/delete production code dulu.

    Tugas awal:
    - validasi handoff dan source priority
    - baca dokumen canonical yang dipointer dari README/handoff
    - audit source spesifik untuk active slice edit/refund/revision
    - rekomendasi active slice paling aman
    - stop di blueprint/source audit dulu sebelum implementasi

    Response wajib pakai:
    FACT
    GAP
    ASSUMPTION
    DECISION
    ACTIVE STEP
    FILES TO TOUCH
    FILES NOT TO TOUCH
    COMMAND
    EXPECTED PROOF
    NEXT

    Aturan:
    - local command output dan push proof dari saya menang
    - no silent assumption
    - no code edit sebelum source audit dan decision lock
    - UI bukan financial truth
    - no ledger/history rewrite
    - file app lebih dari 100 lines harus dipecah rapi, bukan dipadatkan
    - make verify wajib sebelum klaim final safe state
    - saya handle commit/push manual
    - akhir sesi wajib update handoff chain kalau ada keputusan, proof, file changed, gap, atau next active step baru
