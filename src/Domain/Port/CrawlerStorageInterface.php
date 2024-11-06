<?php
declare(strict_types=1);

namespace App\Domain\Port;

use Throwable;

interface CrawlerStorageInterface
{
    /**
     * @param string $url
     * @param string $html
     * @return void
     */
    public function saveHtml(string $url, string $html): void;

    /**
     * @param string $url
     * @param array $data
     * @return void
     */
    public function saveData(string $url, array $data): void;

    /**
     * @param Throwable $error
     * @param string $context
     * @return void
     */
    public function logError(Throwable $error, string $context): void;
}
