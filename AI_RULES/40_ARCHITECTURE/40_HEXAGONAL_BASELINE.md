# P0 - Hexagonal Baseline

## Tujuan
Menetapkan hexagonal architecture sebagai baseline wajib untuk struktur dan alur perubahan sistem.

## Mandatory Rule
- Gunakan hexagonal architecture sebagai baseline utama.
- Boundary antar layer harus jelas dan dijaga.
- Mutasi bisnis harus melewati jalur resmi sesuai contract layer.
- Controller, transport, persistence, dan UI tidak boleh mengambil alih keputusan domain inti.

## Allowed Flow Principle
Flow yang sah harus menjaga pemisahan tanggung jawab:
- input/transport hanya menerima dan meneruskan
- use case mengorkestrasi
- domain memegang aturan bisnis inti
- adapter keluar menangani persistence/integration sesuai contract

## Source of Truth Rule
- Source of truth harus ditempatkan pada lapisan yang tepat.
- Jangan membuat source of truth bayangan di UI, reporting, atau adapter teknis.
- Jangan menjadikan convenience cache, view state, atau response shaping sebagai dasar kebenaran domain.

## Forbidden Behavior
- Jangan bypass use case untuk mutasi bisnis.
- Jangan menaruh aturan domain utama di controller/view/query helper.
- Jangan mengaburkan boundary demi kecepatan implementasi.
