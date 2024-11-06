<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\CrawlResult;
use App\Domain\Port\WebClientInterface;
use App\Domain\Port\CrawlerStorageInterface;
use App\Infrastructure\Parser\ParserFactory;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine;

class CrawlWebsiteUseCase
{
    private ParserFactory $parserFactory;

    public function __construct(
        private readonly WebClientInterface $client,
        private readonly CrawlerStorageInterface $storage
    ) {
        $this->parserFactory = new ParserFactory();
    }

    /**
     * @param array $urls
     * @param int $concurrency
     * @return array
     */
    public function execute(array $urls, int $concurrency = 20): array
    {
        $results = [];
        $channel = new Channel($concurrency);

        Coroutine\run(function() use ($channel, $urls, &$results) {
            $coroutines = [];

            foreach ($urls as $url) {
                $channel->push(true);

                $coroutines[] = Coroutine::create(function() use ($channel, $url, &$results) {
                    try {
                        $result = $this->processSingleUrl($url);
                        $results[] = $result;
                    } catch (\Exception $e) {
                        $this->storage->logError($e, $url);
                    } finally {
                        $channel->pop();
                    }
                });
            }

            foreach ($coroutines as $cid) {
                Coroutine::join([$cid]);
            }
        });

        return $results;
    }

    /**
     * @param string $url
     * @return CrawlResult
     */
    private function processSingleUrl(string $url): CrawlResult
    {
        $response = $this->client->fetch($url);
        $parser = $this->parserFactory->createForUrl($url);
        $tradeData = $parser->parse($response->html);

        $this->storage->saveHtml($url, $response->html);
        $this->storage->saveData($url, (array)$tradeData);

        return new CrawlResult(
            url: $url,
            status: $response->status,
            data: (array)$tradeData,
            timestamp: date('Y-m-d H:i:s')
        );
    }
}
