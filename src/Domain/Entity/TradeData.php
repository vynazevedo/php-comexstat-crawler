<?php
declare(strict_types=1);

namespace App\Domain\Entity;

readonly class TradeData
{
    public function __construct(
        public array  $exports,
        public array  $imports,
        public string $timestamp
    ) {}
}
