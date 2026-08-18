<?php
/**
 * Header Layout Template
 * Provides semantic head metadata, stylesheet references, and accessible navigation.
 */
require_once __DIR__ . '/../../app/helpers/auth.php';
secureSessionStart();

// Set default title and description if not defined in the parent page
if (!isset($pageTitle)) {
    $pageTitle = "Scottish Mammal Observations | Explore & Document Wildlife";
}
if (!isset($pageDescription)) {
    $pageDescription = "Explore Scotland's native mammals, view interactive sighting maps, and contribute your own observations to help conservation research.";
}

// Helper to check active nav link
function isNavLinkActive(string $pageName): string {
    $script = basename($_SERVER['SCRIPT_NAME']);
    return ($script === $pageName) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Design System Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Leaflet Map CSS (Only loaded on pages requiring maps) -->
    <?php if (isset($loadMap) && $loadMap === true): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>
</head>
<body>
    <a href="#main-content" class="btn btn-accent" style="position: absolute; top: -100px; left: 10px; z-index: 10000; transition: top 0.2s;" onfocus="this.style.top='10px'" onblur="this.style.top='-100px'">Skip to main content</a>

    <header class="site-header">
        <div class="container">
            <a href="/index.php" class="site-logo" aria-label="Scottish Mammal Observations Home">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-accent);" aria-hidden="true">
                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                    <path d="M19 10v1a7 7 0 0 1-14 0v-1"></path>
                    <line x1="12" x2="12" y1="19" y2="22"></line>
                </svg>
                <span>Scottish</span> Mammals
            </a>
            
            <nav class="site-nav" aria-label="Main Navigation">
                <ul>
                    <li><a href="/index.php" class="<?php echo isNavLinkActive('index.php'); ?>">Home</a></li>
                    <li><a href="/species.php" class="<?php echo isNavLinkActive('species.php') || isNavLinkActive('species-detail.php'); ?>">Species Directory</a></li>
                    <li><a href="/observations.php" class="<?php echo isNavLinkActive('observations.php'); ?>">Sightings</a></li>
                    <li><a href="/observation-create.php" class="nav-btn <?php echo isNavLinkActive('observation-create.php'); ?>">Submit Sighting</a></li>
                    
                    <?php if (isAdmin()): ?>
                        <li><a href="/admin/index.php" class="<?php echo (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? 'active' : ''; ?>" style="border: 1px dashed var(--color-accent); color: var(--color-accent);">Admin Portal</a></li>
                        <li><a href="/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="/login.php" class="<?php echo isNavLinkActive('login.php'); ?>">Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main-content">
