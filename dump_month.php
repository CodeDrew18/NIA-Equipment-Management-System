<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('storage/app/public/forms/form09_rev08.xlsx');
$sheet = $spreadsheet->getActiveSheet();
foreach ($sheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);
    foreach ($cellIterator as $cell) {
        if (stripos($cell->getValue() ?? '', 'MONTH') !== false) {
            echo $cell->getCoordinate() . ' - ' . $cell->getValue() . "\n";
        }
    }
}
