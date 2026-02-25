<?php

namespace App\Service;

use App\Entity\RegistroHora;

class RelatorioHoraXlsxExporter
{
    /**
     * @param array<int, RegistroHora> $registros
     */
    public function gerarArquivo(array $registros, int $totalHoras): string
    {
        $arquivoBase = tempnam(sys_get_temp_dir(), 'horas_xlsx_');
        if ($arquivoBase === false) {
            throw new \RuntimeException('Nao foi possivel criar arquivo temporario para exportacao.');
        }

        $xlsxPath = $arquivoBase.'.xlsx';
        @unlink($arquivoBase);
        $arquivos = [
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->relsXml(),
            'xl/workbook.xml' => $this->workbookXml(),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelsXml(),
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $this->sheetXml($registros, $totalHoras),
        ];

        $this->criarPacoteXlsx($xlsxPath, $arquivos);

        $conteudo = file_get_contents($xlsxPath);
        @unlink($xlsxPath);

        if ($conteudo === false) {
            throw new \RuntimeException('Nao foi possivel ler o arquivo XLSX gerado.');
        }

        return $conteudo;
    }

    /**
     * @param array<int, RegistroHora> $registros
     */
    private function sheetXml(array $registros, int $totalHoras): string
    {
        $rows = [];
        $rows[] = '<row r="1">'
            . '<c r="A1" t="inlineStr" s="3"><is><t>Data</t></is></c>'
            . '<c r="B1" t="inlineStr" s="3"><is><t>Horas</t></is></c>'
            . '<c r="C1" t="inlineStr" s="3"><is><t>Comentários</t></is></c>'
            . '</row>';

        $rowIndex = 2;
        foreach ($registros as $registro) {
            $dataSerial = $this->dataParaSerialExcel($registro->getData());
            $horas = (string) $registro->getHorasTrabalhadas();
            $comentarios = $this->xmlEscape($registro->getComentarios());

            $rows[] = sprintf(
                '<row r="%1$d"><c r="A%1$d" s="1"><v>%2$d</v></c><c r="B%1$d" s="2"><v>%3$s</v></c><c r="C%1$d" t="inlineStr"><is><t xml:space="preserve">%4$s</t></is></c></row>',
                $rowIndex,
                $dataSerial,
                $horas,
                $comentarios
            );
            ++$rowIndex;
        }

        $totalFormatado = (string) $totalHoras;
        $rows[] = sprintf(
            '<row r="%1$d"><c r="A%1$d" t="inlineStr" s="3"><is><t>TOTAL</t></is></c><c r="B%1$d" s="4"><v>%2$s</v></c></row>',
            $rowIndex,
            $totalFormatado
        );

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<cols><col min="1" max="1" width="14"/><col min="2" max="2" width="18"/><col min="3" max="3" width="75"/></cols>'
            . '<sheetData>' . implode('', $rows) . '</sheetData>'
            . '</worksheet>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Relatório" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="14" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="1" fontId="1" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function dataParaSerialExcel(\DateTimeImmutable $data): int
    {
        $dataUtc = $data->setTimezone(new \DateTimeZone('UTC'))->setTime(0, 0);
        $excelEpoch = new \DateTimeImmutable('1899-12-30 00:00:00', new \DateTimeZone('UTC'));
        $intervalo = $excelEpoch->diff($dataUtc);

        return (int) $intervalo->format('%a');
    }

    private function xmlEscape(string $valor): string
    {
        return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, string> $arquivos
     */
    private function criarPacoteXlsx(string $xlsxPath, array $arquivos): void
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Nao foi possivel abrir o arquivo XLSX temporario.');
            }

            foreach ($arquivos as $path => $conteudo) {
                $zip->addFromString($path, $conteudo);
            }
            $zip->close();

            return;
        }

        // Fallback para ambientes sem extensao zip habilitada.
        try {
            $phar = new \PharData($xlsxPath, 0, null, \Phar::ZIP);
            foreach ($arquivos as $path => $conteudo) {
                $phar[$path] = $conteudo;
            }
            unset($phar);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Nao foi possivel montar o arquivo XLSX.', 0, $e);
        }
    }
}
