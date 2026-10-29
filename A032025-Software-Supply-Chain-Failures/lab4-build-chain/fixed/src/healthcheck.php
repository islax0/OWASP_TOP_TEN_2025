<?php
// tiny demo app used by the Dockerfiles (src/ copied into the image)
require_once __DIR__ . '/../vendor/autoload.php';
http_response_code(200);
echo json_encode([
    'app' => 'invoice-app',
    'php' => PHP_VERSION,
    'status' => 'ok',
]) . "\n";
