<?php
require_once "config/db.php";
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php require_once "views/header.php";  ?>
    <div class="page">
        <h1>Поиск болезни по симптомам</h1>

        <div id="search-block">
            <input type="text" id="symptom-input" placeholder="Введите симптом...">
            <div id="suggestions"></div>
        </div>

        <div class="actions">
            <button id="clear-btn">Очистить</button>
            <p class="disclaimer">
                Обратите внимание: данный сервис предоставляет информацию исключительно в ознакомительных целях.
                Он не заменяет консультацию врача. Не занимайтесь самолечением — при наличии симптомов обратитесь к специалисту.
            </p>
        </div>

        <div id="results-block">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Название болезни</th>
                        <th>Код МКБ-10</th>
                        <th>Описание</th>
                        <th>Совпавшие симптомы</th>
                        <th>Диагностика / Лечение</th>
                        <th>Вероятность</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
    <?php require_once "views/footer.php"; ?>

    <!-- Модальное окно -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modal-body"></div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>