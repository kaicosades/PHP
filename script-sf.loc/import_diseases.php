<?php
require_once 'config/db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('data.xlsx');
$sheet = $spreadsheet->getActiveSheet();

$rowNum = 0;
foreach ($sheet->getRowIterator() as $row) {
    $rowNum++;
    if ($rowNum == 1) continue;
    if ($rowNum > 242) break;

    $cells = [];
    foreach ($row->getCellIterator() as $cell) {
        $cells[] = $cell->getValue();
    }

    $firstCol = trim($cells[0]);
    $descCol  = isset($cells[3]) ? trim($cells[3]) : '';
    $diagCol  = isset($cells[5]) ? trim($cells[5]) : '';

    if (!$firstCol) continue;

    $parts = explode(' ', $firstCol, 2);
    $code = $parts[0];
    $name = isset($parts[1]) ? $parts[1] : '';
    $description = $descCol;
    $treatment = $diagCol;

    // Проверка на дубликат
    $stmt = $pdo->prepare("SELECT id FROM diseases WHERE code = ? LIMIT 1");
    $stmt->execute([$code]);
    if ($stmt->rowCount() == 0) {
        $stmtInsert = $pdo->prepare(
            "INSERT INTO diseases (code, name, description, treatment_diagnosis) 
             VALUES (?, ?, ?, ?)"
        );
        $stmtInsert->execute([$code, $name, $description, $treatment]);
        echo "Добавлено: $code - $name<br>";
    } else {
        echo "Пропущено (дубликат): $code<br>";
    }
}

echo "Импорт завершён.";
