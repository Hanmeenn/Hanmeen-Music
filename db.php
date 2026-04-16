<?php
$host = 'localhost';
$dbname = 'hanmeen_music';
$username = 'root'; // Замените на свои данные
$password = '';     // Замените на свои данные

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>