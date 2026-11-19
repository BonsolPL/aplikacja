
<?php

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Nieprawidłowe żądanie.");
}

// Pobieranie danych z formularza

$email = trim($_POST["email"] ?? "");
$code = trim($_POST["registrationCode"] ?? "");
$class = $_POST["class"] ?? "";
$password = $_POST["password"] ?? "";
$confirmation = $_POST["confirmation"] ?? "";

// Sprawdzanie danych

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Niepoprawny adres e-mail.");
}

if ($code === "") {
    exit("Podaj kod rejestracyjny.");
}

if (!in_array($class, ["1", "2", "3", "4", "5"], true)) {
    exit("Nieprawidłowa klasa.");
}

if (strlen($password) < 8) {
    exit("Hasło musi mieć minimum 8 znaków.");
}

if ($password !== $confirmation) {
    exit("Hasła nie są identyczne.");
}

// Zapisywanie konta w bazie danych

try {

    $sql = "INSERT INTO users
            (email, registration_code, class, password)
            VALUES
            (:email, :code, :class, :password)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email,
        ":code" => $code,
        ":class" => $class,
        ":password" => $password
    ]);

    echo "
    <h2>Konto zostało utworzone!</h2>
    <p>Możesz teraz zalogować się do dziennika.</p>
    <a href='logowanie.html'>Przejdź do logowania</a>
    ";

} catch (PDOException $e) {

    if ($e->getCode() == 23000) {
        echo "Konto z takim adresem e-mail już istnieje.";
    } else {
        echo "Wystąpił błąd podczas rejestracji.";
    }

}

?>
