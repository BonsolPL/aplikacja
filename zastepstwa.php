
<?php
session_start();

if (empty($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel zastępstw</title>
<style>
    button {
        background-color: #2563eb;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    button:hover {
        background-color: #1d4ed8;
    }

    a {
        display: inline-block;
        background-color: #2563eb;
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 5px;
        margin: 5px;
    }

    a:hover {
        background-color: #1d4ed8;
    }
</style>
</head>
<body>

<h1>Panel zastępstw</h1>

<a href="index.php">Powrót do planu</a>
<a href="historia.php">Historia zmian</a>

<p id="status"></p>

<h2>Dodaj zastępstwo lub odwołaj lekcję</h2>

<form id="changeForm">

    <p>Data:</p>
    <input type="date" id="date" required>

    <p>Dzień:</p>
    <select id="day">
        <option>Poniedziałek</option>
        <option>Wtorek</option>
        <option>Środa</option>
        <option>Czwartek</option>
        <option>Piątek</option>
    </select>

    <p>Klasa:</p>
    <select id="class">
        <option>1A</option>
        <option>2A</option>
        <option>3A</option>
        <option>4A</option>
        <option>5A</option>
    </select>

    <p>Godzina:</p>
    <select id="time">
        <option>08:00–08:45</option>
        <option>08:55–09:40</option>
        <option>09:50–10:35</option>
        <option>10:45–11:30</option>
        <option>11:40–12:25</option>
        <option>12:35–13:20</option>
        <option>13:30–14:15</option>
    </select>

    <p>Rodzaj:</p>
    <select id="type" onchange="showFields()">
        <option>Zastępstwo</option>
        <option>Odwołanie</option>
    </select>

    <div id="fields">
        <p>Nowy przedmiot:</p>
        <input type="text" id="subject">

        <p>Nowy nauczyciel:</p>
        <input type="text" id="teacher">
    </div>

    <p>Powód:</p>
    <input type="text" id="reason">

    <p><button type="submit">Zapisz zmianę</button></p>

</form>

<hr>

<h2>Edycja normalnego planu</h2>

<form id="editForm">

    <p>Dzień:</p>
    <select id="editDay">
        <option>Poniedziałek</option>
        <option>Wtorek</option>
        <option>Środa</option>
        <option>Czwartek</option>
        <option>Piątek</option>
    </select>

    <p>Klasa:</p>
    <select id="editClass">
        <option>1A</option>
        <option>2A</option>
        <option>3A</option>
        <option>4A</option>
        <option>5A</option>
    </select>

    <p>Godzina:</p>
    <select id="editTime">
        <option>08:00–08:45</option>
        <option>08:55–09:40</option>
        <option>09:50–10:35</option>
        <option>10:45–11:30</option>
        <option>11:40–12:25</option>
        <option>12:35–13:20</option>
        <option>13:30–14:15</option>
    </select>

    <p>Przedmiot:</p>
    <input type="text" id="editSubject" required>

    <p>Nauczyciel:</p>
    <input type="text" id="editTeacher" required>

    <p><button type="submit">Zapisz plan</button></p>

</form>

<p>
    <button type="button" onclick="resetEdit()">Przywróć lekcję z Google Sheets</button>
</p>

<hr>

<h2>Wprowadzone zastępstwa</h2>

<p>Wybierz datę:</p>
<input type="date" id="listDate">

<button onclick="loadChanges()">Pokaż zmiany</button>

<div id="changes"></div>

<script>

const status = document.getElementById("status");

function todayDate() {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 10);
}

document.getElementById("date").value = todayDate();
document.getElementById("listDate").value = todayDate();

function showFields() {
    document.getElementById("fields").style.display =
        document.getElementById("type").value === "Zastępstwo"
        ? "block" : "none";
}

async function send(data) {
    const response = await fetch("api.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(data)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Wystąpił błąd.");
    }

    return result;
}

document.getElementById("changeForm").onsubmit = async function(e) {
    e.preventDefault();

    const data = {
        action: "add",
        date: document.getElementById("date").value,
        day: document.getElementById("day").value,
        class: document.getElementById("class").value,
        time: document.getElementById("time").value,
        type: document.getElementById("type").value,
        subject: document.getElementById("subject").value,
        teacher: document.getElementById("teacher").value,
        reason: document.getElementById("reason").value
    };

    try {
        const result = await send(data);
        status.textContent = result.message;
        this.reset();
        document.getElementById("date").value = data.date;
        showFields();
        loadChanges();
    } catch (error) {
        status.textContent = error.message;
    }
};

document.getElementById("editForm").onsubmit = async function(e) {
    e.preventDefault();

    const data = {
        action: "edit",
        day: document.getElementById("editDay").value,
        class: document.getElementById("editClass").value,
        time: document.getElementById("editTime").value,
        subject: document.getElementById("editSubject").value,
        teacher: document.getElementById("editTeacher").value
    };

    try {
        const result = await send(data);
        status.textContent = result.message;
        loadChanges();
    } catch (error) {
        status.textContent = error.message;
    }
};

async function resetEdit() {
    const data = {
        action: "resetEdit",
        day: document.getElementById("editDay").value,
        class: document.getElementById("editClass").value,
        time: document.getElementById("editTime").value
    };

    if (!confirm("Przywrócić oryginalną lekcję?")) return;

    try {
        const result = await send(data);
        status.textContent = result.message;
    } catch (error) {
        status.textContent = error.message;
    }
}

async function loadChanges() {
    const date = document.getElementById("listDate").value;

    try {
        const response = await fetch(
            "api.php?date=" + encodeURIComponent(date)
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message);
        }

        const place = document.getElementById("changes");
        place.innerHTML = "";

        if (!result.changes.length) {
            place.textContent = "Brak zmian w tym dniu.";
            return;
        }

        const table = document.createElement("table");
        table.border = "1";
        table.cellPadding = "5";

        const header = document.createElement("tr");

        ["Klasa", "Godzina", "Rodzaj", "Przedmiot", "Nauczyciel", "Powód", "Akcja"]
            .forEach(name => {
                const th = document.createElement("th");
                th.textContent = name;
                header.appendChild(th);
            });

        table.appendChild(header);

        result.changes.forEach(change => {
            const tr = document.createElement("tr");

            [
                change.class_name,
                change.lesson_time,
                change.type,
                change.subject,
                change.teacher,
                change.reason
            ].forEach(value => {
                const td = document.createElement("td");
                td.textContent = value || "-";
                tr.appendChild(td);
            });

            const td = document.createElement("td");
            const button = document.createElement("button");

            button.textContent = "Usuń";

            button.onclick = async () => {
                if (!confirm("Usunąć tę zmianę?")) return;

                try {
                    const result = await send({
                        action: "delete",
                        id: change.id
                    });

                    status.textContent = result.message;
                    loadChanges();
                } catch (error) {
                    status.textContent = error.message;
                }
            };

            td.appendChild(button);
            tr.appendChild(td);
            table.appendChild(tr);
        });

        place.appendChild(table);

    } catch (error) {
        status.textContent = error.message;
    }
}

showFields();
loadChanges();

</script>

</body>
</html>
