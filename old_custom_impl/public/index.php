<?php
/**
 * Scottish Mammal Observations - Home Page
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/media.php';

$db = getDBConnection();

// Fetch statistics
try {
    // Total observations count
    $stmt = $db->query("SELECT COUNT(*) FROM observations WHERE status = 'approved'");
    $totalObservations = $stmt->fetchColumn();

    // Imported observations count
    $stmt = $db->query("SELECT COUNT(*) FROM observations WHERE status = 'approved' AND observation_type = 'imported'");
    $importedObservations = $stmt->fetchColumn();

    // User-submitted observations count
    $stmt = $db->query("SELECT COUNT(*) FROM observations WHERE status = 'approved' AND observation_type = 'user_submitted'");
    $userObservations = $stmt->fetchColumn();

    // Total species count
    $stmt = $db->query("SELECT COUNT(*) FROM species");
    $totalSpecies = $stmt->fetchColumn();
    
    // Top species observed
    $stmt = $db->query("
        SELECT s.common_name, COUNT(o.id) as count 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        WHERE o.status = 'approved' 
        GROUP BY o.species_id 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $topSpecies = $stmt->fetchAll();
} catch (\PDOException $e) {
    $totalObservations = 0;
    $importedObservations = 0;
    $userObservations = 0;
    $totalSpecies = 0;
    $topSpecies = [];
}

// Fetch featured/representative mammals (limit 3)
try {
    $stmt = $db->query("SELECT * FROM species ORDER BY id ASC LIMIT 3");
    $featuredSpecies = $stmt->fetchAll();
} catch (\PDOException $e) {
    $featuredSpecies = [];
}

// Fetch 3 most recent observations
try {
    $stmt = $db->query("
        SELECT o.*, s.common_name, s.scientific_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        WHERE o.status = 'approved' 
        ORDER BY o.observation_date DESC, o.id DESC 
        LIMIT 3
    ");
    $recentObservations = $stmt->fetchAll();
} catch (\PDOException $e) {
    $recentObservations = [];
}

// Fetch map markers data (JSON format for JS consumption, limited to 150 for performance)
try {
    $stmt = $db->query("
        SELECT o.latitude, o.longitude, o.location_name, o.observation_date, o.observer_name, o.observation_type, o.source_url, o.licence, o.data_provider, s.common_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        WHERE o.status = 'approved' 
        ORDER BY o.observation_date DESC 
        LIMIT 150
    ");
    $mapMarkers = $stmt->fetchAll();
} catch (\PDOException $e) {
    $mapMarkers = [];
}

// Prepare pages markers for Leaflet JS
$markersJson = json_encode($mapMarkers);

// Set metadata for header layout
$pageTitle = "Home | Scottish Mammal Observations";
$pageDescription = "Discover Scotland's Wild Side. Explore native mammals, view interactive sighting maps, and contribute your own observations.";
$loadMap = true;
$loadStats = true;

// Include layout header
require_once __DIR__ . '/../views/layouts/header.php';
?>

<!-- Hero Landing Section -->
<section class="hero">
    <div class="container">
        <h1>Discover Scotland's Wild Side</h1>
        <p>Explore Scotland's magnificent mammal species, discover where they have been observed in the wild, and contribute your own sightings to aid national conservation research.</p>
        <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
            <a href="/species.php" class="btn btn-accent">Explore Species Directory</a>
            <a href="/observation-create.php" class="btn btn-secondary" style="border-color: #ffffff; color: #ffffff;">Submit Sighting Report</a>
        </div>
    </div>
</section>

<div class="container" style="margin-top: var(--spacing-xl);">
    
    <!-- Quick Statistics Dashboard -->
    <section aria-label="Key Observations Statistics">
        <div class="stats-grid">
            <div class="chart-card">
                <h3 style="margin-top: 0;">Observations by Species</h3>
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="speciesChart" aria-label="Bar chart showing observations count by species"></canvas>
                </div>
            </div>
            <div class="stats-summary-card" style="padding: var(--spacing-md) var(--spacing-lg);">
                <h3 style="color: var(--color-accent); margin-top: 0; margin-bottom: var(--spacing-md);">Platform Summary</h3>
                <div style="margin-bottom: var(--spacing-sm); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">
                    <span class="stat-label">Total Sightings</span>
                    <span style="font-weight: 700; color: #fff; font-size: 1.25rem;"><?php echo $totalObservations; ?></span>
                </div>
                <div style="margin-bottom: var(--spacing-sm); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">
                    <span class="stat-label" style="font-size: 0.8rem; color: #b8d2eb;">&bull; Scientific Records</span>
                    <span style="font-weight: 700; color: var(--color-accent); font-size: 1.1rem;"><?php echo $importedObservations; ?></span>
                </div>
                <div style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">
                    <span class="stat-label" style="font-size: 0.8rem; color: #ccece0;">&bull; Community Sightings</span>
                    <span style="font-weight: 700; color: var(--color-accent); font-size: 1.1rem;"><?php echo $userObservations; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="stat-label">Mammal Species</span>
                    <span style="font-weight: 700; color: #fff; font-size: 1.25rem;"><?php echo $totalSpecies; ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Interactive Map Section -->
    <section class="map-section" style="margin-top: var(--spacing-xl);">
        <h2>Interactive Sighting Map</h2>
        <p style="color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">Explore where Scottish mammals have been spotted recently. Drag and zoom the map to see details of sightings across Scotland's National Parks, islands, and glens.</p>
        <div class="map-container" id="recent-map"></div>
    </section>

    <!-- Featured Species Grid -->
    <section style="margin-top: var(--spacing-xxl);">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--spacing-lg); border-bottom: 2px solid var(--color-border); padding-bottom: var(--spacing-sm);">
            <h2 style="border: none; margin: 0; padding: 0;">Featured Scottish Mammals</h2>
            <a href="/species.php" style="font-weight: 600; display: flex; align-items: center; gap: var(--spacing-xs);">
                View All Species 
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
        </div>
        
        <div class="grid">
            <?php foreach ($featuredSpecies as $spec): ?>
                <article class="card">
                    <div class="card-img-container">
                        <img src="<?php echo getSpeciesImage($spec['image_path'], $spec['common_name']); ?>" alt="Photograph of a wild <?php echo sanitizeOutput($spec['common_name']); ?>">
                    </div>
                    <div class="card-content">
                        <h3><?php echo sanitizeOutput($spec['common_name']); ?></h3>
                        <div class="scientific-name"><?php echo sanitizeOutput($spec['scientific_name']); ?></div>
                        <span class="habitat-tag"><?php echo sanitizeOutput($spec['habitat']); ?></span>
                        <p><?php echo sanitizeOutput(substr($spec['description'], 0, 140)) . '...'; ?></p>
                        <a href="/species-detail.php?id=<?php echo $spec['id']; ?>" class="btn btn-primary" style="margin-top: auto; text-align: center;">View Species Details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Recent Observations List -->
    <section style="margin-top: var(--spacing-xxl); margin-bottom: var(--spacing-xl);">
        <h2>Latest Sighting Submissions</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Species</th>
                        <th scope="col">Sighting Type</th>
                        <th scope="col">Location Name</th>
                        <th scope="col">Date Spotted</th>
                        <th scope="col">Observer / Source</th>
                        <th scope="col">Field Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentObservations)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-muted);">No observations recorded yet. Be the first to submit a sighting!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentObservations as $obs): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--color-primary-dark);"><?php echo sanitizeOutput($obs['common_name']); ?></td>
                                <td>
                                    <?php if ($obs['observation_type'] === 'imported'): ?>
                                        <span class="badge badge-imported">Scientific</span>
                                    <?php else: ?>
                                        <span class="badge badge-user">Community</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitizeOutput($obs['location_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($obs['observation_date'])); ?></td>
                                <td>
                                    <?php if ($obs['observation_type'] === 'imported'): ?>
                                        <span style="display: block; font-weight: 500; font-size: 0.85rem;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                        <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                                            License: <?php echo sanitizeOutput($obs['licence']); ?> | 
                                            <a href="<?php echo sanitizeOutput($obs['source_url']); ?>" target="_blank" rel="noopener" style="text-decoration: underline; color: #2b5c8f;">GBIF &rarr;</a>
                                        </span>
                                    <?php else: ?>
                                        <span style="font-weight: 500;"><?php echo sanitizeOutput($obs['observer_name']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--color-text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo sanitizeOutput($obs['notes']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="text-align: center;">
            <a href="/observations.php" class="btn btn-secondary">Browse All Sightings</a>
        </div>
    </section>

</div>

<!-- Data scripts passing values from PHP to Javascript files -->
<script>
    window.mapMarkersData = <?php echo $markersJson; ?>;
    
    // Process statistics for Chart.js
    window.chartLabels = [
        <?php foreach ($topSpecies as $ts) { echo '"' . sanitizeOutput($ts['common_name']) . '",'; } ?>
    ];
    window.chartData = [
        <?php foreach ($topSpecies as $ts) { echo $ts['count'] . ','; } ?>
    ];
</script>

<?php
// Set specific script files to load in layout footer
$scriptFile = '/assets/js/stats.js';
// We also need map loading, which we trigger in a custom script or merge it. 
// Let's create a combined/separate map-view.js loading inside index.php or footer.
?>
<script src="/assets/js/map-view.js"></script>
<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
