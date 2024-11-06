<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Port\WebClientInterface;

class SwooleWebClient implements WebClientInterface
{
    /**
     * @var array
     */
    private array $cookies = [];

    /**
     * @param string $url
     * @return WebResponse
     */
    public function fetch(string $url): WebResponse
    {
        $parsedUrl = parse_url($url);
        $client = new \Swoole\Coroutine\Http\Client(
            $parsedUrl['host'],
            $parsedUrl['scheme'] === 'https' ? 443 : 80,
            $parsedUrl['scheme'] === 'https'
        );

        $client->set([
            'timeout' => 10,
            'keep_alive' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; SwooleBot/1.0)',
                'Accept' => 'text/html,application/xhtml+xml',
                'Connection' => 'keep-alive'
            ]
        ]);

        if (!empty($this->cookies)) {
            $client->setCookies($this->cookies);
        }

        $client->get($parsedUrl['path'] ?? '/');

        if (isset($client->headers['set-cookie'])) {
            $this->cookies = array_merge($this->cookies, $client->headers['set-cookie']);
        }

        $response = new WebResponse(
            status: $client->statusCode,
            html: $client->body,
            headers: $client->headers
        );

        $client->close();
        return $response;
    }
}
