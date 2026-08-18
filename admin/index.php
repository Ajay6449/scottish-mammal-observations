<?php
/**
 * Scottish Mammal Observations Database - Admin Dashboard
 * Displays platform statistics and navigation links for authenticated moderators.
 *
 * SET08101 Web Technologies Coursework
 */

require_once '../includes/db.php';

// Session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$inAdmin = true;
$currentPage = 'admin';
$pageTitle = 'Admin Dashboard';

$pdo = getDbConnection();

// Fetch summary metrics
$totalSpecies = $pdo->query('SELECT COUNT(*) FROM species')->fetchColumn();
$totalObservations = $pdo->query('SELECT COUNT(*) FROM observations')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// Fetch recent observations
$stmt = $pdo->query('
    SELECT 
        o.id,
        o.locality,
        o.individual_count,
        o.observation_date,
        s.common_name
    FROM observations o
    LEFT JOIN species s ON o.gbif_species_key = s.gbif_species_key
    ORDER BY o.id DESC
    LIMIT 5
');
$recentObservations = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout" style="margin-top: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <h3 style="font-size: 1.2rem; border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-sm);">Moderator Panel</h3>
        <ul>
            <li><a href="index.php" class="active">Overview</a></li>
            <li><a href="observations.php">Manage Observations</a></li>
            <li><a href="../index.php">&larr; Public Site</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div>
        <h2>System Overview</h2>
        <p>Welcome back, <strong><?php echo e($_SESSION['admin_username']); ?></strong>! Below is the current system health and data status.</p>

        <!-- Stats Grid -->
        <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--spacing-md); margin-top: var(--spacing-lg);">
            <div class="stats-summary-card" style="background-color: var(--color-bg-card); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); color: var(--color-text-dark);">
                <div class="stat-number" style="color: var(--color-primary);"><?php echo e($totalSpecies); ?></div>
                <div class="stat-label" style="color: var(--color-text-muted);">Species Profiles</div>
            </div>
            
            <div class="stats-summary-card" style="background-color: var(--color-bg-card); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); color: var(--color-text-dark);">
                <div class="stat-number" style="color: var(--color-secondary);"><?php echo number_format($totalObservations); ?></div>
                <div class="stat-label" style="color: var(--color-text-muted);">Observations Ingested</div>
            </div>
            
            <div class="stats-summary-card" style="background-color: var(--color-bg-card); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); color: var(--color-text-dark);">
                <div class="stat-number" style="color: var(--color-accent-dark);"><?php echo e($totalUsers); ?></div>
                <div class="stat-label" style="color: var(--color-text-muted);">Admin Accounts</div>
            </div>
        </div>

        <h3 style="margin-top: var(--spacing-xl); margin-bottom: var(--spacing-md);">Recent Observations Ingested</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Sighting ID</th>
                        <th>Species</th>
                        <th>Locality</th>
                        <th>Date</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentObservations as $obs): ?>
                        <tr>
                            <td>#<?php echo e($obs['id']); ?></td>
                            <td><strong><?php echo e($obs['common_name']); ?></strong></td>
                            <td><?php echo $obs['locality'] ? e($obs['locality']) : '<em style="color: var(--color-text-muted);">Location not recorded</em>'; ?></td>
                            <td><?php echo $obs['observation_date'] ? e(date('d M Y', strtotime($obs['observation_date']))) : '<em style="color: var(--color-text-muted);">Date not recorded</em>'; ?></td>
                            <td><strong style="color: var(--color-primary);"><?php echo e($obs['individual_count']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
