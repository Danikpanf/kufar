<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Заполните все обязательные поля';
    } elseif (strlen($username) < 3) {
        $error = 'Имя пользователя должно содержать минимум 3 символа';
    } elseif (!isValidEmail($email)) {
        $error = 'Введите корректный email';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        // Проверяем уникальность
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Пользователь с таким email или именем уже существует';
        } else {
            // Создаем пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $phone])) {
                $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
            } else {
                $error = 'Ошибка при регистрации. Попробуйте еще раз.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Kufar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 32px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .auth-header h1 {
            color: #212529;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-links a {
            color: #007bff;
            text-decoration: none;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Регистрация</h1>
                <p>Создайте новый аккаунт</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Имя пользователя *</label>
                    <input type="text" name="username" class="form-control" required 
                           value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Телефон</label>
                    <input type="tel" name="phone" class="form-control" 
                           value="<?= isset($_POST['phone']) ? e($_POST['phone']) : '' ?>"
                           placeholder="+375 (29) 123-45-67">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Подтвердите пароль *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">
                    Зарегистрироваться
                </button>
            </form>
            
            <div class="auth-links">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
                <p><a href="index.php">← Вернуться на главную</a></p>
            </div>
        </div>
    </div>
</body>
</html>