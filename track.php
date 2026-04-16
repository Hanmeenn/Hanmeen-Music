<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';
require 'tracks_data.php';

$trackId = $_GET['id'] ?? 0;
$track = getTrackById($pdo, $trackId);

if (!$track) {
    header('Location: index.php');
    exit;
}

$currentUser = $_SESSION['user']['id'] ?? null;
$isOwner = $currentUser && $currentUser == $track['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($track['title']) ?> | Hanmeen Music</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="header-btn back-btn">← Назад</a>
        <h1 class="logo">HANMEEN MUSIC</h1>
        <div class="header-buttons">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="add_track.php" class="header-btn add-bit-btn">+ Добавить бит</a>
                <a href="logout.php" class="header-btn">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="header-btn">Войти</a>
                <a href="register.php" class="header-btn">Регистрация</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="track-details horizontal-layout compact">
        <div class="track-image-container">
            <?php if (!empty($track['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($track['image']) ?>" class="track-image" alt="<?= htmlspecialchars($track['title']) ?>">
            <?php else: ?>
                <div class="image-placeholder">Нет обложки</div>
            <?php endif; ?>
        </div>
        
        <div class="track-content">
            <div class="track-header">
                <h2><?= htmlspecialchars($track['title']) ?></h2>
                <p class="author"><?= htmlspecialchars($track['author'] ?? 'Неизвестный автор') ?></p>
            </div>
            
            <div class="track-meta">
                <div class="meta-item">
                    <span class="meta-label">Тон:</span>
                    <span class="meta-value"><?= htmlspecialchars($track['note'] ?? '?') ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Лад:</span>
                    <span class="meta-value"><?= htmlspecialchars($track['scale_type'] ?? '?') ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">BPM:</span>
                    <span class="meta-value"><?= htmlspecialchars($track['bpm'] ?? '0') ?></span>
                </div>
            </div>

            <!-- Плеер для прослушивания -->
            <div class="player-container">
                <button class="play-btn" onclick="togglePlay()">▶</button>
                <div class="progress-bar" onclick="seek(event)">
                    <div class="progress" id="progress"></div>
                </div>
                <div class="time">
                    <span id="currentTime">0:00</span>
                    <span id="duration">0:00</span>
                </div>
                <audio id="audio-element" preload="metadata">
                    <source src="uploads/<?= htmlspecialchars($track['audio']) ?>" type="audio/mpeg">
                    Ваш браузер не поддерживает аудио элементы.
                </audio>
            </div>

            <div class="social-links">
                <?php if (!empty($track['author_insta'])): ?>
                    <a href="https://instagram.com/<?= htmlspecialchars($track['author_insta']) ?>" class="social-btn" target="_blank">
                        📸 Instagram
                    </a>
                <?php endif; ?>
                <?php if (!empty($track['author_tg'])): ?>
                    <a href="https://t.me/<?= htmlspecialchars($track['author_tg']) ?>" class="social-btn" target="_blank">
                        ✉️ Telegram
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($isOwner): ?>
                <div class="track-actions">
                    <a href="edit_track.php?id=<?= $track['id'] ?>" class="action-btn edit-btn">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <a href="delete_track.php?id=<?= $track['id'] ?>" 
                       class="action-btn delete-btn"
                       onclick="return confirm('Вы уверены, что хотите удалить этот бит?')">
                        <i class="fas fa-trash-alt"></i> Удалить
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const audio = document.getElementById('audio-element');
        const playBtn = document.querySelector('.play-btn');
        const progress = document.getElementById('progress');
        const currentTimeEl = document.getElementById('currentTime');
        const durationEl = document.getElementById('duration');

        function togglePlay() {
            if (audio.paused) {
                audio.play()
                    .then(() => {
                        playBtn.textContent = '⏸';
                    })
                    .catch(e => {
                        alert('Ошибка воспроизведения: ' + e.message);
                    });
            } else {
                audio.pause();
                playBtn.textContent = '▶';
            }
        }

        audio.addEventListener('timeupdate', () => {
            const percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = `${percent}%`;
            currentTimeEl.textContent = formatTime(audio.currentTime);
        });

        audio.addEventListener('loadedmetadata', () => {
            durationEl.textContent = formatTime(audio.duration);
        });

        audio.addEventListener('error', (e) => {
            console.error('Audio error:', audio.error);
            alert('Ошибка загрузки аудио: ' + (audio.error ? audio.error.message : 'Неизвестная ошибка'));
        });

        function seek(event) {
            const bar = event.currentTarget;
            const rect = bar.getBoundingClientRect();
            const pos = (event.clientX - rect.left) / bar.offsetWidth;
            audio.currentTime = pos * audio.duration;
        }

        function formatTime(seconds) {
            if (isNaN(seconds)) return "0:00";
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec < 10 ? '0' : ''}${sec}`;
        }
    </script>
</body>
</html>