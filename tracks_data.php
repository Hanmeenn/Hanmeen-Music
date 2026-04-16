<?php
require 'db.php';

function getTracks($pdo) {
    $stmt = $pdo->query("
        SELECT tracks.*, users.email AS user_email 
        FROM tracks 
        JOIN users ON tracks.user_id = users.id
        ORDER BY tracks.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrackById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT tracks.*, users.email AS user_email 
        FROM tracks 
        JOIN users ON tracks.user_id = users.id
        WHERE tracks.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveTrack($pdo, $data) {
    if (empty($data['id'])) {
        $stmt = $pdo->prepare("
            INSERT INTO tracks (
                user_id, title, author, author_insta, author_tg, 
                image, audio, bpm, note, scale_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['author'],
            $data['author_insta'] ?? '',
            $data['author_tg'] ?? '',
            $data['image'] ?? '',
            $data['audio'],
            $data['bpm'],
            $data['note'],
            $data['scale_type']
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE tracks SET
                title = ?,
                author = ?,
                author_insta = ?,
                author_tg = ?,
                image = ?,
                audio = ?,
                bpm = ?,
                note = ?,
                scale_type = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['author'],
            $data['author_insta'] ?? '',
            $data['author_tg'] ?? '',
            $data['image'] ?? '',
            $data['audio'],
            $data['bpm'],
            $data['note'],
            $data['scale_type'],
            $data['id']
        ]);
    }
}

function deleteTrack($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM tracks WHERE id = ?");
    return $stmt->execute([$id]);
}
?>