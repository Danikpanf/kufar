<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Kufar</title>
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
                <h1>Вход в Kufar</h1>
                <p>Войдите в свой аккаунт</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">
                    Войти
                </button>
            </form>
            
            <div class="auth-links">
                <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                <p><a href="index.php">← Вернуться на главную</a></p>
            </div>
        </div>
    </div>
</body>
</html>