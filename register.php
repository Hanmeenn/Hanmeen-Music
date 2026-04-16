<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация | Hanmeen Music</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="header-btn back-btn">Главная</a>
        <h1 class="logo">HANMEEN MUSIC</h1>
        <div class="header-buttons">
            <a href="login.php" class="header-btn">Войти</a>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">📝 Регистрация</h2>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'exists'): ?>
                <div class="error-message">Пользователь с таким email уже существует!</div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <input class="input-field" type="text" name="name" placeholder="Ваше имя" required>
                <input class="input-field" type="email" name="email" placeholder="Ваш Email" required>
                <input class="input-field" type="password" name="password" placeholder="Пароль" required>
                <button type="submit" class="btn submit-btn">Зарегистрироваться</button>
            </form>
            <div style="text-align: center; margin-top: 25px;">
                <p>Уже есть аккаунт? <a href="login.php" style="color: var(--accent);">Войти</a></p>
            </div>
        </div>
    </div>
</body>
</html>