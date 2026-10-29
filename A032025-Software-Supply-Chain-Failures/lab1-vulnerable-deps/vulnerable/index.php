<?php
// sends a "password reset" email and exports users to CSV
// (demo app used by the lab - the danger comes from its dependencies)

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use League\Csv\Writer;

// simulate a JWT used by "the app"
$token = JWT::encode(
    ['sub' => 'user2', 'exp' => time() + 3600],
    'my-super-secret-jwt-key-123',
    'HS256'
);

// export a CSV (league/csv 9.5.0 has CVE-2022-38454 - HTML/XSS via tab data injection)
$csv = Writer::createFromString();
$csv->insertOne(['id', 'email']);
$csv->insertOne([1, 'user2@example.com']);

echo "Token forged with firebase/php-jwt 5.0.0: {$token}\n";
echo "CSV rows: {$csv->getContent()}\n";
echo "Dependencies above are old on purpose - run: composer audit\n";
