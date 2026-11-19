
<?php

require_once "config.php";

function getGoogleSchedule() {
    $url = "https://sheets.googleapis.com/v4/spreadsheets/"
        . SPREADSHEET_ID
        . "/values/"
        . urlencode(SHEET_RANGE)
        . "?key=" . urlencode(GOOGLE_API_KEY);

    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15
    ]);

    $result = curl_exec($curl);
    $error = curl_error($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($result === false || $status !== 200) {
        throw new Exception("Nie udało się pobrać planu z Google Sheets: " . $error);
    }

    $data = json_decode($result, true);

    if (!isset($data["values"])) {
        throw new Exception("Arkusz nie zawiera danych.");
    }

    $schedule = [];
    $currentDay = "";

    foreach ($data["values"] as $row) {
        $first = trim($row[0] ?? "");

        if (in_array($first, DAYS, true)) {
            $currentDay = $first;
            continue;
        }

        if (!$currentDay || !in_array($first, HOURS, true)) {
            continue;
        }

        $lessons = [];

        for ($i = 1; $i <= 5; $i++) {
            $lessons[] = trim($row[$i] ?? "");
        }

        $schedule[] = [
            "day" => $currentDay,
            "time" => $first,
            "lessons" => $lessons
        ];
    }

    return $schedule;
}
