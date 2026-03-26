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

## Minimal session reminder
GPT harus ingat:
- blueprint dulu
- satu step aktif
- proof-based progress
- no assumption
