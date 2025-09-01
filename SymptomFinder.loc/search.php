<?php
require_once 'config/db.php';


$q = $_GET['q'] ?? '';
$q = trim($q);

header('Content-Type: application/json; charset=utf-8');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$terms = array_map('trim', explode(',', $q));
$terms = array_values(array_filter(array_unique($terms), fn($s) => $s !== ''));

if (empty($terms)) {
    echo json_encode([]);
    exit;
}

/* находим ID симптомов по именам */
$ph = implode(',', array_fill(0, count($terms), '?'));
$sql = "SELECT id, name FROM symptoms WHERE name IN ($ph)";
$stmt = $pdo->prepare($sql);
$stmt->execute($terms);
$found = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($found)) {
    echo json_encode([]);
    exit;
}

$symptomIds = array_column($found, 'id');
$ph2 = implode(',', array_fill(0, count($symptomIds), '?'));

/*
Ищем болезни, где встречаются эти симптомы.
matches = сколько из введённых симптомов нашли у болезни.
matched_symptoms = какие именно совпали (именами).
all_symptoms = ВСЕ симптомы болезни.
 */
$sql = "
    SELECT 
        d.id, d.name, d.code, d.description, d.treatment_diagnosis,
        COUNT(DISTINCT ds.symptom_id) AS matches,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS matched_symptoms,
        (
            SELECT GROUP_CONCAT(DISTINCT s2.name ORDER BY s2.name SEPARATOR ', ')
            FROM disease_symptom ds2
            JOIN symptoms s2 ON s2.id = ds2.symptom_id
            WHERE ds2.disease_id = d.id
        ) AS all_symptoms
    FROM diseases d
    JOIN disease_symptom ds ON d.id = ds.disease_id
    JOIN symptoms s ON s.id = ds.symptom_id
    WHERE ds.symptom_id IN ($ph2)
    GROUP BY d.id
    ORDER BY matches DESC, d.name
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute($symptomIds);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($terms);
$out = [];
foreach ($rows as $r) {
    $prob = $total > 0 ? round(($r['matches'] / $total) * 100) : 0;
    $out[] = [
        'id' => (int)$r['id'],
        'name' => $r['name'],
        'code' => $r['code'],
        'description' => $r['description'],
        'description_full' => $r['description_full'] ?? '',
        'treatment_diagnosis' => $r['treatment_diagnosis'],
        'matched_symptoms' => $r['matched_symptoms'],
        'all_symptoms' => $r['all_symptoms'],
        'matches' => (int)$r['matches'],
        'probability' => $prob
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
