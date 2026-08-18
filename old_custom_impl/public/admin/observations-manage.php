<?php
/**
 * Scottish Mammal Observations - Sightings Manager / Moderator Panel
 */

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';

// Check admin role permission
requireAdmin();

$db = getDBConnection();

$errors = [];
$successMessage = "";

// Handle POST actions (Delete or Change Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        die("CSRF Token validation failed. Request blocked.");
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $observationId = isset($_POST['observation_id']) ? (int)$_POST['observation_id'] : 0;
    
    if ($observationId > 0) {
        if ($action === 'delete') {
            try {
                $stmt = $db->prepare("DELETE FROM observations WHERE id = :id");
                $stmt->execute(['id' => $observationId]);
                $successMessage = "Observation sighting report has been successfully deleted.";
            } catch (\PDOException $e) {
                $errors['db'] = "Failed to delete report: " . $e->getMessage();
            }
        } elseif ($action === 'update_status') {
            $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
            if (in_array($newStatus, ['pending', 'approved', 'rejected'])) {
                try {
                    $stmt = $db->prepare("UPDATE observations SET status = :status WHERE id = :id");
                    $stmt->execute(['status' => $newStatus, 'id' => $observationId]);
                    $successMessage = "Sighting status updated to " . ucfirst($newStatus) . " successfully.";
                } catch (\PDOException $e) {
                    $errors['db'] = "Failed to update status: " . $e->getMessage();
                }
            } else {
                $errors['status'] = "Invalid status selected.";
            }
        }
    }
}

// Fetch all observations (joined with species name)
try {
    $stmt = $db->query("
        SELECT o.*, s.common_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        ORDER BY o.observation_date DESC, o.id DESC
    ");
    $observationsList = $stmt->fetchAll();
} catch (\PDOException $e) {
    $observationsList = [];
}

$pageTitle = "Moderate Sighting Reports | Admin Portal";
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">
        <h1 style="margin-bottom: var(--spacing-xs);">Sighting Reports Moderation</h1>
        <p style="color: var(--color-text-muted); margin: 0;">Moderate user-submitted mammal sightings. Approve them for public mapping display, reject, or delete spam reports.</p>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" role="alert">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span><?php echo $successMessage; ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['db'])): ?>
        <div class="alert alert-danger" role="alert">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?php echo $errors['db']; ?></span>
        </div>
    <?php endif; ?>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" aria-label="Admin Navigation Panel">
            <ul>
                <li><a href="/admin/index.php">Dashboard Summary</a></li>
                <li><a href="/admin/species-manage.php">Manage Species Profile</a></li>
                <li><a href="/admin/observations-manage.php" class="active">Moderate Sightings</a></li>
                <li style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); margin-top: var(--spacing-sm);"><a href="/index.php">&larr; Return to Website</a></li>
                <li><a href="/logout.php" style="color: var(--color-error);">Log Out</a></li>
            </ul>
        </aside>

        <!-- Main Sightings Table Column -->
        <section>
            <h2>Verified & Pending Sightings Log (<?php echo count($observationsList); ?> records)</h2>
            
            <?php if (empty($observationsList)): ?>
                <div class="alert alert-info" role="alert">
                    No mammal sighting reports have been submitted yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th scope="col">Species</th>
                                <th scope="col">Date</th>
                                <th scope="col">Sighting Type</th>
                                <th scope="col">Location Name</th>
                                <th scope="col">Observer / Source</th>
                                <th scope="col">Field Notes</th>
                                <th scope="col">Approval Status</th>
                                <th scope="col" style="text-align: right; width: 220px;">Moderate Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($observationsList as $obs): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--color-primary-dark);"><?php echo sanitizeOutput($obs['common_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($obs['observation_date'])); ?></td>
                                    <td>
                                        <?php if ($obs['observation_type'] === 'imported'): ?>
                                            <span class="badge badge-imported">Scientific</span>
                                        <?php else: ?>
                                            <span class="badge badge-user">Community</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 0.85rem; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo sanitizeOutput($obs['location_name']); ?>">
                                        <?php echo sanitizeOutput($obs['location_name']); ?><br>
                                        <small style="color: var(--color-text-muted);"><?php echo number_format($obs['latitude'], 4); ?>, <?php echo number_format($obs['longitude'], 4); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($obs['observation_type'] === 'imported'): ?>
                                            <span style="font-weight: 600; font-size: 0.85rem; display: block;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                                                Licence: <?php echo sanitizeOutput($obs['licence']); ?> | 
                                                <a href="<?php echo sanitizeOutput($obs['source_url']); ?>" target="_blank" rel="noopener" style="text-decoration: underline; color: #2b5c8f;">GBIF Record &rarr;</a>
                                            </span>
                                        <?php else: ?>
                                            <span style="font-weight: 500;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--color-text-muted); max-width: 200px;" title="<?php echo sanitizeOutput($obs['notes']); ?>">
                                        <?php echo !empty($obs['notes']) ? sanitizeOutput(substr($obs['notes'], 0, 100)) . '...' : '<i>None</i>'; ?>
                                    </td>
                                    <td>
                                        <?php if ($obs['status'] === 'approved'): ?>
                                            <span style="background: #eef8f2; color: var(--color-success); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">Approved</span>
                                        <?php elseif ($obs['status'] === 'pending'): ?>
                                            <span style="background: #fdf3e8; color: var(--color-accent-dark); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">Pending</span>
                                        <?php else: ?>
                                            <span style="background: #fce8e6; color: var(--color-error); font-weight: 600; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem;">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; display: flex; gap: var(--spacing-xs); justify-content: flex-end; align-items: center; border-bottom: none; height: 100%;">
                                        <!-- Approval status forms -->
                                        <?php if ($obs['status'] !== 'approved'): ?>
                                            <form action="/admin/observations-manage.php" method="POST" style="display: inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="observation_id" value="<?php echo $obs['id']; ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; border-color: var(--color-success); color: var(--color-success);">Approve</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="/admin/observations-manage.php" method="POST" style="display: inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="observation_id" value="<?php echo $obs['id']; ?>">
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; border-color: var(--color-accent); color: var(--color-accent-dark);">Reject</button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete form -->
                                        <form action="/admin/observations-manage.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this sighting report permanently?');" style="display: inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="observation_id" value="<?php echo $obs['id']; ?>">
                                            <button type="submit" class="btn btn-accent" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; background-color: var(--color-error); color: #fff;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php
require_once __DIR__ . '/../../views/layouts/footer.php';
?>
