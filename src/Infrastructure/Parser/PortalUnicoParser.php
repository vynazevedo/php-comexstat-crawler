<?php

namespace App\Infrastructure\Parser;

use App\Domain\Entity\TradeData;
use App\Domain\Port\TradeDataParserInterface;
use DOMDocument;
use DOMXPath;

class PortalUnicoParser implements TradeDataParserInterface
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
    private function extractExportData(\DOMXPath $xpath): array
    {
        return $this->extractTableData(
            $xpath,
            "//div[contains(@class, 'exportacao')]//table//tr"
        );
    }

    /**
     * @param DOMXPath $xpath
     * @return array
     */
    private function extractImportData(DOMXPath $xpath): array
    {
        return $this->extractTableData(
            $xpath,
            "//div[contains(@class, 'importacao')]//table//tr"
        );
    }

    /**
     * @param DOMXPath $xpath
     * @param string $query
     * @return array
     */
    private function extractTableData(\DOMXPath $xpath, string $query): array
    {
        $data = [];
        $rows = $xpath->query($query);

        if ($rows) {
            foreach ($rows as $row) {
                $rowData = [];
                $cells = $xpath->query(".//td", $row);

                if ($cells->length > 0) {
                    foreach ($cells as $cell) {
                        $value = trim($cell->textContent);
                        if (preg_match('/^R\$\s*[\d.,]+/', $value)) {
                            $value = preg_replace('/[R\$\s.]/', '', $value);
                            $value = str_replace(',', '.', $value);
                        }
                        $rowData[] = $value;
                    }
                    $data[] = $rowData;
                }
            }
        }

        return $data;
    }
}
