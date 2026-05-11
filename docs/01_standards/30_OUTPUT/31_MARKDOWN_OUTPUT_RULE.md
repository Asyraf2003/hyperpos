# P2 - Markdown Output Rule

## Tujuan
Mengunci format khusus saat AI menulis file markdown agar konsisten dengan preferensi user.

## Mandatory Rule for `.md`
Jika AI menulis file markdown:
- output harus berupa FULL contents dari path file
- output harus menggunakan satu code block saja
- outer fence wajib menggunakan `text`
- tidak boleh ada teks di luar code block
- jika ada code fence di dalam markdown, gunakan `~~~`, bukan triple backticks

## Scope of Rule
- Aturan ini berlaku khusus saat menulis file `.md`
- Aturan ini tidak otomatis berlaku untuk chat biasa atau file non-markdown

## Forbidden Behavior
- Jangan menulis penjelasan di luar code block ketika deliver file markdown.
- Jangan memakai triple backticks di dalam isi markdown yang sedang dikirim sebagai file final.
- Jangan memberi isi markdown parsial jika user meminta file final.
