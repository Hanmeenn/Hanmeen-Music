<?php
session_start();
require 'db.php';
require 'tracks_data.php';

$tracks = getTracks($pdo);
$currentUser = $_SESSION['user']['id'] ?? null;

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>HANMEEN MUSIC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: fadeInOut 3.5s forwards;
        }
        
        .success {
            background-color: #4CAF50;
        }
        
        .error {
            background-color: #f44336;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; top: 0; }
            10% { opacity: 1; top: 20px; }
            90% { opacity: 1; top: 20px; }
            100% { opacity: 0; top: 0; }
        }

        /* Стили для мини-плеера */
        .mini-player {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mini-play-btn {
            background: var(--accent);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .mini-progress-bar {
            flex-grow: 1;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            cursor: pointer;
        }
        
        .mini-progress {
            height: 100%;
            background: var(--highlight);
            border-radius: 2px;
            width: 0%;
        }
        
        .mini-time {
            font-size: 12px;
            color: var(--gray);
            min-width: 60px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php if (!empty($successMessage)): ?>
        <div class="notification success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="notification error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <header class="header">
        <a href="index.php" class="header-btn back-btn">Главная</a>
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

    <div class="track-list">
        <?php if (empty($tracks)): ?>
            <div class="empty-state">
                <p>Пока нет ни одного бита</p>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="add_track.php" class="btn add-bit-btn">Добавить первый бит</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Войти и добавить бит</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($tracks as $track): ?>
                <?php if (is_array($track)): ?>
                    <?php $isOwner = isset($_SESSION['user']) && $_SESSION['user']['id'] == $track['user_id']; ?>
                    
                    <div class="track-card horizontal-layout">
                        <div class="track-image-container">
                            <?php if (!empty($track['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($track['image']) ?>" alt="<?= htmlspecialchars($track['title']) ?>" class="track-image">
                            <?php else: ?>
                                <div class="image-placeholder">Нет обложки</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="track-content">
                            <div class="track-header">
                                <div>
                                    <h3 class="track-title"><?= htmlspecialchars($track['title'] ?? 'Без названия') ?></h3>
                                    <p class="author"><?= htmlspecialchars($track['author'] ?? 'Автор не указан') ?></p>
                                </div>
                            </div>
                            
                            <div class="track-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Тон:</span>
                                    <span class="meta-value"><?= htmlspecialchars($track['note'] ?? '?') ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Лад:</span>
                                    <span class="meta-value"><?= htmlspecialchars($track['scale_type'] ?? '') ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">BPM:</span>
                                    <span class="meta-value"><?= htmlspecialchars($track['bpm'] ?? '0') ?></span>
                                </div>
                            </div>
                            
                            <!-- Мини-плеер для трека -->
                            <div class="mini-player" data-audio="uploads/<?= htmlspecialchars($track['audio']) ?>">
                                <button class="mini-play-btn">▶</button>
                                <div class="mini-progress-bar">
                                    <div class="mini-progress"></div>
                                </div>
                                <div class="mini-time">
                                    <span class="mini-current-time">0:00</span>
                                </div>
                                <audio preload="metadata" class="audio-element">
                                    <source src="uploads/<?= htmlspecialchars($track['audio']) ?>" type="audio/mpeg">
                                </audio>
                            </div>
                            
                            <div class="track-actions">
                                <a href="track.php?id=<?= htmlspecialchars($track['id']) ?>" class="action-btn details-btn">
                                    <i class="fas fa-info-circle"></i> Подробнее
                                </a>
                                
                                <?php if ($isOwner): ?>
                                    <a href="edit_track.php?id=<?= htmlspecialchars($track['id']) ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Редактировать
                                    </a>
                                    <a href="delete_track.php?id=<?= htmlspecialchars($track['id']) ?>" 
                                       class="action-btn delete-btn"
                                       onclick="return confirm('Вы уверены, что хотите удалить этот бит?')">
                                        <i class="fas fa-trash-alt"></i> Удалить
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Функция для форматирования времени
        function formatTime(seconds) {
            if (isNaN(seconds)) return "0:00";
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec < 10 ? '0' : ''}${sec}`;
        }

        // Обработка мини-плееров
        document.querySelectorAll('.mini-player').forEach(player => {
            const playBtn = player.querySelector('.mini-play-btn');
            const progressBar = player.querySelector('.mini-progress-bar');
            const progress = player.querySelector('.mini-progress');
            const currentTimeEl = player.querySelector('.mini-current-time');
            const audio = player.querySelector('.audio-element');
            
            // Воспроизведение/пауза
            playBtn.addEventListener('click', () => {
                if (audio.paused) {
                    // Останавливаем все другие аудио
                    document.querySelectorAll('.audio-element').forEach(a => {
                        if (a !== audio) {
                            a.pause();
                            a.currentTime = 0;
                            const otherPlayer = a.closest('.mini-player');
                            otherPlayer.querySelector('.mini-play-btn').textContent = '▶';
                            otherPlayer.querySelector('.mini-progress').style.width = '0%';
                            otherPlayer.querySelector('.mini-current-time').textContent = '0:00';
                        }
                    });
                    
                    audio.play()
                        .then(() => {
                            playBtn.textContent = '⏸';
                        })
                        .catch(e => {
                            console.error('Ошибка воспроизведения:', e);
                        });
                } else {
                    audio.pause();
                    playBtn.textContent = '▶';
                }
            });
            
            // Обновление прогресса
            audio.addEventListener('timeupdate', () => {
                const percent = (audio.currentTime / audio.duration) * 100;
                progress.style.width = `${percent}%`;
                currentTimeEl.textContent = formatTime(audio.currentTime);
            });
            
            // Перемотка по клику на прогресс-бар
            progressBar.addEventListener('click', (e) => {
                const rect = progressBar.getBoundingClientRect();
                const pos = (e.clientX - rect.left) / rect.width;
                audio.currentTime = pos * audio.duration;
            });
            
            // Сброс при окончании трека
            audio.addEventListener('ended', () => {
                playBtn.textContent = '▶';
                progress.style.width = '0%';
                currentTimeEl.textContent = '0:00';
                audio.currentTime = 0;
            });
        });
    </script>
</body>
</html>