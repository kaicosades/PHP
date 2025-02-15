<?php

require_once '../functions/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Не авторизован"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $conn = connectDB();
    $sql = "SELECT 
                SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) AS progress,
                SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) AS failed
            FROM tasks 
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($stats);
} catch (PDOException $e) {
    echo json_encode(["error" => "Ошибка базы данных"]);
}
