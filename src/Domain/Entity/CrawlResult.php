<?php
declare(strict_types=1);

namespace App\Domain\Entity;

readonly class CrawlResult
{
    public function __construct(
        public string $url,
        public int    $status,
        public array  $data,
        public string $timestamp
    ) {}
}
