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

    $diseaseCol = isset($cells[0]) ? trim($cells[0]) : '';
    $symptomsCol = isset($cells[4]) ? trim($cells[4]) : '';

    if (!$diseaseCol || !$symptomsCol) continue;

    $parts = explode(' ', $diseaseCol, 2);
    $code = $parts[0];

    $stmt = $pdo->prepare("SELECT id FROM diseases WHERE code = :code LIMIT 1");
    $stmt->execute([':code' => $code]);
    $disease = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$disease) {
        echo "❌ Болезнь не найдена: $code<br>";
        continue;
    }
    $diseaseId = $disease['id'];

    // Разделяем симптомы, игнорируя запятые внутри скобок
    $symptomParts = preg_split('/,(?![^(]*\))/u', $symptomsCol);

    foreach ($symptomParts as $rawSymptom) {
        $symptom = trim($rawSymptom);
        if ($symptom === '') continue;

        $variant = 1;     // по умолчанию
        $category = NULL; // по умолчанию


        if (preg_match('/^\/\s*/u', $symptom)) {
            $variant = 2;
            $symptom = preg_replace('/^\/\s*/u', '', $symptom);
        }


        if (preg_match('/^~\s*/u', $symptom)) {
            $category = 2; // редкий
            $symptom = preg_replace('/^~\s*/u', '', $symptom);
        }


        if (preg_match('/^!+\s*|\(\s*\)/u', $symptom)) {
            $category = 1; // общий
            $variant = NULL;
            $symptom = preg_replace('/^!+\s*|\(\s*\)/u', '', $symptom);
        }

        // Обрезаем лишние точки/запятые
        $symptom = rtrim($symptom, ".,;");

        if ($symptom === '') continue;

        $stmt = $pdo->prepare("SELECT id FROM symptoms WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $symptom]);
        $symptomRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$symptomRow) {
            echo "⚠️ Симптом не найден: $symptom<br>";
            continue;
        }
        $symptomId = $symptomRow['id'];

        $check = $pdo->prepare("SELECT id FROM disease_symptom WHERE disease_id = :disease_id AND symptom_id = :symptom_id AND symptom_variant " . ($variant === NULL ? "IS NULL" : "= :variant") . " LIMIT 1");
        $params = [':disease_id' => $diseaseId, ':symptom_id' => $symptomId];
        if ($variant !== NULL) $params[':variant'] = $variant;
        $check->execute($params);
        if ($check->rowCount() == 0) {
            $insert = $pdo->prepare("
                INSERT INTO disease_symptom (disease_id, symptom_id, symptom_variant, symptom_category)
                VALUES (:disease_id, :symptom_id, :variant, :category)
            ");
            $insert->execute([
                ':disease_id' => $diseaseId,
                ':symptom_id' => $symptomId,
                ':variant' => $variant,
                ':category' => $category
            ]);
            echo "✅ Связь добавлена: $code → $symptom (variant " . ($variant ?? 'NULL') . ", category " . ($category ?? 'NULL') . ")<br>";
        } else {
            echo "🔄 Уже есть связь: $code → $symptom<br>";
        }
    }
}

echo "<br>Импорт связей завершён.";
