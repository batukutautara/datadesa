<?php

class XlsxReader {
    private $zip;
    private $sharedStrings = [];
    private $sheetData = [];
    private $error = '';

    public function load($filePath) {
        if (!file_exists($filePath)) {
            $this->error = 'File tidak ditemukan';
            return false;
        }

        $this->zip = new ZipArchive;
        if ($this->zip->open($filePath) !== true) {
            $this->error = 'Gagal membuka file (bukan XLSX valid)';
            return false;
        }

        libxml_use_internal_errors(true);
        $this->parseSharedStrings();
        $this->parseSheet();
        libxml_use_internal_errors(false);
        $this->zip->close();
        return true;
    }

    private function parseSharedStrings() {
        $xml = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) return;

        $reader = new SimpleXMLElement($xml);
        foreach ($reader->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string)$si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $r) {
                    $text .= (string)$r->t;
                }
            }
            $this->sharedStrings[] = $text;
        }
    }

    private function parseSheet() {
        $xml = $this->zip->getFromName('xl/worksheets/sheet1.xml');
        if ($xml === false) {
            $xml = $this->findFirstSheet();
        }
        if ($xml === false) {
            $this->error = 'Tidak dapat menemukan sheet';
            return;
        }

        $xml = preg_replace('/\s+xmlns[^=]*="[^"]*"/', '', $xml);
        $xml = preg_replace('/\s+xmlns:[^=]+="[^"]*"/', '', $xml);
        $reader = @new SimpleXMLElement($xml);

        $rows = [];
        if ($reader && isset($reader->sheetData)) {
            foreach ($reader->sheetData->row as $row) {
                $rowIndex = (int)$row->attributes()->r;
                $cells = [];
                foreach ($row->c as $cell) {
                    $ref = (string)$cell->attributes()->r;
                    $type = (string)$cell->attributes()->t;
                    $col = preg_replace('/[0-9]/', '', $ref);
                    $colIndex = $this->colToIndex($col);

                    $value = '';
                    if ($type === 's' && isset($cell->v)) {
                        $idx = (int)$cell->v;
                        $value = $this->sharedStrings[$idx] ?? '';
                    } elseif (isset($cell->v)) {
                        $value = (string)$cell->v;
                    }

                    $cells[$colIndex] = $value;
                }
                $rows[$rowIndex] = $cells;
            }
        }

        $this->sheetData = $rows;
    }

    private function findFirstSheet() {
        $workbook = $this->zip->getFromName('xl/workbook.xml');
        if ($workbook === false) return false;

        $wb = @new SimpleXMLElement($workbook);
        if (!$wb) return false;

        $sheets = $wb->xpath('//*[local-name()="sheet"]');
        if (!$sheets || !isset($sheets[0])) return false;

        $rid = (string)$sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
        if (!$rid) return false;

        $rels = $this->zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($rels === false) return false;

        $r = @new SimpleXMLElement($rels);
        if (!$r) return false;

        foreach ($r->Relationship as $rel) {
            $relId = (string)$rel->attributes()->Id;
            $relTarget = (string)$rel->attributes()->Target;
            if ($relId === $rid) {
                return $this->zip->getFromName('xl/' . $relTarget);
            }
        }

        return false;
    }

    private function colToIndex($col) {
        $col = strtoupper($col);
        $result = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $result = $result * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $result;
    }

    public function getRows() {
        return $this->sheetData;
    }

    public function getRowsAsArray() {
        $result = [];
        $headers = $this->getHeaders();
        $dataRows = $this->getDataRows();
        foreach ($dataRows as $row) {
            $mapped = [];
            foreach ($headers as $colIndex => $header) {
                $mapped[$header] = $row[$colIndex] ?? '';
            }
            $result[] = $mapped;
        }
        return $result;
    }

    public function getHeaders() {
        if (empty($this->sheetData)) return [];
        $firstRow = reset($this->sheetData);
        ksort($firstRow);
        $result = [];
        foreach ($firstRow as $colIndex => $value) {
            $result[$colIndex] = trim($value);
        }
        return $result;
    }

    public function getDataRows() {
        if (empty($this->sheetData)) return [];
        $rows = $this->sheetData;
        array_shift($rows);
        return $rows;
    }

    public function getRowCount() {
        return count($this->getDataRows());
    }

    public function getError() {
        return $this->error;
    }
}
