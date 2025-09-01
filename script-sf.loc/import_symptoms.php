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

    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);

    $cells = [];
    foreach ($cellIterator as $cell) {
        $cells[] = $cell->getValue();
    }

    $symptomsCol = isset($cells[4]) ? trim($cells[4]) : '';
    if (!$symptomsCol) continue;

    $symptomParts = preg_split('/,(?![^(]*\))/u', $symptomsCol);

    foreach ($symptomParts as $rawSymptom) {
        $symptom = trim($rawSymptom);

        if ($symptom === '') continue;

        // Убираем служебные символы в начале
        $symptom = preg_replace('/^(\/|~|!>|!)+\s*/u', '', $symptom);

        // Если скобки пустые "()", удаляем их
        $symptom = preg_replace('/\(\s*\)/u', '', $symptom);

        // Убираем точку/запятую/точку с запятой в конце
        $symptom = rtrim($symptom, ".,;");

        if ($symptom === '') continue;

        $check = $pdo->prepare("SELECT id FROM symptoms WHERE name = :name LIMIT 1");
        $check->execute([':name' => $symptom]);

        if ($check->rowCount() == 0) {
            $insert = $pdo->prepare("INSERT INTO symptoms (name) VALUES (:name)");
            if ($insert->execute([':name' => $symptom])) {
                echo "Добавлен симптом: $symptom<br>";
            } else {
                echo "Ошибка при добавлении: $symptom<br>";
            }
        } else {
            echo "Пропущен (дубликат): $symptom<br>";
        }
    }
}

echo "Импорт симптомов завершён.";
