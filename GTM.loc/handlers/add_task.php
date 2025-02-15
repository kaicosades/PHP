<?php
require_once '../functions/functions.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Недопустимый метод запроса']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit();
}

$conn = connectDB();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name_task'], $data['importance'], $data['due_date'])) {
    echo json_encode(['error' => 'Не все данные переданы']);
    exit();
}

$name_task = trim($data['name_task']);
$importance = (int) $data['importance'];
$due_date = $data['due_date'];
$user_id = $_SESSION['user_id'];

try {
    $sql = "INSERT INTO tasks (user_id, name_task, importance, due_date, status_id) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $name_task, $importance, $due_date]);

    echo json_encode(['success' => 'Задача добавлена']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка при добавлении: ' . $e->getMessage()]);
}
