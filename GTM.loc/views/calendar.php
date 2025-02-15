<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GTM</title>
</head>

<body>
    <?php require_once("header_menu.php");
    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ../index.php");
        exit();
    } ?>

    <div class="column">
        <selection>

            <?php
            $daysInMonth = date('t');
            $firstDayOfMonth = date('N', strtotime(date('Y-m-01')));
            $currentDay = date('j');
            $weekends = [6, 7];
            //$holidays = [31]; // Праздники
            ?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Календарь</title>
                <link rel="stylesheet" href="/css/calendar.css">
            </head>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let calendarDays = document.querySelectorAll(".calendar .day");

                    calendarDays.forEach(day => {
                        day.addEventListener("mouseenter", function() {
                            let taskText = this.getAttribute("data-task");
                            if (taskText) {
                                let tooltip = document.createElement("div");
                                tooltip.className = "task-tooltip";
                                tooltip.textContent = taskText;
                                document.body.appendChild(tooltip);

                                let rect = this.getBoundingClientRect();
                                tooltip.style.left = rect.left + "px";
                                tooltip.style.top = (rect.top - 30) + "px";

                                this.addEventListener("mouseleave", function() {
                                    tooltip.remove();
                                });
                            }
                        });
                    });
                });
            </script>

            <body>

                <div class="calendar">
                    <?php
                    require_once '../functions/functions.php';
                    $conn = connectDB();


                    $sql = "SELECT name_task, due_date FROM tasks WHERE status_id = 1";
                    $stmt = $conn->query($sql);
                    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Формируем массив задач по датам
                    $tasksByDate = [];
                    foreach ($tasks as $task) {
                        $date = date('j', strtotime($task['due_date']));
                        if (!isset($tasksByDate[$date])) {
                            $tasksByDate[$date] = [];
                        }
                        $tasksByDate[$date][] = $task['name_task'];
                    }

                    // Пустые ячейки перед началом месяца
                    for ($i = 1; $i < $firstDayOfMonth; $i++) {
                        echo '<div class="day"></div>';
                    }

                    // Дни месяца
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $dayOfWeek = ($firstDayOfMonth + $day - 2) % 7 + 1; // День недели
                        $classes = ['day'];

                        if (in_array($dayOfWeek, $weekends)) {
                            $classes[] = 'weekend';
                        }

                        /*if (in_array($day, $holidays)) {
                            $classes[] = 'holiday';
                        }*/

                        if ($day == $currentDay) {
                            $classes[] = 'current';
                        }

                        $taskText = isset($tasksByDate[$day]) ? implode(', ', $tasksByDate[$day]) : '';
                        $taskAttr = $taskText ? ' data-task="' . htmlspecialchars($taskText) . '"' : '';

                        echo '<div class="' . implode(' ', $classes) . '"' . $taskAttr . '>' . $day . '</div>';
                    }
                    ?>
                </div>
        </selection>
    </div>
    <script src="../assets/calendar.js"></script>

</body>

</html>