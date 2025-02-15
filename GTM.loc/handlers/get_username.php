<?php
require_once '../functions/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit();
}

try {
    $conn = connectDB();
    $sql = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(['username' => $row['username']]);
    } else {
        echo json_encode(['error' => 'Логин не найден']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка базы данных']);
}
