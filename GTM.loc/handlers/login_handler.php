<?php
require_once '../functions/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enter'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $conn = connectDB();
        $sql = "SELECT * FROM `users` WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        if ($row && password_verify($password, $row->password)) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row->user_id;
            setcookie("username", $username, 0, "/", "", isset($_SERVER['HTTPS']), true);
            header("Location: ../views/calendar.php");
            exit();
        } else {
            $_SESSION['error'] = "Неверный логин или пароль";
            header("Location: ../index.php?page=login");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Ошибка базы данных";
        header("Location: ../index.php?page=login");
        exit();
    }
} else {
    header("Location: ../index.php?page=login");
    exit();
}
