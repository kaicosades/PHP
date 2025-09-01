<?php
require_once 'config/db.php';

$q = $_GET['q'] ?? '';
$q = trim($q);

//mb_strlen для работы с Кириллицей
if (mb_strlen($q) < 1) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name FROM symptoms WHERE name LIKE :q ORDER BY name LIMIT 10");
$stmt->execute([':q' => "%$q%"]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
