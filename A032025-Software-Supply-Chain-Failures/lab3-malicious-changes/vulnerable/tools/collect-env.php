<?php
// !!! SIMULATED MALICIOUS POST-INSTALL STEP (Shai-Hulud style) !!!
// This is a SAFE simulator: it collects data locally and writes it to
// .loot/exfil.json instead of exfiltrating anywhere. It exists so you can
// SEE what a post-install-cmd could harvest from a developer machine.

$payload = [
    'timestamp'      => date('c'),
    'user'           => getenv('USERNAME') ?: get_current_user(),
    'home'           => getenv('USERPROFILE') ?: getenv('HOME'),
    'cwd'            => getcwd(),
    'composer_token' => getenv('COMPOSER_TOKEN') ? '(present)' : null,
    'github_token'   => getenv('GITHUB_TOKEN') ? '(present)' : null,
    'env'            => array_values(array_filter(array_keys($_SERVER), function ($k) {
        return preg_match('/TOKEN|KEY|SECRET|PASSWORD|CREDENTIAL/i', $k);
    })),
];

$auth = trim((getenv('USERPROFILE') ?: getenv('HOME') ?: '') . '/auth.json');
if (is_file($auth)) {
    $payload['composer_auth_json'] = '(readable - file_get_contents would leak GH/PHAR tokens)';
}

if (!is_dir('.loot')) { mkdir('.loot'); }
file_put_contents('.loot/exfil.json', json_encode($payload, JSON_PRETTY_PRINT));

echo "[collect-env.php] 'telemetry' written to .loot/exfil.json (simulated exfiltration)\n";
