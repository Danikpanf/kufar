<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Получаем данные для главной страницы
$categories = getCategories();
$latest_ads = getLatestAds(12);
$featured_ads = getFeaturedAds(6);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kufar - Доска объявлений Беларуси</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Шапка сайта -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Логотип -->
                <div class="logo">
                    <a href="index.php">
                        <div class="logo-icon">K</div>
                        <span>Kufar</span>
                    </a>
                </div>
                
                <!-- Поиск -->
                <div class="search-section">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Поиск товаров и услуг" class="search-input">
                        <select name="category" class="search-category">
                            <option value="">Все категории</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Действия пользователя -->
                <div class="user-actions">
                    <?php if(isLoggedIn()): ?>
                        <a href="profile.php" class="user-link">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <a href="add-ad.php" class="btn-add">
                            <i class="fas fa-plus"></i>
                            Подать объявление
                        </a>
                        <a href="logout.php" class="logout-link">Выйти</a>
                    <?php else: ?>
                        <a href="login.php" class="login-link">Войти</a>
                        <a href="register.php" class="register-link">Регистрация</a>
                        <a href="add-ad.php" class="btn-add">
                            <i class="fas fa-plus"></i>
                            Подать объявление
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Навигация по категориям -->
    <nav class="categories-nav">
        <div class="container">
            <ul class="categories-list">
                <?php foreach($categories as $category): ?>
                    <li>
                        <a href="category.php?id=<?= $category['id'] ?>">
                            <i class="<?= $category['icon'] ?>"></i>
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="main">
        <div class="container">
            <!-- Рекомендуемые объявления -->
            <?php if(!empty($featured_ads)): ?>
            <section class="featured-section">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    Рекомендуемые
                </h2>
                <div class="ads-grid">
                    <?php foreach($featured_ads as $ad): ?>
                        <div class="ad-card featured">
                            <div class="ad-image">
                                <img src="<?= getAdMainImage($ad['id']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>">
                                <div class="ad-badge">ТОП</div>
                                <button class="favorite-btn" data-ad-id="<?= $ad['id'] ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            <div class="ad-content">
                                <h3 class="ad-title">
                                    <a href="ad.php?id=<?= $ad['id'] ?>"><?= htmlspecialchars($ad['title']) ?></a>
                                </h3>
                                <div class="ad-price"><?= formatPrice($ad['price']) ?></div>
                                <div class="ad-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($ad['location']) ?>
                                </div>
                                <div class="ad-date"><?= formatDate($ad['created_at']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Последние объявления -->
            <section class="latest-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Последние объявления
                </h2>
                <div class="ads-grid">
                    <?php foreach($latest_ads as $ad): ?>
                        <div class="ad-card">
                            <div class="ad-image">
                                <img src="<?= getAdMainImage($ad['id']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>">
                                <?php if($ad['is_urgent']): ?>
                                    <div class="ad-badge urgent">СРОЧНО</div>
                                <?php endif; ?>
                                <button class="favorite-btn" data-ad-id="<?= $ad['id'] ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            <div class="ad-content">
                                <h3 class="ad-title">
                                    <a href="ad.php?id=<?= $ad['id'] ?>"><?= htmlspecialchars($ad['title']) ?></a>
                                </h3>
                                <div class="ad-price"><?= formatPrice($ad['price']) ?></div>
                                <div class="ad-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($ad['location']) ?>
                                </div>
                                <div class="ad-date"><?= formatDate($ad['created_at']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="load-more">
                    <a href="all-ads.php" class="btn-load-more">Показать все объявления</a>
                </div>
            </section>
        </div>
    </main>

    <!-- Подвал -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Kufar</h3>
                    <p>Доска объявлений Беларуси</p>
                </div>
                <div class="footer-section">
                    <h4>Помощь</h4>
                    <ul>
                        <li><a href="help.php">Как подать объявление</a></li>
                        <li><a href="rules.php">Правила сайта</a></li>
                        <li><a href="safety.php">Безопасность</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Контакты</h4>
                    <ul>
                        <li>Email: info@kufar.by</li>
                        <li>Телефон: +375 (29) 123-45-67</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Kufar. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>