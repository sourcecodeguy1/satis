<?php

namespace Juliowebmaster\Satis\Tests;

use Juliowebmaster\Satis\WebhookHandler;
use PHPUnit\Framework\TestCase;

class WebhookHandlerTest extends TestCase
{
    private const SECRET = 'test-secret';

    private function sign(string $payload): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, self::SECRET);
    }

    public function test_valid_push_triggers_build(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $payload  = '{"ref":"refs/tags/v1.0.0"}';
        $response = $handler->handle($payload, $this->sign($payload), 'push');

        $this->assertSame(200, $response->status);
        $this->assertSame('Build triggered', $response->body['message']);
    }

    public function test_invalid_signature_returns_403(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $response = $handler->handle('payload', 'sha256=badsignature', 'push');

        $this->assertSame(403, $response->status);
        $this->assertArrayHasKey('error', $response->body);
    }

    public function test_empty_signature_returns_403(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $response = $handler->handle('payload', '', 'push');

        $this->assertSame(403, $response->status);
    }

    public function test_empty_secret_returns_403(): void
    {
        $handler  = new WebhookHandler('');
        $payload  = 'payload';
        $response = $handler->handle($payload, $this->sign($payload), 'push');

        $this->assertSame(403, $response->status);
    }

    public function test_unhandled_event_is_ignored(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $payload  = '{}';
        $response = $handler->handle($payload, $this->sign($payload), 'star');

        $this->assertSame(200, $response->status);
        $this->assertStringContainsString('ignored', $response->body['message']);
    }

    public function test_release_event_triggers_build(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $payload  = '{"action":"published"}';
        $response = $handler->handle($payload, $this->sign($payload), 'release');

        $this->assertSame(200, $response->status);
        $this->assertSame('Build triggered', $response->body['message']);
    }

    public function test_create_event_triggers_build(): void
    {
        $handler  = new WebhookHandler(self::SECRET);
        $payload  = '{"ref_type":"tag"}';
        $response = $handler->handle($payload, $this->sign($payload), 'create');

        $this->assertSame(200, $response->status);
        $this->assertSame('Build triggered', $response->body['message']);
    }
}
