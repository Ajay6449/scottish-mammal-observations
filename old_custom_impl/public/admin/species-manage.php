<?php
/**
 * Scottish Mammal Observations - Species Manager (CRUD Dashboard)
 */

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/helpers/csrf.php';

// Enforce admin permission check
requireAdmin();

$db = getDBConnection();

$errors = [];
$successMessage = "";

// Form variables initialization
$action = 'add'; // 'add' or 'edit'
$editId = 0;
$formData = [
    'common_name' => '',
    'scientific_name' => '',
    'habitat' => '',
    'conservation_status' => '',
    'description' => '',
    'diet' => '',
    'lifespan' => '',
    'average_weight' => '',
    'image_path' => ''
];

// Handle edit command trigger via GET parameter
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    try {
        $stmt = $db->prepare("SELECT * FROM species WHERE id = :id");
        $stmt->execute(['id' => $editId]);
        $row = $stmt->fetch();
        if ($row) {
            $action = 'edit';
            $formData = $row;
        } else {
            $errors['edit'] = "The requested species profile was not found.";
        }
    } catch (\PDOException $e) {
        $errors['edit'] = "Database query failure.";
    }
}

// Handle delete action via POST parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CSRF Check
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        die("CSRF Token validation failed. Request blocked.");
    }
    
    $deleteId = isset($_POST['delete_id']) ? (int)$_POST['delete_id'] : 0;
    try {
        $stmt = $db->prepare("DELETE FROM species WHERE id = :id");
        $stmt->execute(['id' => $deleteId]);
        $successMessage = "Species profile has been successfully deleted.";
        
        // If we deleted the species we were currently editing, reset form
        if ($deleteId === $editId) {
            $action = 'add';
            $editId = 0;
        }
    } catch (\PDOException $e) {
        $errors['delete'] = "Cannot delete species. It may be linked to active observation sighting records.";
    }
}

// Handle form submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add', 'edit'])) {
    // CSRF Check
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        die("CSRF Token validation failed. Request blocked.");
    }
    
    $action = $_POST['action'];
    $editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // Extract and clean values
    $formData['common_name'] = isset($_POST['common_name']) ? cleanInput($_POST['common_name']) : '';
    $formData['scientific_name'] = isset($_POST['scientific_name']) ? cleanInput($_POST['scientific_name']) : '';
    $formData['habitat'] = isset($_POST['habitat']) ? cleanInput($_POST['habitat']) : '';
    $formData['conservation_status'] = isset($_POST['conservation_status']) ? cleanInput($_POST['conservation_status']) : '';
    $formData['description'] = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
    $formData['diet'] = isset($_POST['diet']) ? cleanInput($_POST['diet']) : '';
    $formData['lifespan'] = isset($_POST['lifespan']) ? cleanInput($_POST['lifespan']) : '';
    $formData['average_weight'] = isset($_POST['average_weight']) ? cleanInput($_POST['average_weight']) : '';
    $formData['image_path'] = isset($_POST['image_path']) ? cleanInput($_POST['image_path']) : '';
    
    // Server-side validation
    if (empty($formData['common_name'])) $errors['common_name'] = "Common name is required.";
    if (empty($formData['scientific_name'])) $errors['scientific_name'] = "Scientific name is required.";
    if (empty($formData['habitat'])) $errors['habitat'] = "Habitat classification is required.";
    if (empty($formData['conservation_status'])) $errors['conservation_status'] = "Conservation status is required.";
    if (empty($formData['description'])) $errors['description'] = "A brief description is required.";
    
    // Default image path if empty
    if (empty($formData['image_path'])) {
        $formData['image_path'] = 'default.jpg';
    }

    if (empty($errors)) {
        if ($action === 'add') {
            try {
                $insertSql = "
                    INSERT INTO species (common_name, scientific_name, habitat, conservation_status, description, diet, lifespan, average_weight, image_path) 
                    VALUES (:common_name, :scientific_name, :habitat, :conservation_status, :description, :diet, :lifespan, :average_weight, :image_path)
                ";
                $stmt = $db->prepare($insertSql);
                $stmt->execute($formData);
                $successMessage = "Successfully created new species profile for '" . htmlspecialchars($formData['common_name']) . "'.";
                
                // Clear fields
                $formData = [
                    'common_name' => '', 'scientific_name' => '', 'habitat' => '', 'conservation_status' => '',
                    'description' => '', 'diet' => '', 'lifespan' => '', 'average_weight' => '', 'image_path' => ''
                ];
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate key
                    $errors['scientific_name'] = "A species with this scientific name already exists.";
                } else {
                    $errors['db'] = "Failed to create species profile: " . $e->getMessage();
                }
            }
        } else { // Edit
            try {
                $updateSql = "
                    UPDATE species 
                    SET common_name = :common_name, 
                        scientific_name = :scientific_name, 
                        habitat = :habitat, 
                        conservation_status = :conservation_status, 
                        description = :description, 
                        diet = :diet, 
                        lifespan = :lifespan, 
                        average_weight = :average_weight, 
                        image_path = :image_path 
                    WHERE id = :id
                ";
                $stmt = $db->prepare($updateSql);
                $stmt->execute(array_merge($formData, ['id' => $editId]));
                $successMessage = "Successfully updated species profile for '" . htmlspecialchars($formData['common_name']) . "'.";
                
                // Return back to add action
                $action = 'add';
                $editId = 0;
                $formData = [
                    'common_name' => '', 'scientific_name' => '', 'habitat' => '', 'conservation_status' => '',
                    'description' => '', 'diet' => '', 'lifespan' => '', 'average_weight' => '', 'image_path' => ''
                ];
            } catch (\PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors['scientific_name'] = "A species with this scientific name already exists.";
                } else {
                    $errors['db'] = "Failed to update species profile: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch all species to display in the management table
try {
    $stmt = $db->query("SELECT * FROM species ORDER BY common_name ASC");
    $speciesList = $stmt->fetchAll();
} catch (\PDOException $e) {
    $speciesList = [];
}

$pageTitle = "Manage Species Profiles | Admin Portal";
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">
        <h1 style="margin-bottom: var(--spacing-xs);">Species Catalog Management</h1>
        <p style="color: var(--color-text-muted); margin: 0;">Add, modify, or remove Scottish mammal species profile descriptions in the public directory.</p>
    </div>

    <!-- Alert Messages -->
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
                <li><a href="/admin/species-manage.php" class="active">Manage Species Profile</a></li>
                <li><a href="/admin/observations-manage.php">Moderate Sightings</a></li>
                <li style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); margin-top: var(--spacing-sm);"><a href="/index.php">&larr; Return to Website</a></li>
                <li><a href="/logout.php" style="color: var(--color-error);">Log Out</a></li>
            </ul>
        </aside>

        <!-- Main Column: split into listing and create/edit form -->
        <section style="display: grid; grid-template-columns: 1.5fr 1fr; gap: var(--spacing-xl); align-items: start;">
            
            <!-- List of existing species -->
            <div>
                <h2>Registered Species Profiles (<?php echo count($speciesList); ?> records)</h2>
                <div class="table-responsive">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th scope="col">Common Name</th>
                                <th scope="col">Scientific Name</th>
                                <th scope="col">Habitat</th>
                                <th scope="col" style="text-align: right; width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($speciesList)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--color-text-muted);">No species registered.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($speciesList as $spec): ?>
                                    <tr style="<?php echo ($editId === $spec['id']) ? 'background: #fdfae6;' : ''; ?>">
                                        <td style="font-weight: 600;"><?php echo sanitizeOutput($spec['common_name']); ?></td>
                                        <td style="font-style: italic;"><?php echo sanitizeOutput($spec['scientific_name']); ?></td>
                                        <td><?php echo sanitizeOutput($spec['habitat']); ?></td>
                                        <td style="text-align: right; display: flex; gap: var(--spacing-xs); justify-content: flex-end;">
                                            <a href="/admin/species-manage.php?edit=<?php echo $spec['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 4px 8px; border-radius: 4px;">Edit</a>
                                            
                                            <form action="/admin/species-manage.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this species profile? All linked observations will also be deleted.');" style="display: inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="delete_id" value="<?php echo $spec['id']; ?>">
                                                <button type="submit" class="btn btn-accent" style="font-size: 0.8rem; padding: 4px 8px; border-radius: 4px; background-color: var(--color-error); color: #fff;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form to Add/Edit species -->
            <div class="card" style="padding: var(--spacing-lg); border-top: 4px solid var(--color-primary);">
                <h2 style="margin-top: 0; font-size: 1.4rem; border: none; padding-bottom: 0;"><?php echo ($action === 'add') ? 'Add Species Profile' : 'Edit Species Profile'; ?></h2>
                
                <form action="/admin/species-manage.php" method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $editId; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="common_name">Common Name <span style="color: var(--color-error);">*</span></label>
                        <input type="text" name="common_name" id="common_name" class="form-control" value="<?php echo sanitizeOutput($formData['common_name']); ?>" required placeholder="e.g. Red Squirrel">
                        <?php if (isset($errors['common_name'])): ?>
                            <span class="alert-danger" style="display: block; font-size: 0.8rem; padding: 2px; margin-top: 2px; border-radius: 2px;"><?php echo $errors['common_name']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="scientific_name">Scientific Name <span style="color: var(--color-error);">*</span></label>
                        <input type="text" name="scientific_name" id="scientific_name" class="form-control" value="<?php echo sanitizeOutput($formData['scientific_name']); ?>" required placeholder="e.g. Sciurus vulgaris">
                        <?php if (isset($errors['scientific_name'])): ?>
                            <span class="alert-danger" style="display: block; font-size: 0.8rem; padding: 2px; margin-top: 2px; border-radius: 2px;"><?php echo $errors['scientific_name']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="habitat">Primary Habitat <span style="color: var(--color-error);">*</span></label>
                        <input type="text" name="habitat" id="habitat" class="form-control" value="<?php echo sanitizeOutput($formData['habitat']); ?>" required placeholder="e.g. Coniferous Woodland">
                        <?php if (isset($errors['habitat'])): ?>
                            <span class="alert-danger" style="display: block; font-size: 0.8rem; padding: 2px; margin-top: 2px; border-radius: 2px;"><?php echo $errors['habitat']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="conservation_status">Conservation Status <span style="color: var(--color-error);">*</span></label>
                        <input type="text" name="conservation_status" id="conservation_status" class="form-control" value="<?php echo sanitizeOutput($formData['conservation_status']); ?>" required placeholder="e.g. Near Threatened (UK)">
                        <?php if (isset($errors['conservation_status'])): ?>
                            <span class="alert-danger" style="display: block; font-size: 0.8rem; padding: 2px; margin-top: 2px; border-radius: 2px;"><?php echo $errors['conservation_status']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="diet">Dietary Classification</label>
                        <input type="text" name="diet" id="diet" class="form-control" value="<?php echo sanitizeOutput($formData['diet']); ?>" placeholder="e.g. Seeds, pine cones, nuts">
                    </div>

                    <div class="form-group">
                        <label for="lifespan">Average Lifespan</label>
                        <input type="text" name="lifespan" id="lifespan" class="form-control" value="<?php echo sanitizeOutput($formData['lifespan']); ?>" placeholder="e.g. 3 - 5 years">
                    </div>

                    <div class="form-group">
                        <label for="average_weight">Typical Weight Range</label>
                        <input type="text" name="average_weight" id="average_weight" class="form-control" value="<?php echo sanitizeOutput($formData['average_weight']); ?>" placeholder="e.g. 250 - 350 g">
                    </div>

                    <div class="form-group">
                        <label for="image_path">Species Image Filename</label>
                        <input type="text" name="image_path" id="image_path" class="form-control" value="<?php echo sanitizeOutput($formData['image_path']); ?>" placeholder="e.g. red_squirrel.jpg">
                    </div>

                    <div class="form-group">
                        <label for="description">Detailed Description <span style="color: var(--color-error);">*</span></label>
                        <textarea name="description" id="description" rows="5" class="form-control" required placeholder="Detailed information about the species physical characteristics, behavior, and distribution in Scotland..."><?php echo sanitizeOutput($formData['description']); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="alert-danger" style="display: block; font-size: 0.8rem; padding: 2px; margin-top: 2px; border-radius: 2px;"><?php echo $errors['description']; ?></span>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                        <button type="submit" class="btn btn-primary" style="flex-grow: 1;"><?php echo ($action === 'add') ? 'Create Profile' : 'Save Changes'; ?></button>
                        <?php if ($action === 'edit'): ?>
                            <a href="/admin/species-manage.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php
require_once __DIR__ . '/../../views/layouts/footer.php';
?>
