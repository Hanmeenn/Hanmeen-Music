<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход | Hanmeen Music</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="header-btn back-btn">Главная</a>
        <h1 class="logo">HANMEEN MUSIC</h1>
        <div class="header-buttons">
            <a href="register.php" class="header-btn">Регистрация</a>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">🔐 Вход в аккаунт</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">Неверный email или пароль!</div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <input class="input-field" type="email" name="email" placeholder="Ваш Email" required>
                <input class="input-field" type="password" name="password" placeholder="Пароль" required>
                <button type="submit" class="btn submit-btn">Продолжить</button>
            </form>
            <div style="text-align: center; margin-top: 25px;">
                <p>Нет аккаунта? <a href="register.php" style="color: var(--accent);">Создать аккаунт</a></p>
            </div>
        </div>
    </div>
</body>
</html>