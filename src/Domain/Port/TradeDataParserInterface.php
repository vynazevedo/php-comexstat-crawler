<?php
declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Entity\TradeData;

interface TradeDataParserInterface
{
    /**
     * @param string $html
     * @return TradeData
     */
    public function parse(string $html): TradeData;
}
