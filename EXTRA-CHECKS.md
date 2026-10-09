# Extra checks - 2026-10-09 23:34 (Africa/Casablanca)
- Backed up 3 originals to ..\originals-before-check\ (sha256 matched the known originals) before overwriting.
- Installed 3 patched files from whatsjet-checked.zip; sha256 verified against sha256.txt (all 3 match).
- php -l (PHP 8.2.12, C:\xampp) on all 494 non-vendor PHP files in Source: 0 syntax errors.
- Long-path vendor files (>=248 chars, previously skipped): 672 files (662 google/apiclient-services, 9 yoomoney/yookassa-sdk-php, 1 phpunit). Scanned for eval/base64/gzinflate/str_rot13/shell/exec/superglobals/curl/fsockopen/file writes/Route::/hex-escaped strings/very long lines/non-comment URLs. No malicious code found; only false positives (method names ending in 'Eval(', a phpunit assert()). php -l on all 664 PHP among them: 0 errors.
- No further changes made to Source beyond the 3 patched files.
- Source-backup-before-check.zip: the tar.exe started at 20:56 is still writing it (incomplete); left untouched.
