<?php

function registerUser(string $name, string $email, string $pass, string $pass_conf): void
{
    $name = trim(htmlspecialchars($name));
    $email = trim(htmlspecialchars($email));
    $pass = trim(htmlspecialchars($pass));
    $pass_conf = trim(htmlspecialchars($pass_conf));

    $errors = [];

    if ($name === "" || $email === "" || $pass === "" || $pass_conf === "") {
        $errors[] = "Все поля должны быть заполнены";
    }

    if ($pass !== $pass_conf) {
        $errors[] = "Пароли должны совпадать";
    }

    if (strlen($pass) < 6 || strlen($pass) > 50) {
        $errors[] = "Длина пароля должна быть от 6 до 50 символов";
    }

    if (!preg_match("/^[a-zA-Z0-9_!@$]{6,50}$/", $pass)) {
        $errors[] = "Пароль может содержать только латинские буквы, цифры и символы _!@$";
    }

    if (mb_strlen($name) < 3 || mb_strlen($name) > 50) {
        $errors[] = "Длина имени должна быть от 3 до 50 символов";
    }

    if (!preg_match("/^[a-zA-Zа-яА-Я0-9_ -]{3,50}$/u", $name)) {
        $errors[] = "Имя может содержать только буквы, цифры, пробел, _, -";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            printAlert($error);
        }
        exit();
    }

    try {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            printAlert("Этот email уже зарегистрирован");
            exit();
        }

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPass]);

        printAlert("Регистрация успешна!", "h5", "green");
    } catch (PDOException $e) {
        printAlert("Ошибка регистрации: " . $e->getMessage());
    }
}

function printAlert(string $message, string $tag = "h5", string $color = "red"): void
{
    echo "<$tag style='color:$color;'><b>$message</b></$tag>";
}

function connectDB()
{
    $host = "MySQL-8.2";
    $dbname = "GTM";
    $username = "root";
    $password = "";
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    try {
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $conn;
    } catch (PDOException $e) {
        die("Ошибка подключения: " . $e->getMessage());
    }
}
