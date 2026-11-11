<?php
$host = 'localhost';
$dbname = 'vulcan_dziennik';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Nie można połączyć się z bazą danych. Sprawdź, czy Apache i MySQL są uruchomione oraz czy zaimportowano database.sql.');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
 