
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
<title>Plan lekcji</title>


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

<h1>Plan lekcji</h1>

<p id="today"></p>

<button onclick="loadSchedule()">Odśwież plan</button>
<a href="zastepstwa.php">Panel zastępstw</a>
<a href="historia.php">Historia zmian</a>

<p id="status">Pobieranie planu lekcji...</p>

<label>Data planu:</label>
<input type="date" id="date" onchange="loadSchedule()">

<div id="schedule"></div>

<script>

const DAYS = [
    "Poniedziałek",
    "Wtorek",
    "Środa",
    "Czwartek",
    "Piątek"
];

const CLASSES = ["1A", "2A", "3A", "4A", "5A"];

function todayDate() {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 10);
}

document.getElementById("date").value = todayDate();

document.getElementById("today").textContent =
    new Date().toLocaleDateString("pl-PL", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
    });

async function loadSchedule() {
    const status = document.getElementById("status");
    const container = document.getElementById("schedule");

    status.textContent = "Pobieranie planu...";
    
    try {
        const date = document.getElementById("date").value;

        const response = await fetch(
            "api.php?date=" + encodeURIComponent(date)
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Błąd pobierania planu.");
        }

        renderSchedule(data.schedule);

        status.textContent =
            "Ostatnia aktualizacja: " +
            new Date().toLocaleTimeString("pl-PL");

    } catch (error) {
        status.textContent = error.message;
        container.textContent = "";
    }
}

function renderSchedule(schedule) {
    const container = document.getElementById("schedule");
    container.innerHTML = "";

    DAYS.forEach(day => {
        const lessons = schedule.filter(item => item.day === day);

        if (!lessons.length) return;

        const section = document.createElement("section");
        const heading = document.createElement("h2");

        heading.textContent = day;
        section.appendChild(heading);

        const table = document.createElement("table");
        table.border = "1";
        table.cellPadding = "5";

        const header = document.createElement("tr");

        ["Godzina", ...CLASSES].forEach(name => {
            const th = document.createElement("th");
            th.textContent = name;
            header.appendChild(th);
        });

        table.appendChild(header);

        lessons.forEach(lesson => {
            const tr = document.createElement("tr");

            const time = document.createElement("td");
            time.textContent = lesson.time;
            tr.appendChild(time);

            lesson.lessons.forEach(subject => {
                const td = document.createElement("td");
                const text = (subject || "").trim();

                if (text === "ODWOŁANA LEKCJA") {
                    td.textContent = text;
                    td.style.color = "red";
                    td.style.fontWeight = "bold";
                } else {
                    text.split(/\r?\n/).forEach((line, index) => {
                        if (index > 0) {
                            td.appendChild(document.createElement("br"));
                        }

                        td.appendChild(document.createTextNode(line));
                    });
                }

                tr.appendChild(td);
            });

            if (isCurrentLesson(day, lesson.time)) {
                tr.style.backgroundColor = "lightgreen";
            }

            table.appendChild(tr);
        });

        section.appendChild(table);
        container.appendChild(section);
    });
}

function isCurrentLesson(day, time) {
    const now = new Date();
    const todayIndex = (now.getDay() + 6) % 7;

    if (DAYS[todayIndex] !== day) return false;

    const match = time.match(
        /(\d{1,2}):(\d{2})\s*[-–]\s*(\d{1,2}):(\d{2})/
    );

    if (!match) return false;

    const [, h1, m1, h2, m2] = match;

    const current = now.getHours() * 60 + now.getMinutes();
    const start = Number(h1) * 60 + Number(m1);
    const end = Number(h2) * 60 + Number(m2);

    return current >= start && current < end;
}

loadSchedule();

setInterval(loadSchedule, 60000);

</script>
</body>
</html>
