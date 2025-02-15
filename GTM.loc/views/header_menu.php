<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Меню</title>
    <link rel="stylesheet" href="../css/header_menu.css">
    <script src="../assets/header_menu.js" defer></script>
</head>

<body>
    <div class="header">
        <div class="left">
            <span id="tasksDueTomorrow">Загрузка...</span>
        </div>
        <div class="center">
            <button onclick="addTask()">Добавить задачу</button>
        </div>
        <div class="right">
            <div class="icon" onclick="toggleDropdown()"></div>
            <div class="dropdown" id="userDropdown">
                <p>Имя: <span id="username"></span></p>
                <p>Выполнено: <span id="completedTasks">0</span></p>
                <p>Запланировано: <span id="plannedTasks">0</span></p>
                <p>Не выполнено: <span id="pendingTasks">0</span></p>
                <button onclick="logout()">Выход</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch("../handlers/get_task_status.php")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    document.getElementById("completedTasks").textContent = data.completed || 0;
                    document.getElementById("plannedTasks").textContent = data.progress || 0;
                    document.getElementById("pendingTasks").textContent = data.failed || 0;
                })
                .catch(error => console.error("Ошибка загрузки данных:", error));
        });
    </script>
</body>

</html>