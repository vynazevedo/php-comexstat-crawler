<?php
declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Port\CrawlerStorageInterface;
use Throwable;

class FileSystemStorage implements CrawlerStorageInterface
{
    private string $outputDir;

    /**
     * @param string $baseDir
     */
    public function __construct(string $baseDir = __DIR__ . '/../../data')
    {
        $this->outputDir = $baseDir . '/' . date('Y-m-d_H-i-s');
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    /**
     * @param string $url
     * @param string $html
     * @return void
     */
    public function saveHtml(string $url, string $html): void
    {
        file_put_contents(
            $this->outputDir . '/' . md5($url) . '.html',
            $html
        );
    }

    /**
     * @param string $url
     * @param array $data
     * @return void
     */
    public function saveData(string $url, array $data): void
    {
        file_put_contents(
            $this->outputDir . '/' . md5($url) . '.json',
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @param Throwable $error
     * @param string $context
     * @return void
     */
    public function logError(\Throwable $error, string $context): void
    {
        $message = sprintf(
            "[%s] Error in %s: %s\n",
            date('Y-m-d H:i:s'),
            $context,
            $error->getMessage()
        );

        file_put_contents(
            $this->outputDir . '/errors.log',
            $message,
            FILE_APPEND
        );
    }
}