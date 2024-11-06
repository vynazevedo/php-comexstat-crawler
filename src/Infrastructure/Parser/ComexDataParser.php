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

        return new TradeData(
            exports: $this->extractExportData($xpath),
            imports: $this->extractImportData($xpath),
            timestamp: date('Y-m-d H:i:s')
        );
    }

    /**
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractExportData(DOMXPath $xpath): array
    {
        return $this->extractTableData($xpath, "//table[contains(@class, 'exports-table')]//tr");
    }

    /**
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractImportData(DOMXPath $xpath): array
    {
        return $this->extractTableData($xpath, "//table[contains(@class, 'imports-table')]//tr");
    }

    /**
     * @param DOMXPath $xpath
     * @param string $query
     * @return array
     */
    private function extractTableData(DOMXPath $xpath, string $query): array
    {
        $data = [];
        $rows = $xpath->query($query);

        if ($rows) {
            foreach ($rows as $row) {
                $cells = $xpath->query(".//td", $row);
                if ($cells->length > 0) {
                    $rowData = [];
                    foreach ($cells as $cell) {
                        $rowData[] = trim($cell->textContent);
                    }
                    $data[] = $rowData;
                }
            }
        }

        return $data;
    }
}
