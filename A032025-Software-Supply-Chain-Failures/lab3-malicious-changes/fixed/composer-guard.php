<?php
/**
 * Composer supply-chain guard.
 * Usage: php composer-guard.php [hook|ci]
 * In hook mode it inspects the STAGED diff (git diff --cached),
 * in ci mode it inspects HEAD diff (composer.json + composer.lock).
 * Exit 1 = block the commit/build.
 */

$mode = $argv[1] ?? 'ci';
// ci mode: pass a base ref (or set GUARD_BASE / run on a PR merge-base pipeline)
// e.g. php composer-guard.php ci origin/main
$base = $argv[2] ?? getenv('GUARD_BASE') ?: 'HEAD^';
$diffCmd = $mode === 'hook'
    ? 'git diff --cached -- composer.json composer.lock'
    : "git diff $base -- composer.json composer.lock";
$diff = (string) shell_exec($diffCmd);
if (trim($diff) === '') {
    echo "[guard] no composer.json/composer.lock changes - OK\n";
    exit(0);
}

$failures = [];
$warnings = [];

// 1. Production-critical composer.json keys must never change unnoticed
foreach (['scripts', 'repositories', 'allow-plugins', 'minimum-stability', 'prefer-source'] as $critical) {
    if (preg_match('/^[+-].*"' . $critical . '"/m', $diff)) {
        $failures[] = "composer.json: change to \"$critical\" detected "
            . '(post-install scripts, extra repos and plugin allowlists are the #1 malware carrier)';
    }
}

// 2. Typosquatting heuristic for new package names (vendor AND name)
$known = ['framework','support','collection','laravel','laminas','symfony','guzzle','phpmailer','monolog',
          'firebase','doctrine','phpunit','faker','predis','stripe','carbon','spatie','vlucas','league',
          'mailer','jwt','psr','slack','aws'];
preg_match_all('/^\+(?![+-]).*?"([a-z0-9_\-\.]+)\/([a-z0-9_\-\.]+)"\s*:/m', $diff, $added, PREG_SET_ORDER);
foreach ($added as $pkg) {
    $parts = [$pkg[1], $pkg[2]];
    foreach ($parts as $part) {
        $part = strtolower($part);
        if (in_array($part, $known, true)) { continue; }
        foreach ($known as $genuine) {
            if (levenshtein($part, $genuine) <= 2) {
                $failures[] = "possible typosquat: \"{$pkg[1]}/{$pkg[2]}\" - \"$part\" is only "
                    . levenshtein($part, $genuine) . " edits away from \"$genuine\"";
                break 2;
            }
        }
    }
}

// 3. Lockfile dist URLs must come from trusted https sources
$trustedHosts = ['repo.packagist.org', 'api.github.com', 'github.com'];
preg_match_all('/^\+(?![+-]).*?"url"\s*:\s*"([^"]+)"/m', $diff, $urls);
foreach ($urls[1] as $url) {
    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
    if (str_starts_with($url, 'http://')) {
        $failures[] = "plain HTTP URL added: $url";
    } elseif ($host !== '' && !in_array($host, $trustedHosts, true)) {
        $failures[] = "URL from untrusted host added: $url (extend \$trustedHosts only after review)";
    }
}

// 4. Lockfile drifted without a matching composer.json change (hand-rewrite smell)
$lockChanged  = str_contains($diff, 'diff --git a/composer.lock');
$jsonChanged  = str_contains($diff, 'diff --git a/composer.json');
if ($lockChanged && !$jsonChanged) {
    $warnings[] = 'composer.lock changed but composer.json did not - version drift rewritten by hand';
}

// 5. Plain ssh/git URLs
if (preg_match('/^\+(?![+-]).*"(git@|ssh:)/m', $diff)) {
    $failures[] = 'git ssh URL added - require https (signed dist)';
}

foreach ($failures as $f) { echo "[guard][FAIL] $f\n"; }
foreach ($warnings as $w) { echo "[guard][WARN] $w\n"; }

if ($failures) {
    echo "\n[guard] BLOCKED. Security review required for composer changes.\n";
    exit(1);
}
echo "[guard] no obvious supply-chain tampering found\n";
exit(0);
