<?php
session_start();
session_destroy();
setcookie("username", "", time() - 3600, "/", "", true, true);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// Запрещаем кеширование страницы, чтобы браузер не мог восстановить доступ по кнопке "Назад"
header("Location: ../index.php");
exit();
