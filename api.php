
<?php

session_start();
header("Content-Type: application/json; charset=utf-8");

require_once "db.php";
require_once "config.php";
require_once "sheets.php";

function output($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function checkLogin() {
    if (empty($_SESSION["user_id"])) {
        output(["success" => false, "message" => "Zaloguj się ponownie."], 401);
    }
}

function logChange($pdo, $operation, $details) {
    $stmt = $pdo->prepare("
        INSERT INTO historia_zmian
        (operation, details, user_id, user_email)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $operation,
        $details,
        $_SESSION["user_id"],
        $_SESSION["user_email"] ?? ""
    ]);
}

function validateLesson($data) {
    if (
        !in_array($data["day"] ?? "", DAYS, true) ||
        !in_array($data["time"] ?? "", HOURS, true) ||
        !in_array($data["class"] ?? "", CLASSES, true)
    ) {
        output(["success" => false, "message" => "Nieprawidłowe dane lekcji."], 400);
    }
}

checkLogin();

try {

    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        $date = $_GET["date"] ?? date("Y-m-d");

        $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

        if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
            output(["success" => false, "message" => "Nieprawidłowa data."], 400);
        }

        $schedule = getGoogleSchedule();

        $edits = $pdo->query("SELECT * FROM plan_edits")->fetchAll();

        foreach ($edits as $edit) {
            foreach ($schedule as &$lesson) {
                if (
                    $lesson["day"] === $edit["day_name"] &&
                    $lesson["time"] === $edit["lesson_time"]
                ) {
                    $index = array_search($edit["class_name"], CLASSES, true);

                    if ($index !== false) {
                        $lesson["lessons"][$index] =
                            $edit["subject"] . "\n" . $edit["teacher"];
                    }
                }
            }
            unset($lesson);
        }

        $stmt = $pdo->prepare("
            SELECT * FROM zastepstwa
            WHERE lesson_date = ?
            ORDER BY id ASC
        ");

        $stmt->execute([$date]);

        $changes = $stmt->fetchAll();

        foreach ($changes as $change) {
            foreach ($schedule as &$lesson) {
                if (
                    $lesson["day"] === $change["day_name"] &&
                    $lesson["time"] === $change["lesson_time"]
                ) {
                    $index = array_search($change["class_name"], CLASSES, true);

                    if ($index !== false) {
                        if ($change["type"] === "Odwołanie") {
                            $lesson["lessons"][$index] = "ODWOŁANA LEKCJA";
                        } else {
                            $lesson["lessons"][$index] =
                                $change["subject"] . "\n" . $change["teacher"];
                        }
                    }
                }
            }
            unset($lesson);
        }

        output([
            "success" => true,
            "schedule" => $schedule,
            "changes" => $changes
        ]);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $action = $data["action"] ?? "";

        if ($action === "add") {

            validateLesson($data);

            $date = $data["date"] ?? "";
            $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
                output(["success" => false, "message" => "Nieprawidłowa data."], 400);
            }

            if (!in_array($data["type"] ?? "", ["Zastępstwo", "Odwołanie"], true)) {
                output(["success" => false, "message" => "Nieprawidłowy rodzaj zmiany."], 400);
            }

            if (
                $data["type"] === "Zastępstwo" &&
                (trim($data["subject"] ?? "") === "" || trim($data["teacher"] ?? "") === "")
            ) {
                output(["success" => false, "message" => "Wpisz przedmiot i nauczyciela."], 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO zastepstwa
                (lesson_date, day_name, lesson_time, class_name, type,
                 subject, teacher, reason, user_id, user_email)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $date,
                $data["day"],
                $data["time"],
                $data["class"],
                $data["type"],
                trim($data["subject"] ?? ""),
                trim($data["teacher"] ?? ""),
                trim($data["reason"] ?? ""),
                $_SESSION["user_id"],
                $_SESSION["user_email"] ?? ""
            ]);

            logChange($pdo, "Dodanie zmiany",
                "$date | {$data["day"]} | {$data["time"]} | {$data["class"]} | {$data["type"]}");

            output(["success" => true, "message" => "Zmiana została zapisana."]);
        }

        if ($action === "delete") {

            $id = (int)($data["id"] ?? 0);

            $stmt = $pdo->prepare("SELECT * FROM zastepstwa WHERE id = ?");
            $stmt->execute([$id]);
            $change = $stmt->fetch();

            if (!$change) {
                output(["success" => false, "message" => "Nie znaleziono zmiany."], 404);
            }

            $stmt = $pdo->prepare("DELETE FROM zastepstwa WHERE id = ?");
            $stmt->execute([$id]);

            logChange($pdo, "Usunięcie zmiany",
                $change["lesson_date"] . " | " . $change["class_name"] .
                " | " . $change["lesson_time"]);

            output(["success" => true, "message" => "Zmiana została usunięta."]);
        }

        if ($action === "edit") {

            validateLesson($data);

            $subject = trim($data["subject"] ?? "");
            $teacher = trim($data["teacher"] ?? "");

            if ($subject === "" || $teacher === "") {
                output(["success" => false, "message" => "Wpisz przedmiot i nauczyciela."], 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO plan_edits
                (day_name, lesson_time, class_name, subject, teacher, user_email)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                subject = VALUES(subject),
                teacher = VALUES(teacher),
                user_email = VALUES(user_email)
            ");

            $stmt->execute([
                $data["day"],
                $data["time"],
                $data["class"],
                $subject,
                $teacher,
                $_SESSION["user_email"] ?? ""
            ]);

            logChange($pdo, "Edycja planu",
                "{$data["day"]} | {$data["time"]} | {$data["class"]} | $subject | $teacher");

            output(["success" => true, "message" => "Plan został zmieniony."]);
        }

        if ($action === "resetEdit") {

            validateLesson($data);

            $stmt = $pdo->prepare("
                DELETE FROM plan_edits
                WHERE day_name = ? AND lesson_time = ? AND class_name = ?
            ");

            $stmt->execute([
                $data["day"],
                $data["time"],
                $data["class"]
            ]);

            logChange($pdo, "Przywrócenie planu",
                "{$data["day"]} | {$data["time"]} | {$data["class"]}");

            output(["success" => true, "message" => "Przywrócono plan z Google Sheets."]);
        }

        output(["success" => false, "message" => "Nieznana operacja."], 400);
    }

    output(["success" => false, "message" => "Nieobsługiwana metoda."], 405);

} catch (Throwable $e) {
    error_log($e->getMessage());
    output(["success" => false, "message" => "Wystąpił błąd serwera."], 500);
}
