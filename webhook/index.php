<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Juliowebmaster\Satis\WebhookHandler;

header('Content-Type: application/json');

$handler  = new WebhookHandler(getenv('GITHUB_WEBHOOK_SECRET') ?: '');
$payload  = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$event    = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

$response = $handler->handle($payload, $signature, $event);

http_response_code($response->status);

if ($response->status === 200 && $response->body['message'] === 'Build triggered') {
    $token  = escapeshellarg(getenv('GITHUB_TOKEN') ?: '');
    exec("GITHUB_TOKEN={$token} satis-build > /tmp/satis-build.log 2>&1 &");
}

echo $response->toJson();
