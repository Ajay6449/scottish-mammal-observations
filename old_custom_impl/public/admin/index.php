<?php
/**
 * Scottish Mammal Observations - Administrator Dashboard
 */

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

// Enforce admin authorization check
requireAdmin();

$db = getDBConnection();

// Fetch summary metrics
try {
    // Total species count
    $stmt = $db->query("SELECT COUNT(*) FROM species");
    $totalSpecies = $stmt->fetchColumn();

    // Approved observations count
    $stmt = $db->query("SELECT COUNT(*) FROM observations WHERE status = 'approved'");
    $approvedObservations = $stmt->fetchColumn();

    // Pending observations count
    $stmt = $db->query("SELECT COUNT(*) FROM observations WHERE status = 'pending'");
    $pendingObservations = $stmt->fetchColumn();
} catch (\PDOException $e) {
    $totalSpecies = 0;
    $approvedObservations = 0;
    $pendingObservations = 0;
}

// Fetch the 5 most recent observations (both pending and approved) for a quick overview log
try {
    $stmt = $db->query("
        SELECT o.*, s.common_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentObservations = $stmt->fetchAll();
} catch (\PDOException $e) {
    $recentObservations = [];
}

$pageTitle = "Admin Dashboard Portal | Scottish Mammal Observations";
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">
        <h1 style="margin-bottom: var(--spacing-xs);">Administration Portal</h1>
        <p style="color: var(--color-text-muted); margin: 0;">Welcome, <strong><?php echo sanitizeOutput($_SESSION['username']); ?></strong>. Manage species data records and moderate community sighting submissions.</p>
    </div>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" aria-label="Admin Navigation Panel">
            <ul>
                <li><a href="/admin/index.php" class="active">Dashboard Summary</a></li>
                <li><a href="/admin/species-manage.php">Manage Species Profile</a></li>
                <li><a href="/admin/observations-manage.php">Moderate Sightings</a></li>
                <li style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); margin-top: var(--spacing-sm);"><a href="/index.php">&larr; Return to Website</a></li>
                <li><a href="/logout.php" style="color: var(--color-error);">Log Out</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <section>
            <!-- Stats Dashboard Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md); margin-bottom: var(--spacing-xl);">
                <div class="stats-summary-card" style="background-color: var(--color-primary); min-height: 120px; padding: var(--spacing-md);">
                    <div class="stat-number" style="font-size: 2.25rem;"><?php echo $totalSpecies; ?></div>
                    <div class="stat-label" style="font-size: 0.8rem; letter-spacing: 0.5px;">Registered Species</div>
                </div>
                
                <div class="stats-summary-card" style="background-color: var(--color-secondary); min-height: 120px; padding: var(--spacing-md);">
                    <div class="stat-number" style="font-size: 2.25rem;"><?php echo $approvedObservations; ?></div>
                    <div class="stat-label" style="font-size: 0.8rem; letter-spacing: 0.5px;">Verified Observations</div>
                </div>

                <div class="stats-summary-card" style="background-color: <?php echo ($pendingObservations > 0) ? '#9e2a2b' : '#3c4a3f'; ?>; min-height: 120px; padding: var(--spacing-md);">
                    <div class="stat-number" style="font-size: 2.25rem; color: #fff;"><?php echo $pendingObservations; ?></div>
                    <div class="stat-label" style="font-size: 0.8rem; letter-spacing: 0.5px;">Sightings Awaiting Review</div>
                </div>
            </div>

            <!-- Management Actions Card Panel -->
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
                <div class="card" style="padding: var(--spacing-lg); border-top: 4px solid var(--color-secondary);">
                    <h3 style="margin-top: 0;">Species Profile Management</h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Add new species profiles to the public directory, edit existing profiles (lifespan, typical weights, diets), or delete redundant entries.</p>
                    <a href="/admin/species-manage.php" class="btn btn-secondary" style="margin-top: auto; display: block; text-align: center;">Open Species Profiles</a>
                </div>
                <div class="card" style="padding: var(--spacing-lg); border-top: 4px solid var(--color-accent);">
                    <h3 style="margin-top: 0;">Sighting Report Moderation</h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Review submissions from the public. Verify the coordinates, check notes, and either approve for public map visualization or delete spam submissions.</p>
                    <a href="/admin/observations-manage.php" class="btn btn-secondary" style="margin-top: auto; display: block; text-align: center;">Open Sighting Moderation</a>
                </div>
            </div>

            <!-- Recent Activity Log Table -->
            <div>
                <h2>Recent Activity Sighting Logs</h2>
                <div class="table-responsive" style="margin-bottom: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Species</th>
                                <th scope="col">Location Name</th>
                                <th scope="col">Date Spotted</th>
                                <th scope="col">Observer</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentObservations)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--color-text-muted);">No activity recorded.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentObservations as $obs): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?php echo sanitizeOutput($obs['common_name']); ?></td>
                                        <td><?php echo sanitizeOutput($obs['location_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($obs['observation_date'])); ?></td>
                                        <td><?php echo sanitizeOutput($obs['observer_name']); ?></td>
                                        <td>
                                            <?php if ($obs['status'] === 'approved'): ?>
                                                <span style="background: #eef8f2; color: var(--color-success); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem;">Approved</span>
                                            <?php elseif ($obs['status'] === 'pending'): ?>
                                                <span style="background: #fdf3e8; color: var(--color-accent-dark); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem;">Pending Review</span>
                                            <?php else: ?>
                                                <span style="background: #fce8e6; color: var(--color-error); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem;">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php
require_once __DIR__ . '/../../views/layouts/footer.php';
?>
