<?php
require_once '../functions/functions.php';
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['tasks'])) {
    echo json_encode(["success" => false, "error" => "Некорректные данные"]);
    exit;
}

try {
    $conn = connectDB();
    foreach ($data['tasks'] as $task) {
        $stmt = $conn->prepare("UPDATE tasks SET status_id = ? WHERE name_task = ?");
        $stmt->execute([$task['status_id'], $task['name_task']]);
    }

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Ошибка базы данных"]);
}
