<?php
declare(strict_types=1);

namespace App\Domain\Port;

use App\Infrastructure\Http\WebResponse;

interface WebClientInterface
{
    /**
     * @param string $url
     * @return WebResponse
     */
    public function fetch(string $url): WebResponse;
}
