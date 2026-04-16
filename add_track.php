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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    
    // Создаем папку для загрузок, если ее нет
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newTrack = [
        'user_id' => $_SESSION['user']['id'],
        'title' => $_POST['title'] ?? 'Без названия',
        'author' => $_POST['author'] ?? 'Неизвестный автор',
        'author_insta' => $_POST['author_insta'] ?? '',
        'author_tg' => $_POST['author_tg'] ?? '',
        'image' => '',
        'audio' => '',
        'bpm' => (int)($_POST['bpm'] ?? 0),
        'note' => $_POST['note'] ?? 'C',
        'scale_type' => $_POST['scale_type'] ?? 'Major'
    ];

    // Обработка загрузки файлов
    try {
        // Загрузка изображения
        if (!empty($_FILES['image']['tmp_name'])) {
            $imageExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $imageExt;
            $imagePath = $uploadDir . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $newTrack['image'] = $imageName;
            } else {
                throw new Exception("Ошибка загрузки изображения");
            }
        }

        // Загрузка аудио (обязательное поле)
        if (empty($_FILES['audio']['tmp_name'])) {
            throw new Exception("Аудио файл обязателен");
        }
        
        $audioExt = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
        $audioName = uniqid() . '.' . $audioExt;
        $audioPath = $uploadDir . $audioName;
        
        if (move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath)) {
            $newTrack['audio'] = $audioName;
        } else {
            // Удаляем изображение, если аудио не загрузилось
            if (!empty($newTrack['image'])) {
                unlink($uploadDir . $newTrack['image']);
            }
            throw new Exception("Ошибка загрузки аудио");
        }

        // Сохраняем в базу
        if (saveTrack($pdo, $newTrack)) {
            $_SESSION['success'] = "Бит успешно добавлен!";
            header('Location: index.php');
            exit;
        } else {
            // Удаляем загруженные файлы при ошибке базы
            if (!empty($newTrack['image'])) {
                unlink($uploadDir . $newTrack['image']);
            }
            if (!empty($newTrack['audio'])) {
                unlink($uploadDir . $newTrack['audio']);
            }
            throw new Exception("Ошибка сохранения в базу данных");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: add_track.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Добавить бит</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <header class="header">
        <a href="index.php" class="header-btn back-btn">Главная</a>
        <h1 class="logo">HANMEEN MUSIC</h1>
        <div class="header-buttons">
            <a href="add_track.php" class="header-btn add-bit-btn">+ Добавить бит</a>
            <a href="logout.php" class="header-btn">Выйти</a>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">🎵 Добавить новый бит</h2>
            <form method="POST" enctype="multipart/form-data">
                <input class="input-field" type="text" name="title" placeholder="Название бита" required>
                <input class="input-field" type="text" name="author" placeholder="Ваш никнейм" required>
                <input class="input-field" type="text" name="author_insta" placeholder="Instagram (пример: hanmeenn)">
                <input class="input-field" type="text" name="author_tg" placeholder="Telegram (пример: @hanmeen)">
                
                <div class="params-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <select class="input-field" name="note" required style="flex: 1;">
                        <option value="C">C (До)</option>
                        <option value="C#">C# (До#)</option>
                        <option value="D">D (Ре)</option>
                        <option value="D#">D# (Ре#)</option>
                        <option value="E">E (Ми)</option>
                        <option value="F">F (Фа)</option>
                        <option value="F#">F# (Фа#)</option>
                        <option value="G">G (Соль)</option>
                        <option value="G#">G# (Соль#)</option>
                        <option value="A">A (Ля)</option>
                        <option value="A#">A# (Ля#)</option>
                        <option value="B">B (Си)</option>
                    </select>

                    <select class="input-field" name="scale_type" required style="flex: 1;">
                        <option value="Major">Мажор (Major)</option>
                        <option value="Minor">Минор (Minor)</option>
                        <option value="Harmonic Minor">Гармонический минор</option>
                        <option value="Melodic Minor">Мелодический минор</option>
                        <option value="Dorian">Дорийский (Dorian)</option>
                        <option value="Phrygian">Фригийский (Phrygian)</option>
                        <option value="Lydian">Лидийский (Lydian)</option>
                        <option value="Mixolydian">Миксолидийский (Mixolydian)</option>
                        <option value="Locrian">Локрийский (Locrian)</option>
                        <option value="Blues Major">Блюзовый мажор</option>
                        <option value="Blues Minor">Блюзовый минор</option>
                        <option value="Pentatonic Major">Пентатоника мажор</option>
                        <option value="Pentatonic Minor">Пентатоника минор</option>
                    </select>
                </div>

                <input class="input-field" type="number" name="bpm" placeholder="BPM" min="0" required>
                
                <div class="file-upload">
                    <label>Обложка бита:</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <div class="file-upload">
                    <label>Аудио файл (MP3, WAV, OGG):</label>
                    <input type="file" name="audio" accept="audio/*" required>
                </div>
                
                <button type="submit" class="action-btn edit-btn" style="margin-top: 20px;">
                    <span class="action-icon">🚀</span>
                    <span>Опубликовать бит</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>