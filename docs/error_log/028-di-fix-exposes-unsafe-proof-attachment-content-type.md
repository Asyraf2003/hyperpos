# 028 - DI fix exposes unsafe proof attachment content type

Status: reported
Severity: Medium
Classification: new unique error-log file
Introduced commit: c1baf1f
Patch status: not provided in this report

## Summary

A security issue became exploitable when the DI binding for `SupplierPaymentProofFileStoragePort` was corrected, making the supplier payment proof upload and attachment-serving path reachable.

The refactor around `storedFile()` and `deleteMany()` was behavior-preserving. The security-relevant change is that the corrected binding activates a path that persists client-controlled upload MIME metadata and later uses it as the HTTP response `Content-Type` for inline attachment responses.

The upload controller validates allowed file types with Laravel file rules, but it stores `UploadedFile::getClientMimeType()`, which comes from multipart request metadata and can be controlled by the uploader. The serve controller later returns the stored file inline and sets `Content-Type` from that stored value.

An authenticated admin can upload a payload whose bytes pass allowed PDF/JPG/PNG validation while the multipart `Content-Type` is supplied as `text/html`. When another admin opens the proof attachment link, the application can serve the attachment inline as HTML from the HyperPOS origin, enabling stored XSS in the victim admin session.

The original filename is also concatenated into `Content-Disposition` without safe header construction.

## Why this is new

This is not the same issue as the existing private storage/public helper exposure report. The storage remains private, and the exploit path is the authenticated application attachment-serving controller.

This is also not the same issue as prior reflected XSS findings. The sink here is stored attachment metadata plus inline same-origin file serving.

## Affected files

- `app/Providers/HexagonalServiceProvider.php`
- `routes/web/admin_procurement.php`
- `app/Adapters/In/Http/Controllers/Admin/Procurement/AttachSupplierPaymentProofController.php`
- `app/Adapters/In/Http/Controllers/Admin/Procurement/ServeSupplierPaymentProofAttachmentController.php`
- `app/Adapters/Out/Procurement/LaravelSupplierPaymentProofFileStorageAdapter.php`

## Evidence

`HexagonalServiceProvider` binds `SupplierPaymentProofFileStoragePort` to `LaravelSupplierPaymentProofFileStorageAdapter`, making the supplier payment proof file storage path resolvable.

`routes/web/admin_procurement.php` exposes the affected upload and attachment serve routes under `web`, `auth`, and `admin.page` middleware.

`AttachSupplierPaymentProofController` validates uploaded proof files as `jpg`, `jpeg`, `png`, or `pdf`, but stores `getClientOriginalName()` and `getClientMimeType()` into the uploaded file metadata.

`LaravelSupplierPaymentProofFileStorageAdapter::storedFile()` preserves `original_filename` and `mime_type` from the provided file metadata without server-side MIME recomputation or allowlisting.

`ServeSupplierPaymentProofAttachmentController` serves the attachment inline by default and sets:

- `Content-Type` from `$attachment->mimeType()`
- `Content-Disposition` by concatenating the original filename into a header string

No repository-level `X-Content-Type-Options: nosniff` or CSP control was reported for this response.

## Attack path

Authenticated admin uploads supplier payment proof file -> upload validation accepts bytes as allowed PDF/JPG/PNG -> controller stores attacker-controlled multipart MIME metadata -> storage adapter persists MIME unchanged -> victim admin opens proof attachment route -> serve controller returns inline response with stored `Content-Type` -> browser treats response as same-origin HTML/script -> stored XSS executes in victim admin session.

## Impact

Successful exploitation can execute JavaScript from the HyperPOS application origin in another admin browser. That script can read same-origin admin pages or API responses available to the victim and submit same-origin state-changing requests as that victim.

Severity is medium because the route is admin-authenticated and exploitation requires an authenticated admin attacker plus victim admin interaction. It does not show unauthenticated compromise, RCE, broad deployment compromise, or cross-tenant impact.

## Preconditions

- Attacker has an authenticated admin account.
- Attacker can upload supplier payment proof files.
- Uploaded bytes pass allowed PDF/JPG/PNG validation.
- Attacker controls multipart `Content-Type`, such as `text/html`.
- Victim admin opens the attachment route without `download=true`.
- No external server-level headers override the unsafe response behavior.

## Controls present

- Routes are protected by `web`, `auth`, and `admin.page`.
- Laravel CSRF protection is expected for web POST routes.
- Upload validation limits files to `jpg`, `jpeg`, `png`, and `pdf`.
- Upload validation limits proof files to max 3 files and max 2048 KB each.
- Files are stored on a private Laravel disk rather than directly under public storage.
- Laravel session cookies are expected to be HttpOnly and SameSite by framework defaults.

## Controls missing

- MIME type is not recomputed server-side before persistence.
- Stored MIME type is not restricted to a safe allowlist before serving.
- Inline serving is allowed by default.
- `X-Content-Type-Options: nosniff` is not set by the attachment response.
- `Content-Disposition` is manually concatenated instead of generated through safe framework helpers.
- Original filename is not safely escaped for header construction in the shown response path.

## Recommended fix

Store a server-derived MIME type instead of `getClientMimeType()`.

Allowlist served MIME values to safe values such as:

- `application/pdf`
- `image/jpeg`
- `image/png`

For unknown or mismatched files, serve as `application/octet-stream` and force download.

Set `X-Content-Type-Options: nosniff` on attachment responses.

Use Symfony/Laravel response helpers for safe `Content-Disposition` generation instead of manual header concatenation.

Consider forcing download for proof attachments unless inline preview is explicitly required and safely isolated.

## Verification gap

This session has not independently verified the local repository diff or runtime behavior. Treat this entry as report-derived until `git status --short`, `git diff`, and relevant test output are provided.

The report states that validation demonstrated a PDF-looking payload stored with `text/html` and served inline as `text/html`, but no local test output has been provided in this session.

