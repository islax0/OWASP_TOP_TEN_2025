<?php
// tiny "production app" whose inventory we want to track

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(sys_get_temp_dir() . '/lab2.log', Logger::INFO));

$mailer = new Mailer(Transport::fromDsn('null://null'));
$mailer->send(
    (new Email())
        ->from('no-reply@example.com')
        ->to('user2@example.com')
        ->subject('SBOM lab')
        ->text('sent from symfony/mailer')
);

$log->info('email queued');

echo "Sent + logged. Now answer: how many components did this tiny app pull in?\n";
echo "Run: composer show --direct  |  composer show -t  |  composer cyclonedx:make-sbom\n";
