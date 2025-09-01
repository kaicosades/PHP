const input = document.getElementById("symptom-input");
const suggestions = document.getElementById("suggestions");

input.addEventListener("input", () => {
  const raw = input.value;
  const parts = raw.split(",").map((s) => s.trim());
  const last = parts[parts.length - 1] || "";

  if (last.length < 1) {
    suggestions.innerHTML = "";
    suggestions.style.display = "none";
    return;
  }

  fetch("suggest.php?q=" + encodeURIComponent(last))
    .then((res) => (res.ok ? res.json() : Promise.reject()))
    .then((data) => {
      suggestions.innerHTML = "";
      if (!data.length) {
        suggestions.style.display = "none";
        return;
      }

      data.forEach((symptom) => {
        const div = document.createElement("div");
        div.textContent = symptom.name;
        div.addEventListener("click", () => {
          parts[parts.length - 1] = symptom.name;
          const cleaned = [
            ...new Set(parts.map((s) => s.trim()).filter(Boolean)),
          ];
          input.value = cleaned.join(", ") + ", ";
          suggestions.innerHTML = "";
          suggestions.style.display = "none";
          loadDiseases(input.value);
        });
        suggestions.appendChild(div);
      });
      suggestions.style.display = "block";
    })
    .catch(() => {
      suggestions.innerHTML = "";
      suggestions.style.display = "none";
    });
});

function loadDiseases(symptomList) {
  const tbody = document.querySelector(".results-table tbody");
  tbody.innerHTML = "";

  fetch("search.php?q=" + encodeURIComponent(symptomList))
    .then((res) => (res.ok ? res.json() : Promise.reject()))
    .then((diseases) => {
      if (!diseases.length) {
        const row = document.createElement("tr");
        row.innerHTML = `<td colspan="6">Ничего не найдено</td>`;
        tbody.appendChild(row);
        return;
      }

      diseases.forEach((d) => {
        let probColor = "gray";
        if (d.probability >= 70) probColor = "green";
        else if (d.probability >= 40) probColor = "blue";

        const row = document.createElement("tr");

        // Название + "Подробнее" (синяя ссылка)
        const nameTd = document.createElement("td");
        const nameDiv = document.createElement("div");
        nameDiv.className = "cell-text";
        nameDiv.textContent = d.name;

        const moreLink = document.createElement("span");
        moreLink.textContent = "Подробнее";
        moreLink.style.color = "blue";
        moreLink.style.cursor = "pointer";
        //moreLink.style.marginLeft = "8px";

        moreLink.addEventListener("click", () => {
          const modalBody = document.getElementById("modal-body");
          modalBody.innerHTML = `
            <h3>${d.name}</h3>
            <p><strong>Код МКБ-10:</strong> ${d.code ?? "-"}</p>
            <p><strong>Все симптомы:</strong> ${d.all_symptoms ?? "-"}</p>
            <p><strong>Описание:</strong><br>${
              d.description_full || d.description || "-"
            }</p>
            <p><strong>Диагностика / Лечение:</strong><br>${
              d.treatment_diagnosis ?? "-"
            }</p>
          `;
          document.getElementById("modal").style.display = "block";
        });

        nameTd.appendChild(nameDiv);
        nameTd.appendChild(moreLink);
        row.appendChild(nameTd);

        // Остальные ячейки
        const codeTd = document.createElement("td");
        codeTd.innerHTML = `<div class="cell-text">${d.code ?? "-"}</div>`;
        row.appendChild(codeTd);

        const descTd = document.createElement("td");
        descTd.innerHTML = `<div class="cell-text">${
          d.description ?? "-"
        }</div>`;
        row.appendChild(descTd);

        const matchedTd = document.createElement("td");
        matchedTd.innerHTML = `<div class="cell-text">${
          d.matched_symptoms ?? "-"
        }</div>`;
        row.appendChild(matchedTd);

        const treatmentTd = document.createElement("td");
        treatmentTd.innerHTML = `<div class="cell-text">${
          d.treatment_diagnosis ?? "-"
        }</div>`;
        row.appendChild(treatmentTd);

        const probTd = document.createElement("td");
        probTd.style.fontWeight = "600";
        probTd.style.color = probColor;
        probTd.innerHTML = `<div class="cell-text">${d.probability}%</div>`;
        row.appendChild(probTd);

        tbody.appendChild(row);
      });
    })
    .catch((err) => {
      console.error(err);
      const row = document.createElement("tr");
      row.innerHTML = `<td colspan="6">Ошибка загрузки</td>`;
      tbody.appendChild(row);
    });
}

// Очистка
const clearBtn = document.getElementById("clear-btn");
clearBtn.addEventListener("click", () => {
  const tbody = document.querySelector(".results-table tbody");
  tbody.innerHTML = "";
  input.value = "";
  suggestions.innerHTML = "";
  suggestions.style.display = "none";
});

// Закрытие модалки
document.querySelector(".modal .close").onclick = function () {
  document.getElementById("modal").style.display = "none";
};
window.onclick = function (event) {
  if (event.target == document.getElementById("modal")) {
    document.getElementById("modal").style.display = "none";
  }
};
