# 033 - Web and mobile login endpoints lack explicit rate limiting

Status: Reported
Keparahan: Medium
Klasifikasi: authentication hardening / brute-force resistance

## Ringkasan

Web login dan mobile API login tidak terlihat memiliki throttle middleware atau rate limiter eksplisit.

Invalid credential handling sudah mengembalikan pesan aman, tetapi tidak ada proof bahwa percobaan login berulang akan dibatasi dengan response `429` atau lockout window.

Ini membuka risiko brute force dan credential stuffing terhadap endpoint login.

## Bukti awal

Route web auth:

`routes/web/auth.php`

- Route group memakai middleware `web` dan `app.shell`.
- `POST /login` diarahkan ke `AuthenticateController`.
- Tidak ada `throttle` middleware pada route login.

Route mobile API auth:

`routes/api.php`

- `POST /api/v1/auth/login` diarahkan ke `LoginMobileApiController`.
- Route ini berada di luar middleware `mobile.api.auth`.
- Tidak ada `throttle` middleware pada route login.

Web login controller:

`app/Adapters/In/Http/Controllers/Auth/AuthenticateController.php`

- memakai `Auth::attempt(...)`;
- jika gagal, langsung redirect back dengan error;
- tidak ada `RateLimiter`, `tooManyAttempts`, atau lockout handling.

Mobile login controller dan handler:

`app/Adapters/In/Http/Controllers/Api/V1/Auth/LoginMobileApiController.php`

`app/Application/MobileApi/Auth/UseCases/LoginMobileApiUserHandler.php`

- handler memanggil credential verifier;
- jika gagal, return invalid credentials;
- tidak ada rate limiter.

Search lokal:

`rg -n "Throttle|throttle|RateLimiter|tooManyAttempts|Limit::|login" app routes tests config bootstrap`

Output relevan hanya menemukan password reset throttle config, route login, controller login, dan test invalid login. Tidak ditemukan implementasi login throttling pada auth flow.

Route list:

`php artisan route:list --except-vendor | rg 'login|auth/login'`

Output menampilkan:

- `POST api/v1/auth/login`
- `GET|HEAD login`
- `POST login`

Tidak ada proof middleware throttle dari source route.

## Jalur rentan

Attacker mengirim banyak request login dengan kombinasi email/password ke web login atau mobile API login -> aplikasi memproses setiap attempt tanpa counter rate-limit yang terlihat -> attacker dapat melakukan brute force atau credential stuffing sampai dibatasi oleh kontrol eksternal.

## Dampak

- Meningkatkan peluang credential stuffing.
- Meningkatkan beban aplikasi dan password hashing workload.
- Membuat audit security auth belum lengkap meskipun pesan invalid credential sudah aman.

Keparahan Medium karena issue ini tidak membuktikan bypass autentikasi, tetapi menghilangkan kontrol dasar pada boundary login.

## Kontrol yang sudah ada

- Invalid login web tidak membocorkan detail selain pesan umum.
- Invalid mobile API login mengembalikan safe payload menurut test yang ada.
- Session regeneration dilakukan setelah web login berhasil.
- Mobile API memakai token setelah login berhasil.

Kontrol tersebut tidak menggantikan rate limiting.

## Remediasi yang disarankan

Candidate patch direction:

- Tambahkan throttle untuk `POST /login` dan `POST /api/v1/auth/login`.
- Gunakan key kombinasi email normalized dan IP address.
- Pertahankan pesan error umum untuk invalid credentials.
- Tambahkan tests yang membuktikan attempt berulang menghasilkan `429`.
- Pastikan successful login membersihkan counter untuk key yang relevan bila menggunakan manual `RateLimiter`.

## Keputusan owner yang mungkin dibutuhkan

- Limit web login dan mobile login disamakan atau dibedakan.
- Lockout window yang diterima operasional, misalnya `5 attempts / minute` atau `10 attempts / 5 minutes`.
- Apakah perlu IP-only fallback saat email kosong/invalid.

## Verification gap

Belum ada patch.

Belum ada test web login throttle.

Belum ada test mobile API login throttle.
