<?php
// same demo app, now running on patched + maintained dependencies

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use League\Csv\Writer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

// JWT on a supported, patched major
$token = JWT::encode(
    ['sub' => 'user2', 'exp' => time() + 3600],
    getenv('JWT_SECRET') ?: 'dev-only-secret-with-32-bytes-minimum!',
    'HS256'
);

$csv = Writer::createFromString();
$csv->insertOne(['id', 'email']);
$csv->insertOne([1, 'user2@example.com']);

// SMTP sending via symfony/mailer (replacement for the abandoned swiftmailer)
$mailer = new Mailer(Transport::fromDsn(getenv('MAILER_DSN') ?: 'null://null'));
$mailer->send(
    (new Email())
        ->from('no-reply@example.com')
        ->to('user2@example.com')
        ->subject('Password reset')
        ->text("Reset link. Token: {$token}")
);

echo "CSV rows: {$csv->getContent()}\n";
echo "Email queued via symfony/mailer\n";
echo "Verify: composer audit  (should report 0 advisories)\n";
