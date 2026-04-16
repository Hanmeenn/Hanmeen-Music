<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';
require 'tracks_data.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$trackId = $_GET['id'] ?? 0;
$track = getTrackById($pdo, $trackId);

// Проверка владельца
if (!$track || $track['user_id'] != $_SESSION['user']['id']) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    
    // Подготовка данных
    $updatedTrack = [
        'id' => $trackId,
        'title' => $_POST['title'] ?? 'Без названия',
        'author' => $_POST['author'] ?? 'Неизвестный автор',
        'author_insta' => $_POST['author_insta'] ?? '',
        'author_tg' => $_POST['author_tg'] ?? '',
        'image' => $track['image'], // старое значение по умолчанию
        'audio' => $track['audio'], // старое значение по умолчанию
        'bpm' => (int)($_POST['bpm'] ?? 0),
        'note' => $_POST['note'] ?? 'C',
        'scale_type' => $_POST['scale_type'] ?? 'Major'
    ];

    try {
        // Обработка изображения
        if (!empty($_FILES['image']['tmp_name'])) {
            $imageExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $imageExt;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImageName)) {
                // Удаляем старое изображение
                if (!empty($track['image']) && file_exists($uploadDir . $track['image'])) {
                    unlink($uploadDir . $track['image']);
                }
                $updatedTrack['image'] = $newImageName;
            }
        }

        // Обработка аудио
        if (!empty($_FILES['audio']['tmp_name'])) {
            $audioExt = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
            $newAudioName = uniqid() . '.' . $audioExt;
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadDir . $newAudioName)) {
                // Удаляем старое аудио
                if (!empty($track['audio']) && file_exists($uploadDir . $track['audio'])) {
                    unlink($uploadDir . $track['audio']);
                }
                $updatedTrack['audio'] = $newAudioName;
            }
        }

        if (saveTrack($pdo, $updatedTrack)) {
            $_SESSION['success'] = "Бит успешно обновлен!";
            header('Location: index.php');
            exit;
        } else {
            throw new Exception("Ошибка при сохранении изменений");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_track.php?id=$trackId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактировать бит</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="header-btn back-btn">← Назад</a>
        <h1 class="logo">HANMEEN MUSIC</h1>
        <div class="header-buttons">
            <a href="add_track.php" class="header-btn add-bit-btn">+ Добавить бит</a>
            <a href="logout.php" class="header-btn">Выйти</a>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">✏️ Редактировать бит</h2>
            
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input class="input-field" type="text" name="title" placeholder="Название бита" 
                       value="<?= htmlspecialchars($track['title']) ?>" required>
                
                <input class="input-field" type="text" name="author" placeholder="Ваш никнейм" 
                       value="<?= htmlspecialchars($track['author']) ?>" required>
                
                <input class="input-field" type="text" name="author_insta" placeholder="Instagram (пример: hanmeenn)"
                       value="<?= htmlspecialchars($track['author_insta']) ?>">
                
                <input class="input-field" type="text" name="author_tg" placeholder="Telegram (пример: @hanmeen)"
                       value="<?= htmlspecialchars($track['author_tg']) ?>">
                
                <div class="params-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <select class="input-field" name="note" required style="flex: 1;">
                        <?php
                        $notes = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
                        foreach ($notes as $note): ?>
                            <option value="<?= $note ?>" <?= $track['note'] == $note ? 'selected' : '' ?>>
                                <?= $note ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select class="input-field" name="scale_type" required style="flex: 1;">
                        <?php
                        $scales = [
                            'Major', 'Minor', 'Harmonic Minor', 'Melodic Minor',
                            'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Locrian',
                            'Blues Major', 'Blues Minor', 'Pentatonic Major', 'Pentatonic Minor'
                        ];
                        foreach ($scales as $scale): ?>
                            <option value="<?= $scale ?>" <?= $track['scale_type'] == $scale ? 'selected' : '' ?>>
                                <?= $scale ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input class="input-field" type="number" name="bpm" placeholder="BPM" min="0"
                       value="<?= htmlspecialchars($track['bpm']) ?>" required>
                
                <div class="file-upload">
                    <label>Обложка бита (оставьте пустым, чтобы оставить текущую):</label>
                    <?php if (!empty($track['image'])): ?>
                        <div>Текущая: <?= htmlspecialchars($track['image']) ?></div>
                        <img src="uploads/<?= htmlspecialchars($track['image']) ?>" style="max-width: 200px; margin: 10px 0;">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <div class="file-upload">
                    <label>Аудио файл (оставьте пустым, чтобы оставить текущий):</label>
                    <?php if (!empty($track['audio'])): ?>
                        <div>Текущий: <?= htmlspecialchars($track['audio']) ?></div>
                        <audio controls src="uploads/<?= htmlspecialchars($track['audio']) ?>" style="width: 100%; margin: 10px 0;"></audio>
                    <?php endif; ?>
                    <input type="file" name="audio" accept="audio/*">
                </div>
                
                <button type="submit" class="action-btn edit-btn" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Сохранить изменения
                </button>
            </form>
        </div>
    </div>
</body>
</html>