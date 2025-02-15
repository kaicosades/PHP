document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".calendar .day").forEach((day) => {
    day.addEventListener("click", function () {
      let taskText = this.getAttribute("data-task");
      let dayNumber = this.textContent.trim();

      if (taskText) {
        openTaskModal(dayNumber, taskText);
      }
    });
  });
});

function openTaskModal(day, tasks) {
  let modal = document.createElement("div");
  modal.classList.add("modal_close_task");
  modal.innerHTML = `
        <div class="modal-content">
            <h3>Задачи на ${day} число</h3>
            <ul id="taskList">
                ${tasks
                  .split(", ")
                  .map(
                    (task) => `
                    <li>
                        <span>${task}</span>
                        <select class="statusSelect">
                            <option value="1">В процессе</option>
                            <option value="2">Выполнено</option>
                            <option value="3">Провалено</option>
                        </select>
                    </li>
                `
                  )
                  .join("")}
            </ul>
            <button id="saveStatusBtn">Сохранить</button>
            <button id="closeModalBtn">Отмена</button>
        </div>
    `;
  document.body.appendChild(modal);

  document
    .getElementById("closeModalBtn")
    .addEventListener("click", () => modal.remove());

  document.getElementById("saveStatusBtn").addEventListener("click", () => {
    let tasksToUpdate = [];
    document.querySelectorAll("#taskList li").forEach((li, index) => {
      let taskName = li.querySelector("span").textContent;
      let status = li.querySelector("select").value;
      tasksToUpdate.push({ name_task: taskName, status_id: status });
    });

    fetch("../handlers/update_task_status_2.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ tasks: tasksToUpdate }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Статусы обновлены!");
          modal.remove();
          location.reload();
        } else {
          alert("Ошибка: " + data.error);
        }
      })
      .catch((error) => console.error("Ошибка:", error));
  });
}
