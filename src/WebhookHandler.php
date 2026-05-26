<?php

namespace Juliowebmaster\Satis;

class WebhookHandler
{
    private const HANDLED_EVENTS = ['push', 'release', 'create'];

    public function __construct(
        private readonly string $secret
    ) {}

    public function handle(string $payload, string $signature, string $event): WebhookResponse
    {
        if (!$this->isValidSignature($payload, $signature)) {
            return new WebhookResponse(403, ['error' => 'Invalid signature']);
        }

        if (!in_array($event, self::HANDLED_EVENTS, true)) {
            return new WebhookResponse(200, ['message' => 'Event ignored: ' . $event]);
        }

        return new WebhookResponse(200, ['message' => 'Build triggered', 'event' => $event]);
    }

    private function isValidSignature(string $payload, string $signature): bool
    {
        if (empty($this->secret) || empty($signature)) {
            return false;
        }

        return hash_equals('sha256=' . hash_hmac('sha256', $payload, $this->secret), $signature);
    }
}
