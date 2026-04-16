<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Регистрация
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        
        // Проверка существования email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: register.php?error=exists');
            exit;
        }
        
        // Хеширование пароля
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Создание пользователя
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword]);
        
        // Автовход после регистрации
        $_SESSION['user'] = [
            'id' => $pdo->lastInsertId(),
            'name' => $name,
            'email' => $email
        ];
        
        header('Location: index.php');
        exit;
    } 
    // Авторизация
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];
            header('Location: index.php');
            exit;
        } else {
            header('Location: login.php?error=1');
            exit;
        }
    }
}
?>