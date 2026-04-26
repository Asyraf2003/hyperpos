# Session Start Protocol

## Tujuan
Menstandarkan cara GPT memulai sesi kerja baru agar tidak langsung melompat ke asumsi atau implementasi.

## Mandatory Opening Flow
Pada awal sesi kerja, GPT harus:
1. mengidentifikasi fakta yang tersedia
2. mengidentifikasi tujuan user
3. mengidentifikasi scope in dan scope out
4. memetakan rules yang mengikat
5. menyusun blueprint singkat
6. menyatakan step aktif
7. menyebut proof yang tersedia atau yang dibutuhkan

## Jika konteks belum cukup
- Tandai GAP secara eksplisit
- Jangan berpura-pura konteks sudah cukup
- Jangan menulis implementasi spekulatif

## Jika user meminta lanjut
- lanjut hanya ke step berikut yang sah menurut workflow
- jangan membuka dua step aktif sekaligus
- jangan melewati validation gate


## Session Capacity Baseline

At the start of a new technical work session, GPT must initialize an operational capacity estimate.

A new page does not mean perfect 100% capability. Use the latest handoff, active repo facts, and current task complexity to estimate:

~~~text
Kapasitas sesi:
- Kemampuan menalar: xx%
- Jendela konteks: xx%
- Kemampuan sisa: xx%
- Status: aman / mulai rawan / ganti halaman baru
~~~

For a clean new page with a reliable handoff, the usual starting range is:

~~~text
Kapasitas sesi:
- Kemampuan menalar: 92-95%
- Jendela konteks: 95-98%
- Kemampuan sisa: 92-95%
- Status: aman
~~~

These are operational risk estimates, not exact internal telemetry.

## Minimal session reminder
GPT harus ingat:
- blueprint dulu
- satu step aktif
- proof-based progress
- no assumption
