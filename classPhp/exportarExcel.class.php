<?php

require __DIR__ . "/../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class exportarExcel
{
    function exportar($titulo, $dados, $titulos) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:O1');
        $sheet->getCell('A1')->getStyle()->getFill()->setFillType('solid')->getStartColor()->setARGB('d2d2d2');
        $sheet->getCell('A1')->getStyle()->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getCell('A1')->getStyle()->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getCell('O1')->getStyle()->getBorders()->getAllBorders()->setBorderStyle(PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);

        foreach($dados as $key => $dado) {
            for ($x = 0; $x < sizeof($dado); $x++) {
                $sheet->setCellValue(chr($x + 65).($key + 3), $dado[$x]);
                $sheet->setCellValue(chr($x + 65)."2", $titulos[$x]);
                $sheet->getCell(chr($x + 65)."2")->getStyle()->getBorders()->getAllBorders()->setBorderStyle('medium');
                $sheet->getCell(chr($x + 65)."2")->getStyle()->getAlignment()->setHorizontal('center');
                $sheet->getCell(chr($x + 65)."2")->getStyle()->getFill()->setFillType('solid')->getStartColor()->setARGB('d2d2d2');

                $sheet->getColumnDimension(chr($x + 65))->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $data = date("Y-m-d h-i-sa");
        $writer->save($data.'.xlsx');
        return $data;

    }
}
