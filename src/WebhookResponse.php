<?php

namespace Juliowebmaster\Satis;

class WebhookResponse
{
    public function __construct(
        public readonly int   $status,
        public readonly array $body
    ) {}

    public function toJson(): string
    {
        return json_encode($this->body);
    }
}
