<?php
/**
 * LAB 4 — XML parser with entities enabled (Vulnerable)
 *
 * Vulnerable code:
 *   $xml->loadXML($data, LIBXML_NOENT | LIBXML_DTDLOAD);
 *   → external entities + entity expansion enabled (CWE-611, CWE-776).
 *     Attacker XML can read server files or DoS the parser.
 *
 * Fix (see ../fixed/parse_secure.php):
 *   LIBXML_NONET, no NOENT/DTDLOAD, entity loader disabled.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['xml'] ?? '';

    // ========== VULNERABLE: entities resolved, network allowed ==========
    $xml = new DOMDocument();
    $xml->loadXML($data, LIBXML_NOENT | LIBXML_DTDLOAD);
    $items = [];
    foreach ($xml->getElementsByTagName('item') as $node) {
        $items[] = $node->textContent;
    }
    $result = $items;
}
$sample = <<<XML
<order>
  <item>USB-C Hub</item>
  <item>Monitor 27"</item>
</order>
XML;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Order Import (Lab 4)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }
        nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        nav a { color: #94a3b8; text-decoration: none; margin-left: 1rem; }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: #1e293b; border-radius: 12px; padding: 1.5rem; border: 1px solid #334155; }
        textarea { width: 100%; min-height: 160px; background: #0f172a; color: #f1f5f9; border: 1px solid #334155; border-radius: 8px; padding: .75rem; font-family: monospace; }
        button { margin-top: 1rem; padding: .75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; }
        pre { background: #0f172a; padding: 1rem; border-radius: 8px; margin-top: 1rem; white-space: pre-wrap; }
    </style>
</head>
<body>
    <nav><span>Lab 4 — XML Import</span>
        <div><a href="/dashboard.php">Dashboard</a><a href="/logout.php">Logout</a></div>
    </nav>
    <div class="container"><div class="card">
        <h1>Bulk order import</h1>
        <p style="color:#94a3b8;margin:.5rem 0 1rem">Welcome, <?= htmlspecialchars($user['username']) ?>. Paste supplier XML:</p>
        <form method="POST">
            <textarea name="xml"><?= htmlspecialchars($_POST['xml'] ?? $sample) ?></textarea>
            <button type="submit">Import</button>
        </form>
        <?php if ($result !== null): ?>
            <pre>Imported <?= count($result) ?> item(s):&#10;<?= htmlspecialchars(implode("&#10;", $result)) ?></pre>
        <?php endif; ?>
    </div></div>
</body>
</html>
