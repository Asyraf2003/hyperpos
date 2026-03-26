# P1 - Go Rules

## Tujuan
Menjaga hygiene implementasi Go tetap konsisten dengan constitution project.

## Mandatory Rule
- Satu folder = satu package.
- Jaga ukuran file tetap terkontrol; jika melewati batas internal project, harus ada alasan jelas.
- Patuhi boundary dan import discipline.
- Jangan campur domain, transport, dan persistence tanpa jalur yang sah.

## Forbidden Behavior
- Jangan mencampur package hanya demi kenyamanan sesaat.
- Jangan memakai import yang menabrak boundary yang sudah dikunci.
