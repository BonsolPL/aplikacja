<?php
require 'db.php';
if (empty($_SESSION['user_id'])) { header('Location: logowanie.php'); exit; }
$stmt = $pdo->prepare('SELECT id,email,class_name,role,created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header('Location: logowanie.php'); exit; }
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Moje konto – Vulcan++</title>
        <link rel="stylesheet" href="wyglad.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h1>Dziennik Vulcan++</h1>
                <nav>
                    <a href="logout.php">Wyloguj</a>
                </nav>
            </div>
        </header>
        <main>
            <section class="intro">
                <h2>Witaj, <?= htmlspecialchars($user['email']) ?>!</h2>
                <p>Konto zostało poprawnie utworzone i odczytane z bazy danych MySQL.</p>
            </section>
            <section>
                <h2>Twoje dane</h2>
                <p><b>E-mail:</b> <?= htmlspecialchars($user['email']) ?></p>
                <p><b>Klasa:</b> <?= htmlspecialchars($user['class_name']) ?></p>
                <p><b>Rola:</b> <?= htmlspecialchars($user['role']) ?></p>
                <p><b>Utworzono:</b> <?= htmlspecialchars($user['created_at']) ?></p>
            </section>
        </main>
    </body>
</html>
 