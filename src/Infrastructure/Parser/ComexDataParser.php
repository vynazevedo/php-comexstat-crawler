<?php
declare(strict_types=1);

namespace App\Infrastructure\Parser;

use App\Domain\Port\TradeDataParserInterface;
use App\Domain\Entity\TradeData;
use DOMDocument;
use DOMXPath;

class ComexDataParser implements TradeDataParserInterface
{
    /**
     * @param string $html
     * @return TradeData
     */
    public function parse(string $html): TradeData
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $data = $this->extractTableData($xpath);

        return new TradeData(
            exports: $data['exports'],
            imports: $data['imports'],
            timestamp: date('Y-m-d H:i:s')
        );
    }

    /**
     * @param DOMXPath $xpath
     * @return array|array[]
     */
    private function extractTableData(DOMXPath $xpath): array
    {
        $data = [
            'exports' => [],
            'imports' => [],
            'corrente' => [],
            'saldo' => []
        ];

        $rows = $xpath->query("//table[contains(@class, 'lightable-classic')]//tbody//tr");

        if ($rows) {
            foreach ($rows as $row) {
                $cells = $xpath->query(".//td", $row);
                if ($cells->length > 0) {
                    $periodo = trim($cells[0]->textContent);

                    $data['exports'][] = [
                        'periodo' => $periodo,
                        'valor' => $this->parseNumericValue($cells[1]->textContent),
                        'media_diaria' => $this->parseNumericValue($cells[2]->textContent),
                        'variacao' => $this->parseVariacao($cells[9]->textContent)
                    ];

                    $data['imports'][] = [
                        'periodo' => $periodo,
                        'valor' => $this->parseNumericValue($cells[3]->textContent),
                        'media_diaria' => $this->parseNumericValue($cells[4]->textContent),
                        'variacao' => $this->parseVariacao($cells[10]->textContent)
                    ];

                    $data['corrente'][] = [
                        'periodo' => $periodo,
                        'valor' => $this->parseNumericValue($cells[5]->textContent),
                        'media_diaria' => $this->parseNumericValue($cells[6]->textContent),
                        'variacao' => $this->parseVariacao($cells[11]->textContent)
                    ];

                    $data['saldo'][] = [
                        'periodo' => $periodo,
                        'valor' => $this->parseNumericValue($cells[7]->textContent),
                        'media_diaria' => $this->parseNumericValue($cells[8]->textContent),
                        'variacao' => $this->parseVariacao($cells[12]->textContent)
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param string $value
     * @return float
     */
    private function parseNumericValue(string $value): float
    {
        return (float) str_replace(['.', ','], ['', '.'], trim($value));
    }

    /**
     * @param string $value
     * @return float
     */
    private function parseVariacao(string $value): float
    {
        $value = preg_replace('/[^0-9\-\.]/', '', trim($value));
        return (float) $value;
    }
}
