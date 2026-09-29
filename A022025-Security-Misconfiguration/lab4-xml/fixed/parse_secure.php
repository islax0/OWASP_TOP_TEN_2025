<?php
/**
 * LAB 4 — XML parser (SECURE VERSION)
 *
 * Fixed code:
 *   libxml_disable_entity_loader(true); // PHP < 8
 *   $xml->loadXML($data, LIBXML_NONET);
 *   → no external entities, no network, no entity substitution.
 */
require_once __DIR__ . '/../../../auth.php';
requireLogin();
$user = currentUser();

if (function_exists('libxml_disable_entity_loader')) {
    libxml_disable_entity_loader(true); // no-op on PHP 8+, needed below
}

$result = null;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['xml'] ?? '';

    // ========== SECURE: DTDs rejected, entities off, network off ==========
    // Internal entities are legal XML, so merely dropping NOENT is not
    // enough — a DTD with recursive entities still expands. This importer
    // needs no DTD at all, so refuse any document that declares one.
    if (preg_match('/<!DOCTYPE/i', $data)) {
        $error = 'DTDs are not allowed.';
    } else {
        $prev = libxml_use_internal_errors(true);
        $xml = new DOMDocument();
        // No LIBXML_NOENT (no substitution), no LIBXML_DTDLOAD, LIBXML_NONET (no network)
        $ok = $xml->loadXML($data, LIBXML_NONET);
        libxml_use_internal_errors($prev);
        if (!$ok) {
            $error = 'Invalid XML rejected.';
        } else {
            $items = [];
            foreach ($xml->getElementsByTagName('item') as $node) {
                $items[] = $node->textContent;
            }
            $result = $items;
        }
    }
}
$sample = <<<XML
<order>
  <item>USB-C Hub</item>
</order>
XML;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Order Import (Secure)</title>
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
        .ok { background: #14532d; color: #bbf7d0; padding: .75rem 1rem; border-radius: 8px; margin-top: 1.25rem; font-size: .875rem; }
        .err { background: #7f1d1d; color: #fecaca; padding: .75rem 1rem; border-radius: 8px; margin-top: 1.25rem; font-size: .875rem; }
    </style>
</head>
<body>
    <nav><span>Lab 4 — XML Import (SECURE)</span>
        <div><a href="/dashboard.php">Dashboard</a><a href="/logout.php">Logout</a></div>
    </nav>
    <div class="container"><div class="card">
        <h1>Bulk order import</h1>
        <p style="color:#94a3b8;margin:.5rem 0 1rem">Welcome, <?= htmlspecialchars($user['username']) ?>. Paste supplier XML:</p>
        <form method="POST">
            <textarea name="xml"><?= htmlspecialchars($_POST['xml'] ?? $sample) ?></textarea>
            <button type="submit">Import</button>
        </form>
        <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($result !== null): ?>
            <pre>Imported <?= count($result) ?> item(s):&#10;<?= htmlspecialchars(implode("&#10;", $result)) ?></pre>
        <?php endif; ?>
        <div class="ok">FIXED: external entities and network access disabled in the parser.</div>
    </div></div>
</body>
</html>
