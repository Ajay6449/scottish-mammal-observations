<?php
/**
 * Scottish Mammal Observations - Species Detail Page
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/media.php';

$db = getDBConnection();

$speciesId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch species details
try {
    $stmt = $db->prepare("SELECT * FROM species WHERE id = :id");
    $stmt->execute(['id' => $speciesId]);
    $species = $stmt->fetch();
} catch (\PDOException $e) {
    $species = null;
}

if (!$species) {
    // 404 page handler
    http_response_code(404);
    $pageTitle = "Species Not Found | Scottish Mammal Observations";
    require_once __DIR__ . '/../views/layouts/header.php';
    echo "
        <div class='container' style='text-align: center; padding: var(--spacing-xxl) 0;'>
            <div class='alert alert-danger' style='justify-content: center; display: inline-flex;'>
                Species profile not found in our records.
            </div>
            <p style='margin-top: var(--spacing-md);'><a href='/species.php' class='btn btn-primary'>Back to Species Directory</a></p>
        </div>
    ";
    require_once __DIR__ . '/../views/layouts/footer.php';
    exit();
}

// Fetch all approved observations for this species (to display on map and list)
try {
    $stmt = $db->prepare("
        SELECT * FROM observations 
        WHERE species_id = :species_id AND status = 'approved' 
        ORDER BY observation_date DESC
    ");
    $stmt->execute(['species_id' => $speciesId]);
    $observations = $stmt->fetchAll();
} catch (\PDOException $e) {
    $observations = [];
}

// Map markers for Leaflet (specific to this species)
$mapMarkers = [];
foreach ($observations as $obs) {
    $mapMarkers[] = [
        'latitude' => $obs['latitude'],
        'longitude' => $obs['longitude'],
        'location_name' => $obs['location_name'],
        'observation_date' => $obs['observation_date'],
        'observer_name' => $obs['observer_name'],
        'common_name' => $species['common_name'],
        'observation_type' => $obs['observation_type'],
        'source_url' => $obs['source_url'],
        'licence' => $obs['licence'],
        'data_provider' => $obs['data_provider']
    ];
}
$markersJson = json_encode($mapMarkers);

$pageTitle = $species['common_name'] . " (" . $species['scientific_name'] . ") | Profile";
$pageDescription = "Learn about the " . $species['common_name'] . " in Scotland: habitat, diet, lifespan, and view local observation sightings.";
$loadMap = true;
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-lg);">
        <a href="/species.php" style="font-weight: 600; display: inline-flex; align-items: center; gap: var(--spacing-xs); margin-bottom: var(--spacing-md);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Species Directory
        </a>
    </div>

    <!-- Species Profile Header and Information Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-xxl); align-items: start;">
        
        <!-- Column 1: Image & Key Facts Card -->
        <div>
            <div style="box-shadow: var(--shadow-md); border-radius: var(--border-radius-md); overflow: hidden; margin-bottom: var(--spacing-lg);">
                <img src="<?php echo getSpeciesImage($species['image_path'], $species['common_name']); ?>" alt="Photograph of a wild <?php echo sanitizeOutput($species['common_name']); ?>" style="width: 100%; height: 350px; object-fit: cover; border-radius: 0;">
            </div>
            
            <!-- Quick Facts Card -->
            <div class="card" style="padding: var(--spacing-lg); border-color: var(--color-primary);">
                <h3 style="margin-top: 0; color: var(--color-primary-dark); font-size: 1.25rem; border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-xs);">Quick Facts</h3>
                <table style="width: 100%; font-size: 0.9rem;">
                    <tbody>
                        <tr>
                            <td style="font-weight: 600; padding: var(--spacing-sm) 0; border: none; width: 40%;">Scientific Name</td>
                            <td style="font-style: italic; padding: var(--spacing-sm) 0; border: none;"><?php echo sanitizeOutput($species['scientific_name']); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: var(--spacing-sm) 0; border: none;">Conservation Status</td>
                            <td style="padding: var(--spacing-sm) 0; border: none; font-weight: 600; color: var(--color-primary);"><?php echo sanitizeOutput($species['conservation_status']); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: var(--spacing-sm) 0; border: none;">Preferred Habitat</td>
                            <td style="padding: var(--spacing-sm) 0; border: none;"><?php echo sanitizeOutput($species['habitat']); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: var(--spacing-sm) 0; border: none;">Average Lifespan</td>
                            <td style="padding: var(--spacing-sm) 0; border: none;"><?php echo sanitizeOutput($species['lifespan']); ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; padding: var(--spacing-sm) 0; border: none;">Typical Weight</td>
                            <td style="padding: var(--spacing-sm) 0; border: none;"><?php echo sanitizeOutput($species['average_weight']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 2: Detailed Text Profile -->
        <div style="display: flex; flex-direction: column; height: 100%;">
            <div style="margin-bottom: var(--spacing-md);">
                <span class="habitat-tag" style="margin-bottom: var(--spacing-sm);"><?php echo sanitizeOutput($species['habitat']); ?></span>
                <h1 style="margin-bottom: var(--spacing-xs);"><?php echo sanitizeOutput($species['common_name']); ?></h1>
                <div class="scientific-name" style="font-size: 1.2rem;"><?php echo sanitizeOutput($species['scientific_name']); ?></div>
            </div>
            
            <div style="margin-bottom: var(--spacing-lg);">
                <h3 style="font-size: 1.15rem; color: var(--color-secondary); margin-bottom: var(--spacing-xs);">Species Description</h3>
                <p style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text-dark); text-align: justify;"><?php echo nl2br(sanitizeOutput($species['description'])); ?></p>
            </div>
            
            <div style="margin-bottom: var(--spacing-lg);">
                <h3 style="font-size: 1.15rem; color: var(--color-secondary); margin-bottom: var(--spacing-xs);">Dietary Habits</h3>
                <p style="color: var(--color-text-dark);"><?php echo sanitizeOutput($species['diet']); ?></p>
            </div>

            <div style="margin-top: auto; padding-top: var(--spacing-md); border-top: 1px solid var(--color-border);">
                <h3 style="font-size: 1.1rem; margin-bottom: var(--spacing-sm);">Sighted a <?php echo sanitizeOutput($species['common_name']); ?>?</h3>
                <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: var(--spacing-md);">Help monitor this species by contributing your sighting date, time, and location to our database.</p>
                <a href="/observation-create.php?species_id=<?php echo $species['id']; ?>" class="btn btn-accent">Submit a Sighting for this Species</a>
            </div>
        </div>
    </div>

    <!-- Sighting Records specific to this Species -->
    <div style="margin-top: var(--spacing-xxl);">
        <h2>Observations Mapping</h2>
        <p style="color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">Below is the distribution map for approved observations of the <?php echo sanitizeOutput($species['common_name']); ?> in Scotland.</p>
        
        <?php if (empty($observations)): ?>
            <div class="alert alert-info" role="alert">
                No observations have been verified for the <?php echo sanitizeOutput($species['common_name']); ?> yet. Be the first to submit a sighting!
            </div>
        <?php else: ?>
            <div class="map-section">
                <div class="map-container" id="recent-map"></div>
            </div>
            
            <h2 style="margin-top: var(--spacing-xl);">Sighting Log (<?php echo count($observations); ?> records)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Date Spotted</th>
                            <th scope="col">Sighting Type</th>
                            <th scope="col">Location Name</th>
                            <th scope="col">Coordinates</th>
                            <th scope="col">Observer / Source</th>
                            <th scope="col">Field Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($observations as $obs): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo date('d M Y', strtotime($obs['observation_date'])); ?></td>
                                <td>
                                    <?php if ($obs['observation_type'] === 'imported'): ?>
                                        <span class="badge badge-imported">Scientific</span>
                                    <?php else: ?>
                                        <span class="badge badge-user">Community</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitizeOutput($obs['location_name']); ?></td>
                                <td><code style="background: #f0ede6; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem;"><?php echo number_format($obs['latitude'], 4); ?>, <?php echo number_format($obs['longitude'], 4); ?></code></td>
                                <td>
                                    <?php if ($obs['observation_type'] === 'imported'): ?>
                                        <span style="display: block; font-weight: 500; font-size: 0.85rem;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                        <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                                            License: <?php echo sanitizeOutput($obs['licence']); ?> | 
                                            <a href="<?php echo sanitizeOutput($obs['source_url']); ?>" target="_blank" rel="noopener" style="text-decoration: underline; color: #2b5c8f;">GBIF Record &rarr;</a>
                                        </span>
                                    <?php else: ?>
                                        <span style="font-weight: 500;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--color-text-muted); max-width: 300px;"><?php echo sanitizeOutput($obs['notes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Pass observations marker coordinates to map-view.js
    window.mapMarkersData = <?php echo $markersJson; ?>;
</script>
<script src="/assets/js/map-view.js"></script>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
