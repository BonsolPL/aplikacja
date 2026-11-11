<?php
require 'db.php';
$message = isset($_GET['registered']) ? 'Konto zostało utworzone. Możesz się zalogować.' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, email, password, class_name, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        header('Location: panel.php');
        exit;
    }
    $message = 'Nieprawidłowy e-mail lub hasło.';
}
?>
<!DOCTYPE html> 
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Logowanie – Vulcan++</title>
        <link rel="stylesheet" href="wyglad.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h1>Dziennik Vulcan++</h1>
                <nav>
                    <a href="glowna.php">Strona główna</a>
                    <a href="rejestracja.php">Rejestracja</a>
                </nav>
            </div>
        </header>
        <main>
            <section class="intro">
                <h2>Logowanie</h2>
                <p>Zaloguj się, aby zobaczyć plan lekcji.</p>
            </section>
            <form method="post" action="logowanie.php">
                <fieldset>
                    <legend>Zaloguj się</legend>
                    <label for="email">Adres e-mail</label>
                    <input id="email" name="email" type="email" required autocomplete="username">
                    <label for="password">Hasło</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="submit">Zaloguj</button>
                    <?php if($message): ?>
                        <p class="message"><?= htmlspecialchars($message) ?></p>
                    <?php endif; ?>
                </fieldset>
            </form>
        </main>
    </body>
</html>
 