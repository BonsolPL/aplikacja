
<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

$stmt = $pdo->prepare(
    "SELECT id, email FROM users
     WHERE email = :email AND password = :password
     LIMIT 1"
);

$stmt->execute([
    "email" => $email,
    "password" => $password
]);

$user = $stmt->fetch();

if ($user) {

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_email"] = $user["email"];

    echo json_encode([
        "success" => true
    ]);

} else {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Nieprawidłowy email lub hasło."
    ]);
}
