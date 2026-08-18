<?php
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
$currentPage = $currentPage ?? 'home';
$pathPrefix = isset($inAdmin) && $inAdmin ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? e($pageDescription) : 'An interactive database to catalog, map, and monitor mammal species populations across Scotland. Designed under strict accessibility principles.'; ?>">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Scottish Mammal Observations Database</title>
    <link rel="stylesheet" href="<?php echo $pathPrefix; ?>css/reset.css">
    <link rel="stylesheet" href="<?php echo $pathPrefix; ?>css/style.css">
    
    <?php if (isset($loadMap) && $loadMap === true): ?>
        <!-- Leaflet CSS and JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <?php endif; ?>
    
    <?php if (isset($loadCharts) && $loadCharts === true): ?>
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="<?php echo $pathPrefix; ?>index.php" class="site-logo">
                <span>Scottish</span> Mammal Observations
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            <nav class="site-nav">
                <ul>
                    <li><a href="<?php echo $pathPrefix; ?>index.php" class="<?php echo $currentPage === 'home' ? 'active' : ''; ?>">Catalog</a></li>
                    <li><a href="<?php echo $pathPrefix; ?>about.php" class="<?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo $pathPrefix; ?>contact.php" class="<?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact Us</a></li>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li><a href="<?php echo $pathPrefix; ?>admin/index.php" class="<?php echo $currentPage === 'admin' ? 'active' : ''; ?>">Admin Panel</a></li>
                        <li><a href="<?php echo $pathPrefix; ?>logout.php" class="nav-btn">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $pathPrefix; ?>login.php" class="<?php echo $currentPage === 'login' ? 'active' : ''; ?> nav-btn">Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
