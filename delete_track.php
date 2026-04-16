<?php
session_start();
require 'db.php';
require 'tracks_data.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$trackId = $_GET['id'] ?? 0;

// Проверка владельца
$track = getTrackById($pdo, $trackId);
if (!$track || $track['user_id'] != $_SESSION['user']['id']) {
    header('Location: index.php');
    exit;
}

// Удаляем файлы
$uploadDir = 'uploads/';
if ($track['image'] && file_exists($uploadDir . $track['image'])) {
    unlink($uploadDir . $track['image']);
}
if ($track['audio'] && file_exists($uploadDir . $track['audio'])) {
    unlink($uploadDir . $track['audio']);
}

// Удаляем из базы
if (deleteTrack($pdo, $trackId)) {
    $_SESSION['success'] = "Бит успешно удален!";
} else {
    $_SESSION['error'] = "Ошибка при удалении бита";
}

header('Location: index.php');
exit;
?>