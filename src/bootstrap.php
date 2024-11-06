<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCase\CrawlWebsiteUseCase;
use App\Infrastructure\Http\SwooleWebClient;
use App\Infrastructure\Parser\ComexDataParser;
use App\Infrastructure\Storage\FileSystemStorage;

\Swoole\Runtime::enableCoroutine();

$crawler = new CrawlWebsiteUseCase(
    client: new SwooleWebClient(),
    parser: new ComexDataParser(),
    storage: new FileSystemStorage()
);

$urls = [
    'https://comexstat.mdic.gov.br/pt/home',
];

try {
    $results = $crawler->execute($urls);

    echo "Crawling concluído!\n";

    foreach ($results as $result) {
        echo sprintf(
            "URL: %s\nStatus: %d\nTimestamp: %s\n\n",
            $result->url,
            $result->status,
            $result->timestamp
        );
    }
} catch (\Exception $e) {
    echo "Erro durante o crawling: " . $e->getMessage() . "\n";
}
