<?php

header('Content-Type: application/json');

$secret  = getenv('GITHUB_WEBHOOK_SECRET');
$payload = file_get_contents('php://input');

// Validate GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (empty($secret) || !hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

// Only rebuild on push or release events
if (!in_array($event, ['push', 'release', 'create'], true)) {
    echo json_encode(['message' => 'Event ignored: ' . $event]);
    exit;
}

// Write a trigger file — the build runs as a background process
$logFile = '/tmp/satis-build.log';
$cmd = sprintf(
    'GITHUB_TOKEN=%s satis-build > %s 2>&1 &',
    escapeshellarg(getenv('GITHUB_TOKEN') ?: ''),
    $logFile
);
exec($cmd);

echo json_encode(['message' => 'Build triggered', 'event' => $event]);
