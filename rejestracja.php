<?php
require 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $code = trim($_POST['registrationCode'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirmation'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Podaj poprawny adres e-mail.';
    } elseif ($code !== 'SZKOLA2026') {
        $message = 'Nieprawidłowy kod rejestracyjny.';
    } elseif (!in_array($class, ['1','2','3','4','5'], true)) {
        $message = 'Wybierz klasę.';
    } elseif (strlen($pass) < 8) {
        $message = 'Hasło musi mieć co najmniej 8 znaków.';
    } elseif ($pass !== $confirm) {
        $message = 'Hasła nie są takie same.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (email, password, class_name) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $class]);
            header('Location: logowanie.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $message = $e->getCode() === '23000' ? 'Konto z tym adresem e-mail już istnieje.' : 'Nie udało się utworzyć konta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Rejestracja – Vulcan++</title>
        <link rel="stylesheet" href="wyglad.css">
    </head>
    <body>
        <header>
            <div class="container">
                <h1>Dziennik Vulcan++</h1>
                <nav>
                    <a href="glowna.php">Strona główna</a>
                    <a href="logowanie.php">Zaloguj</a>
                </nav>
            </div>
        </header>
        <main>
            <section class="intro">
                <h2>Utwórz konto</h2>
                <p>Zarejestruj się tutaj oraz wybierz klasę aby zobaczyć plan lekcji.</p>
            </section>
            <form method="post" action="rejestracja.php">
                <fieldset>
                    <legend>Rejestracja</legend>
                    <label for="email">Adres e-mail</label>
                    <input id="email" name="email" type="email" required autocomplete="email">
                    <label for="code">Kod rejestracyjny</label>
                    <input id="code" name="registrationCode" type="text" required>
                    <label for="class">Klasa</label>
                    <select id="class" name="class" required>
                        <option value="">Wybierz klasę</option>
                        <?php for($i=1;$i<=5;$i++): ?>
                            <option value="<?= $i ?>">Klasa <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="password">Hasło (minimum 8 znaków)</label>
                    <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
                    <label for="confirmation">Powtórz hasło</label>
                    <input id="confirmation" name="confirmation" type="password" minlength="8" required autocomplete="new-password">
                    <button type="submit">Utwórz konto</button>
                </fieldset>
            </form>
        </main>
    </body>
</html> 
