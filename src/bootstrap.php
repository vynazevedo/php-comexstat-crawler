<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCase\CrawlWebsiteUseCase;
use App\Infrastructure\Http\SwooleWebClient;
use App\Infrastructure\Storage\FileSystemStorage;

\Swoole\Runtime::enableCoroutine();

$crawler = new CrawlWebsiteUseCase(
    client: new SwooleWebClient(),
    storage: new FileSystemStorage()
);

$urls = [
    // ComexStat
    'https://balanca.economia.gov.br/balanca/pg_principal_bc/principais_resultados.html',

    // Portal Único Siscomex
    'https://www.gov.br/siscomex/pt-br',
    'https://portalunico.siscomex.gov.br/portal/',
    'https://portalunico.siscomex.gov.br/capacidade/consulta-capacidade',
];

try {
    $results = $crawler->execute($urls);

    echo "Crawling concluído!\n";
    print_r($results);

    foreach ($results as $result) {
        echo sprintf(
            "URL: %s\nStatus: %d\nTimestamp: %s\nRegistros: %d\n\n",
            $result->url,
            $result->status,
            $result->timestamp,
            count($result->data['exports'] ?? []) + count($result->data['imports'] ?? [])
        );
    }
} catch (\Exception $e) {
    echo "Erro durante o crawling: " . $e->getMessage() . "\n";
}
