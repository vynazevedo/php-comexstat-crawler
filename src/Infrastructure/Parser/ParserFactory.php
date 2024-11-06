<?php
declare(strict_types=1);

namespace App\Infrastructure\Parser;

use App\Domain\Port\TradeDataParserInterface;

class ParserFactory
{
    /**
     * @param string $url
     * @return TradeDataParserInterface
     */
    public function createForUrl(string $url): TradeDataParserInterface
    {
        if (str_contains($url, 'comexstat.mdic.gov.br')) {
            return new ComexDataParser();
        }

        if (str_contains($url, 'portalunico.siscomex.gov.br')) {
            return new PortalUnicoParser();
        }

        throw new \InvalidArgumentException("URL não suportada: $url");
    }
}
