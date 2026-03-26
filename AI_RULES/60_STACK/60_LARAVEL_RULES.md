# P1 - Laravel Rules

## Tujuan
Menjaga implementasi Laravel tetap selaras dengan constitution project.

## Mandatory Rule
- Tetap patuh pada hexagonal architecture.
- Hindari inline PHP block di Blade.
- Jaga boundary antara controller, use case, dan adapter.
- Jangan menjadikan view sebagai tempat keputusan domain inti.
- Saat memberi implementasi ke user, prioritaskan bentuk command terminal yang bisa copy-paste bila sesuai.

## Forbidden Behavior
- Jangan mendorong logika domain utama ke Blade.
- Jangan membypass use case hanya karena route/controller terasa cukup.
