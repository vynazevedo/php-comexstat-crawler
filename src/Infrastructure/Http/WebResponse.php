<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

class WebResponse
{
    public function __construct(
        public readonly int $status,
        public readonly string $html,
        public readonly array $headers
    ) {}
}
