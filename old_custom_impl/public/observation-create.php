<?php
/**
 * Scottish Mammal Observations - Create Sighting Report
 * Handles form rendering, Leaflet click-to-picker, client-side & server-side validation.
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$db = getDBConnection();

// Fetch species for select options
try {
    $stmt = $db->query("SELECT id, common_name FROM species ORDER BY common_name ASC");
    $speciesList = $stmt->fetchAll();
} catch (\PDOException $e) {
    $speciesList = [];
}

// Check if a pre-selected species ID was provided
$preSelectedSpeciesId = isset($_GET['species_id']) ? (int)$_GET['species_id'] : 0;

// Initialize error and success messages
$errors = [];
$successMessage = "";

// Initialize form values
$formData = [
    'species_id' => $preSelectedSpeciesId,
    'observer_name' => '',
    'observation_date' => date('Y-m-d'),
    'latitude' => '56.49070000',  // Default to center of Scotland
    'longitude' => '-4.20260000',
    'location_name' => '',
    'notes' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate CSRF Token
    $csrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!validateCSRFToken($csrfToken)) {
        http_response_code(403);
        die("CSRF Token validation failed. Request blocked.");
    }
    
    // 2. Extract and Clean Inputs
    $formData['species_id'] = isset($_POST['species_id']) ? (int)$_POST['species_id'] : 0;
    $formData['observer_name'] = isset($_POST['observer_name']) ? cleanInput($_POST['observer_name']) : '';
    $formData['observation_date'] = isset($_POST['observation_date']) ? cleanInput($_POST['observation_date']) : '';
    $formData['latitude'] = isset($_POST['latitude']) ? cleanInput($_POST['latitude']) : '';
    $formData['longitude'] = isset($_POST['longitude']) ? cleanInput($_POST['longitude']) : '';
    $formData['location_name'] = isset($_POST['location_name']) ? cleanInput($_POST['location_name']) : '';
    $formData['notes'] = isset($_POST['notes']) ? cleanInput($_POST['notes']) : '';
    
    // 3. Server-side Validation
    if ($formData['species_id'] <= 0) {
        $errors['species_id'] = "Please select a valid mammal species.";
    }
    if (empty($formData['observer_name'])) {
        $errors['observer_name'] = "Observer name is required.";
    } elseif (strlen($formData['observer_name']) > 100) {
        $errors['observer_name'] = "Observer name must be 100 characters or less.";
    }
    
    if (empty($formData['observation_date'])) {
        $errors['observation_date'] = "Sighting date is required.";
    } elseif (!isValidObservationDate($formData['observation_date'])) {
        $errors['observation_date'] = "Invalid date. Sightings must be valid dates and cannot be in the future.";
    }
    
    if (empty($formData['location_name'])) {
        $errors['location_name'] = "Location name is required.";
    } elseif (strlen($formData['location_name']) > 255) {
        $errors['location_name'] = "Location name must be 255 characters or less.";
    }
    
    if ($formData['latitude'] === '') {
        $errors['latitude'] = "Latitude coordinate is required.";
    } elseif (!isValidLatitude($formData['latitude'])) {
        $errors['latitude'] = "Latitude must be a valid decimal number between -90 and 90.";
    }
    
    if ($formData['longitude'] === '') {
        $errors['longitude'] = "Longitude coordinate is required.";
    } elseif (!isValidLongitude($formData['longitude'])) {
        $errors['longitude'] = "Longitude must be a valid decimal number between -180 and 180.";
    }
    
    // 4. Save to Database if no errors
    if (empty($errors)) {
        try {
            $insertSql = "
                INSERT INTO observations (species_id, observer_name, observation_date, latitude, longitude, location_name, notes, status) 
                VALUES (:species_id, :observer_name, :observation_date, :latitude, :longitude, :location_name, :notes, 'approved')
            ";
            $stmt = $db->prepare($insertSql);
            $stmt->execute([
                'species_id' => $formData['species_id'],
                'observer_name' => $formData['observer_name'],
                'observation_date' => $formData['observation_date'],
                'latitude' => $formData['latitude'],
                'longitude' => $formData['longitude'],
                'location_name' => $formData['location_name'],
                'notes' => empty($formData['notes']) ? null : $formData['notes']
            ]);
            
            $successMessage = "Thank you! Your mammal observation has been successfully logged and published.";
            
            // Reset form except for observer name (for consecutive reports convenience)
            $formData['species_id'] = 0;
            $formData['location_name'] = '';
            $formData['notes'] = '';
            // keep lat/lng centered for next pick
            $formData['latitude'] = '56.49070000';
            $formData['longitude'] = '-4.20260000';
        } catch (\PDOException $e) {
            $errors['db'] = "Failed to submit observation. Database error: " . $e->getMessage();
        }
    }
}

$pageTitle = "Submit Observation | Scottish Mammal Observations";
$pageDescription = "Submit a sighting of a Scottish mammal. Select the species, mark the exact location on our map, and log your field notes.";
$loadMap = true;

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl);">
        <h1>Submit Sighting Report</h1>
        <p style="color: var(--color-text-muted); font-size: 1.1rem; max-width: 800px;">
            Help us track and map Scottish mammal biodiversity. Fill in the sighting information below, click the interactive map to pin the exact coordinates, and click submit.
        </p>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" role="alert" id="success-alert">
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

    <!-- Form layout grid: Left side form fields, Right side map picker -->
    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: var(--spacing-xl); align-items: start; margin-bottom: var(--spacing-xl);">
        
        <!-- Sighting Form -->
        <form action="/observation-create.php" method="POST" class="card" style="padding: var(--spacing-lg); margin-bottom: 0;">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label for="species_id">Mammal Species <span style="color: var(--color-error);">*</span></label>
                <select name="species_id" id="species_id" class="form-control" required aria-describedby="species-error">
                    <option value="">-- Select Species --</option>
                    <?php foreach ($speciesList as $spec): ?>
                        <option value="<?php echo $spec['id']; ?>" <?php echo ((int)$formData['species_id'] === (int)$spec['id']) ? 'selected' : ''; ?>>
                            <?php echo sanitizeOutput($spec['common_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['species_id'])): ?>
                    <span id="species-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['species_id']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="observer_name">Observer Name <span style="color: var(--color-error);">*</span></label>
                <input type="text" name="observer_name" id="observer_name" class="form-control" placeholder="Your full name" value="<?php echo sanitizeOutput($formData['observer_name']); ?>" required maxlength="100" aria-describedby="observer-error">
                <?php if (isset($errors['observer_name'])): ?>
                    <span id="observer-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['observer_name']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="observation_date">Sighting Date <span style="color: var(--color-error);">*</span></label>
                <input type="date" name="observation_date" id="observation_date" class="form-control" value="<?php echo sanitizeOutput($formData['observation_date']); ?>" required max="<?php echo date('Y-m-d'); ?>" aria-describedby="date-error">
                <?php if (isset($errors['observation_date'])): ?>
                    <span id="date-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['observation_date']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="location_name">Location Name / Nearest Landmark <span style="color: var(--color-error);">*</span></label>
                <input type="text" name="location_name" id="location_name" class="form-control" placeholder="e.g. Aviemore Woods, Cairngorms" value="<?php echo sanitizeOutput($formData['location_name']); ?>" required maxlength="255" aria-describedby="location-error">
                <?php if (isset($errors['location_name'])): ?>
                    <span id="location-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['location_name']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="latitude">Latitude <span style="color: var(--color-error);">*</span></label>
                    <input type="number" name="latitude" id="latitude" class="form-control" step="any" min="-90" max="90" placeholder="e.g. 56.4907" value="<?php echo sanitizeOutput($formData['latitude']); ?>" required aria-describedby="lat-error">
                    <?php if (isset($errors['latitude'])): ?>
                        <span id="lat-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['latitude']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="longitude">Longitude <span style="color: var(--color-error);">*</span></label>
                    <input type="number" name="longitude" id="longitude" class="form-control" step="any" min="-180" max="180" placeholder="e.g. -4.2026" value="<?php echo sanitizeOutput($formData['longitude']); ?>" required aria-describedby="lng-error">
                    <?php if (isset($errors['longitude'])): ?>
                        <span id="lng-error" class="alert-danger" style="display: block; font-size: 0.85rem; padding: 4px; margin-top: 4px; border-radius: 4px;"><?php echo $errors['longitude']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Observation Notes / Details</label>
                <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Describe the animal behavior, markings, weather conditions, group size, or health..."><?php echo sanitizeOutput($formData['notes']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 0.9rem;">Submit Sighting Report</button>
        </form>

        <!-- Interactive Map coordinates selector -->
        <div class="map-section" style="margin-bottom: 0;">
            <h2 style="margin-top: 0; font-size: 1.5rem; border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">Sighting Location Picker</h2>
            <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: var(--spacing-md);">
                Click on the map at the approximate location of your observation to automatically fill the Latitude and Longitude coordinates. You can also drag the marker to fine-tune its position.
            </p>
            <div class="map-container" id="picker-map" style="height: 480px;"></div>
            <div style="margin-top: var(--spacing-md); font-size: 0.85rem; color: var(--color-text-muted); display: flex; justify-content: space-between;">
                <span><strong>Map Center:</strong> Scotland</span>
                <span>Click and drag to set position</span>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/map-picker.js"></script>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
