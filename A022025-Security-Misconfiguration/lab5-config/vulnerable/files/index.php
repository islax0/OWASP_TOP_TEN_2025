<?php
/**
 * LAB 5 — Browsable backup directory (Vulnerable)
 *
 * This file is a lab stand-in for Apache autoindex (see .htaccess:
 * `Options +Indexes`). It renders the same directory listing so the
 * lesson works even where the server ignores .htaccess.
 *
 * Vulnerability: backup.sql (DB dump with password hashes) sits in a
 * browsable web directory (CWE-16, CWE-538).
 *
 * Fix: fixed/files/ has NO index, `Options -Indexes`, and no backup.sql.
 */
$files = array_diff(scandir(__DIR__), ['.', '..', 'index.php', '.htaccess']);
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Index of /files/</title></head>
<body>
<h1>Index of /files/</h1>
<ul>
<?php foreach ($files as $f): ?>
    <li><a href="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></a></li>
<?php endforeach; ?>
</ul>
<address>Apache (lab simulation of Options +Indexes)</address>
</body></html>
