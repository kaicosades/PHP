function addTask() {
  let modal = document.createElement("div");
  modal.className = "modal_add_task";
  modal.innerHTML = `
<h3>Добавить задачу</h3>
<label>Название:</label><br>
<input type="text" id="name_task"><br><br>

<label>Важность:</label><br>
<select id="importance">
    <option value="1">Важно</option>
    <option value="2">Желательно</option>
</select><br><br>

<label>Срок окончания:</label><br>
<input type="date" id="due_date"><br><br>

<button onclick="saveTask()">Сохранить</button>
<button onclick="closeModal()">Отмена</button>
`;

  document.body.appendChild(modal);

  window.closeModal = function () {
    document.body.removeChild(modal);
  };

  // Функция сохранения данных
  window.saveTask = function () {
    console.log("Кнопка нажата");
    let name_task = document.getElementById("name_task").value;
    let importance = document.getElementById("importance").value;
    let due_date = document.getElementById("due_date").value;
    console.log(name_task, importance, due_date);

    fetch("../handlers/add_task.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        name_task,
        importance,
        due_date,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        console.log("Ответ сервера:", data);
        if (data.success) {
          alert("Задача добавлена!");
          location.reload();
          closeModal();
        } else {
          alert("Ошибка: " + data.error);
        }
      })
      .catch((error) => console.error("Ошибка:", error));
  };
}

function logout() {
  fetch("../handlers/logout.php", {
    method: "POST",
  }).then(() => {
    alert("Вы вышли из аккаунта.");
    window.location.href = "../index.php";
  });
}

function toggleDropdown() {
  let dropdown = document.getElementById("userDropdown");
  dropdown.style.display =
    dropdown.style.display === "block" ? "none" : "block";
}

function loadUserName() {
  fetch("../handlers/get_username.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.username) {
        document.getElementById("username").innerText = data.username;
      } else {
        console.error("Ошибка: " + data.error);
      }
    })
    .catch((error) => console.error("Ошибка запроса:", error));
}

document.addEventListener("DOMContentLoaded", loadUserName);
