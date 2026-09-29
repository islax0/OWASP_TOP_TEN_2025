# Lab 5 — Force Browsing on Uploaded Files

**Goal:** Access uploaded files without authentication by guessing or enumerating filenames.

## Files

- Vulnerable: `/lab5-force-browsing/vulnerable/upload.php`
- Secure: `/lab5-force-browsing/fixed/upload_secure.php`
- File serving: `/lab5-force-browsing/fixed/serve_file.php`

## Attack

1. Login as any user
2. Upload a file via the vulnerable upload form
3. Access it directly via `/uploads/[filename]`
4. Try accessing other users' files by guessing filenames

## Vulnerability

The upload directory is publicly accessible without authentication, allowing anyone to:
- Directly access uploaded files via URL
- Enumerate and access other users' files
- Potentially access sensitive documents

## Fix

- Store uploads in protected directory with `.htaccess` (Deny from all)
- Serve files through authenticated PHP script
- Verify file ownership before serving
- Prevent directory traversal attacks
- Use unpredictable filenames

## Secure Implementation

The `upload_secure.php` demonstrates a secure approach:
- Files stored in protected directory
- Served through authenticated script with ownership verification
- Uses `.htaccess` to prevent direct access
