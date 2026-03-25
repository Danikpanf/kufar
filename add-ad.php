<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header("Location: login.php?redirect=add-ad.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0;
    $location = trim($_POST['location']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $is_urgent = isset($_POST['is_urgent']);
    
    // Валидация
    if (empty($title) || strlen($title) < 5) {
        $error = 'Заголовок должен содержать минимум 5 символов';
    } elseif (empty($description) || strlen($description) < 20) {
        $error = 'Описание должно содержать минимум 20 символов';
    } elseif (empty($category_id)) {
        $error = 'Выберите категорию';
    } elseif (empty($location)) {
        $error = 'Укажите местоположение';
    } elseif (empty($phone) && empty($email)) {
        $error = 'Укажите телефон или email для связи';
    } else {
        // Создаем объявление
        $ad_data = [
            'user_id' => $_SESSION['user_id'],
            'category_id' => $category_id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'location' => $location,
            'phone' => $phone,
            'email' => $email,
            'is_urgent' => $is_urgent
        ];
        
        $ad_id = createAd($ad_data);
        
        if ($ad_id) {
            // Загружаем изображения
            if (!empty($_FILES['images']['name'][0])) {
                $is_first = true;
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $tmp_name,
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        uploadImage($file, $ad_id, $is_first);
                        $is_first = false;
                    }
                }
            }
            
            $success = 'Объявление успешно добавлено!';
            header("Location: ad.php?id=$ad_id");
            exit;
        } else {
            $error = 'Ошибка при создании объявления';
        }
    }
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подать объявление - Kufar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .add-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 32px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            color: #212529;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-full {
            grid-column: 1 / -1;
        }
        
        .image-upload {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .image-upload:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        
        .preview-item {
            position: relative;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .preview-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }
        
        .remove-image {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .add-form {
                margin: 16px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <div class="logo-icon">K</div>
                        <span>Kufar</span>
                    </a>
                </div>
                <div class="user-actions">
                    <a href="index.php" class="login-link">← Назад</a>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="add-form">
                <h1 class="form-title">
                    <i class="fas fa-plus-circle"></i>
                    Подать объявление
                </h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Категория *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= e($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Цена (BYN)</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0"
                                   value="<?= isset($_POST['price']) ? e($_POST['price']) : '' ?>"
                                   placeholder="0 - договорная">
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label class="form-label">Заголовок *</label>
                        <input type="text" name="title" class="form-control" required maxlength="255"
                               value="<?= isset($_POST['title']) ? e($_POST['title']) : '' ?>"
                               placeholder="Краткое описание товара или услуги">
                    </div>
                    
                    <div class="form-group form-full">
                        <label class="form-label">Описание *</label>
                        <textarea name="description" class="form-control" rows="6" required
                                  placeholder="Подробное описание, состояние, особенности..."><?= isset($_POST['description']) ? e($_POST['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group form-full">
                        <label class="form-label">Фотографии</label>
                        <div class="image-upload" onclick="document.getElementById('images').click()">
                            <i class="fas fa-camera" style="font-size: 32px; color: #007bff; margin-bottom: 12px;"></i>
                            <p>Нажмите для выбора фотографий</p>
                            <p style="font-size: 12px; color: #6c757d;">Максимум 10 фотографий, до 5 МБ каждая</p>
                        </div>
                        <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Город *</label>
                            <input type="text" name="location" class="form-control" required
                                   value="<?= isset($_POST['location']) ? e($_POST['location']) : '' ?>"
                                   placeholder="Минск, Гомель, Брест...">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= isset($_POST['phone']) ? e($_POST['phone']) : '' ?>"
                                   placeholder="+375 (29) 123-45-67">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email для связи</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>"
                               placeholder="example@mail.com">
                    </div>
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="is_urgent" name="is_urgent" 
                               <?= isset($_POST['is_urgent']) ? 'checked' : '' ?>>
                        <label for="is_urgent">Срочное объявление</label>
                    </div>
                    
                    <div style="margin-top: 32px; text-align: center;">
                        <button type="submit" class="btn-primary" style="padding: 16px 32px; font-size: 16px;">
                            <i class="fas fa-paper-plane"></i>
                            Опубликовать объявление
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Обработка загрузки изображений
        const imageInput = document.getElementById('images');
        const imagePreview = document.getElementById('image-preview');
        let selectedFiles = [];
        
        imageInput.addEventListener('change', handleFiles);
        
        function handleFiles(e) {
            const files = Array.from(e.target.files);
            selectedFiles = files.slice(0, 10); // Максимум 10 файлов
            updateImagePreview();
        }
        
        function updateImagePreview() {
            imagePreview.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeImage(${index})">×</button>
                    `;
                    imagePreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
        
        function removeImage(index) {
            selectedFiles.splice(index, 1);
            updateImagePreview();
            updateFileInput();
        }
        
        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }
    </script>
</body>
</html>