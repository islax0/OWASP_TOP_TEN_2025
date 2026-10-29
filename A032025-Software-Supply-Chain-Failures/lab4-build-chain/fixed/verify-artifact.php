<?php
/**
 * Verify a release bundle against sha256sums.txt (+ optional signed checksums).
 * sha256sums.txt format: "<hex>  <relative/path>".
 * If checksums.sig exists, verification happens with cosign verify-blob.
 */
$sums = 'sha256sums.txt';
if (!is_file($sums)) { fwrite(STDERR, "missing $sums\n"); exit(1); }

$lines = file($sums, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    [$expected, $path] = preg_split('/\s+/', trim($line), 2);
    $path = trim($path, '*');
    if (!is_file($path)) { fwrite(STDERR, "FAIL missing artifact: $path\n"); exit(1); }
    $actual = hash_file('sha256', $path);
    echo hash_equals($expected, $actual) ? "[OK]   $path\n" : "[FAIL] $path\n";
    if (!hash_equals($expected, $actual)) exit(1);
}

if (is_file('checksums.sig')) {
    shell_exec('cosign verify-blob --key release.pub --signature checksums.sig ' . $sums . ' || exit 1');
    echo "[OK]   signatures valid\n";
}
echo "artifact integrity verified\n";
