<?php
/**
 * Scottish Mammal Observations Database - Admin Observations CRUD
 * Complete Create, Read, Update, Delete (CRUD) dashboard for sightings moderation.
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
$pageTitle = 'Manage Observations';

$pdo = getDbConnection();
$errors = [];
$successMessage = '';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ----------------------------------------------------
// Action: Delete Sighting
// ----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Validate CSRF token
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Security token validation failed (CSRF mismatch).';
    } else {
        $deleteId = (int)$_GET['id'];
        $stmt = $pdo->prepare('DELETE FROM observations WHERE id = ?');
        $stmt->execute([$deleteId]);
        $successMessage = "Sighting #$deleteId has been deleted successfully.";
    }
}

// ----------------------------------------------------
// Action: Save Form (Add / Edit)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_observation'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Security token validation failed (CSRF mismatch).';
    } else {
        $id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;
        $speciesKey = (int)($_POST['gbif_species_key'] ?? 0);
        $locality = trim($_POST['locality'] ?? '');
        $count = (int)($_POST['individual_count'] ?? 1);
        $latitude = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
        $longitude = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
        $date = $_POST['observation_date'] !== '' ? $_POST['observation_date'] : null;

        // Validations
        if ($speciesKey <= 0) {
            $errors[] = 'Please select a valid mammal species.';
        }
        if ($count <= 0) {
            $errors[] = 'Count must be a positive integer.';
        }
        if ($latitude !== null && ($latitude < -90 || $latitude > 90)) {
            $errors[] = 'Latitude must be between -90 and 90.';
        }
        if ($longitude !== null && ($longitude < -180 || $longitude > 180)) {
            $errors[] = 'Longitude must be between -180 and 180.';
        }
        if ($date !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'Please enter a valid date in YYYY-MM-DD format.';
        }

        if (empty($errors)) {
            if ($id) {
                // Edit / Update
                $stmt = $pdo->prepare('
                    UPDATE observations 
                    SET gbif_species_key = ?, locality = ?, individual_count = ?, latitude = ?, longitude = ?, observation_date = ?
                    WHERE id = ?
                ');
                $stmt->execute([$speciesKey, $locality ?: null, $count, $latitude, $longitude, $date, $id]);
                $successMessage = "Sighting #$id updated successfully.";
            } else {
                // Add / Insert
                $stmt = $pdo->prepare('
                    INSERT INTO observations (gbif_species_key, locality, individual_count, latitude, longitude, observation_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([$speciesKey, $locality ?: null, $count, $latitude, $longitude, $date]);
                $newId = $pdo->lastInsertId();
                $successMessage = "New sighting #$newId recorded successfully.";
            }
            // Redirect back to list to prevent form resubmission
            $_SESSION['crud_success'] = $successMessage;
            header('Location: observations.php');
            exit;
        }
    }
}

// Retrieve redirected success messages
if (isset($_SESSION['crud_success'])) {
    $successMessage = $_SESSION['crud_success'];
    unset($_SESSION['crud_success']);
}

// ----------------------------------------------------
// Setup Form Data if in Add / Edit Mode
// ----------------------------------------------------
$editMode = false;
$addMode = false;
$obsData = [
    'id' => '',
    'gbif_species_key' => '',
    'locality' => '',
    'individual_count' => 1,
    'latitude' => '',
    'longitude' => '',
    'observation_date' => date('Y-m-d')
];

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'edit' && isset($_GET['id'])) {
        $editMode = true;
        $editId = (int)$_GET['id'];
        $stmt = $pdo->prepare('SELECT id, gbif_species_key, locality, individual_count, latitude, longitude, observation_date FROM observations WHERE id = ?');
        $stmt->execute([$editId]);
        $row = $stmt->fetch();
        if ($row) {
            $obsData = $row;
        } else {
            $errors[] = "Sighting #$editId not found.";
            $editMode = false;
        }
    } elseif ($_GET['action'] === 'add') {
        $addMode = true;
    }
}

// Fetch all species for form select options
$speciesList = $pdo->query('SELECT gbif_species_key, common_name, species_name FROM species ORDER BY common_name')->fetchAll();

// ----------------------------------------------------
// Setup Paginated List View
// ----------------------------------------------------
$limit = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = trim($_GET['search'] ?? '');
$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(o.locality LIKE :search OR s.common_name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

// Query Count
$countSql = 'SELECT COUNT(*) FROM observations o LEFT JOIN species s ON o.gbif_species_key = s.gbif_species_key';
if (!empty($where)) {
    $countSql .= ' WHERE ' . implode(' AND ', $where);
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalObservations = $countStmt->fetchColumn();
$totalPages = ceil($totalObservations / $limit);

// Query observations list
$listSql = '
    SELECT 
        o.id,
        o.locality,
        o.individual_count,
        o.latitude,
        o.longitude,
        o.observation_date,
        s.common_name
    FROM observations o
    LEFT JOIN species s ON o.gbif_species_key = s.gbif_species_key
';
if (!empty($where)) {
    $listSql .= ' WHERE ' . implode(' AND ', $where);
}
$listSql .= ' ORDER BY o.id DESC LIMIT :limit OFFSET :offset';

$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $val) {
    $listStmt->bindValue($key, $val);
}
$listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$observationsList = $listStmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout" style="margin-top: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <h3 style="font-size: 1.2rem; border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-sm);">Moderator Panel</h3>
        <ul>
            <li><a href="index.php">Overview</a></li>
            <li><a href="observations.php" class="active">Manage Observations</a></li>
            <li><a href="../index.php">&larr; Public Site</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <div>
        <h2>Sighting Records Management</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: var(--spacing-md); font-size: 0.95rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo e($successMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Form Section: Add / Edit Sighting -->
        <?php if ($addMode || $editMode): ?>
            <div class="chart-card" style="margin-bottom: var(--spacing-xl);">
                <h3><?php echo $editMode ? 'Edit Observation #' . e($obsData['id']) : 'Record New Observation'; ?></h3>
                <form action="observations.php" method="POST" style="margin-top: var(--spacing-md);">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="save_observation" value="1">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo e($obsData['id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="gbif_species_key">Species</label>
                        <select id="gbif_species_key" name="gbif_species_key" class="form-control" required>
                            <option value="">-- Select Mammal Species --</option>
                            <?php foreach ($speciesList as $sp): ?>
                                <option value="<?php echo e($sp['gbif_species_key']); ?>" <?php echo $obsData['gbif_species_key'] == $sp['gbif_species_key'] ? 'selected' : ''; ?>>
                                    <?php echo e($sp['common_name']); ?> (<?php echo e($sp['species_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="locality">Locality</label>
                        <input type="text" id="locality" name="locality" class="form-control" placeholder="e.g. Loch Ness Highlands" value="<?php echo e($obsData['locality']); ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="individual_count">Count</label>
                            <input type="number" id="individual_count" name="individual_count" class="form-control" min="1" value="<?php echo e($obsData['individual_count']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="observation_date">Observation Date</label>
                            <input type="date" id="observation_date" name="observation_date" class="form-control" value="<?php echo e($obsData['observation_date']); ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="number" id="latitude" name="latitude" class="form-control" step="any" min="-90" max="90" placeholder="e.g. 57.1423" value="<?php echo e($obsData['latitude']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="number" id="longitude" name="longitude" class="form-control" step="any" min="-180" max="180" placeholder="e.g. -4.2412" value="<?php echo e($obsData['longitude']); ?>">
                        </div>
                    </div>

                    <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-md);">
                        <button type="submit" class="btn btn-primary">Save Sighting</button>
                        <a href="observations.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- List Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md); flex-wrap: wrap; gap: var(--spacing-sm);">
            <form method="GET" action="observations.php" style="display: flex; gap: var(--spacing-xs); flex-grow: 1; max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Search locality or species..." value="<?php echo e($search); ?>" style="padding: 0.5rem 1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Search</button>
            </form>
            <a href="observations.php?action=add" class="btn btn-accent">+ Record Sighting</a>
        </div>

        <?php if (empty($observationsList)): ?>
            <div class="alert alert-info">No observation records found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Species</th>
                            <th>Locality</th>
                            <th>Count</th>
                            <th>Coordinates</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($observationsList as $obs): ?>
                            <tr>
                                <td>#<?php echo e($obs['id']); ?></td>
                                <td><strong><?php echo e($obs['common_name'] ?: 'Unspecified'); ?></strong></td>
                                <td><?php echo $obs['locality'] ? e($obs['locality']) : '<em style="color: var(--color-text-muted);">Not recorded</em>'; ?></td>
                                <td><?php echo e($obs['individual_count']); ?></td>
                                <td><?php echo $obs['latitude'] !== null ? e(round($obs['latitude'], 3)) . ', ' . e(round($obs['longitude'], 3)) : '<em style="color: var(--color-text-muted);">None</em>'; ?></td>
                                <td><?php echo $obs['observation_date'] ? e($obs['observation_date']) : '<em style="color: var(--color-text-muted);">None</em>'; ?></td>
                                <td>
                                    <div style="display: flex; gap: var(--spacing-xs);">
                                        <a href="observations.php?action=edit&id=<?php echo $obs['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; border-radius: var(--border-radius-sm);">Edit</a>
                                        <a href="observations.php?action=delete&id=<?php echo $obs['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                           class="btn btn-primary" 
                                           style="padding: 0.25rem 0.6rem; font-size: 0.8rem; border-radius: var(--border-radius-sm); background-color: var(--color-error); border: none;"
                                           onclick="return confirm('Are you sure you want to delete sighting #<?php echo $obs['id']; ?>?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- List Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="observations.php?page=<?php echo $page - 1; ?><?php echo $search !== '' ? '&search='.urlencode($search) : ''; ?>" class="pagination-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1) {
                        echo '<a href="observations.php?page=1'.($search !== '' ? '&search='.urlencode($search) : '').'" class="pagination-link">1</a>';
                        if ($startPage > 2) echo '<span class="pagination-link" style="border:none;background:none;cursor:default;">...</span>';
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $activeClass = $i === $page ? 'active' : '';
                        echo '<a href="observations.php?page='.$i.($search !== '' ? '&search='.urlencode($search) : '').'" class="pagination-link '.$activeClass.'">'.$i.'</a>';
                    }

                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span class="pagination-link" style="border:none;background:none;cursor:default;">...</span>';
                        echo '<a href="observations.php?page='.$totalPages.($search !== '' ? '&search='.urlencode($search) : '').'" class="pagination-link">'.$totalPages.'</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="observations.php?page=<?php echo $page + 1; ?><?php echo $search !== '' ? '&search='.urlencode($search) : ''; ?>" class="pagination-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
