<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('storage/app/public/forms/form09_rev08.xlsx');
$sheet = $spreadsheet->getActiveSheet();
echo 'AI16: '.$sheet->getCell('AI16')->getValue()."\n";
echo 'AJ16: '.$sheet->getCell('AJ16')->getValue()."\n";
echo 'AK16: '.$sheet->getCell('AK16')->getValue()."\n";
