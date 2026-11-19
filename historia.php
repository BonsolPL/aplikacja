
<?php
session_start();

if (empty($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

require_once "db.php";

$stmt = $pdo->query("
    SELECT * FROM historia_zmian
    ORDER BY created_at DESC
    LIMIT 300
");

$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historia zmian</title>
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

<h1>Historia zmian planu</h1>

<a href="index.php">Plan lekcji</a>
<a href="zastepstwa.php">Panel zastępstw</a>

<table border="1" cellpadding="6">
<tr>
    <th>Data</th>
    <th>Użytkownik</th>
    <th>Operacja</th>
    <th>Szczegóły</th>
</tr>

<?php foreach ($history as $item): ?>
<tr>
    <td><?= htmlspecialchars($item["created_at"]) ?></td>
    <td><?= htmlspecialchars($item["user_email"] ?? "") ?></td>
    <td><?= htmlspecialchars($item["operation"]) ?></td>
    <td><?= htmlspecialchars($item["details"] ?? "") ?></td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>
