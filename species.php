<?php
/**
 * Scottish Mammal Observations Database - Species Detail Page
 * Displays detailed information about a single species, its occurrences map, and paginated logs.
 *
 * SET08101 Web Technologies Coursework
 */

require_once 'includes/db.php';

// Validate the species key parameter
if (!isset($_GET['key']) || !is_numeric($_GET['key'])) {
    header('Location: index.php');
    exit;
}

$speciesKey = (int)$_GET['key'];
$pdo = getDbConnection();

// Fetch the species details
$stmt = $pdo->prepare('
    SELECT
        gbif_species_key,
        species_name,
        common_name,
        iucn_red_list_category,
        body_mass_kg,
        dietary_category,
        uk_protection_status,
        habitat,
        image_url
    FROM species
    WHERE gbif_species_key = ?
');
$stmt->execute([$speciesKey]);
$species = $stmt->fetch();

// If species not found, redirect to home
if (!$species) {
    header('Location: index.php');
    exit;
}

$pageTitle = $species['common_name'];
$pageDescription = 'Detailed profile, protection status, physical characteristics, and interactive observation mapping for ' . $species['common_name'] . ' (' . $species['species_name'] . ') in Scotland.';
$loadMap = true; // Signals header.php to load Leaflet CDNs

// Configure Pagination for Observations
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total count of observations for pagination
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM observations WHERE gbif_species_key = :key');
$countStmt->execute([':key' => $speciesKey]);
$totalObservations = $countStmt->fetchColumn();
$totalPages = ceil($totalObservations / $limit);

// Fetch paginated observations
$obsStmt = $pdo->prepare('
    SELECT
        id,
        locality,
        individual_count,
        latitude,
        longitude,
        observation_date
    FROM observations
    WHERE gbif_species_key = :key
    ORDER BY observation_date DESC, id DESC
    LIMIT :limit OFFSET :offset
');
$obsStmt->bindValue(':key', $speciesKey, PDO::PARAM_INT);
$obsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$obsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$obsStmt->execute();
$observations = $obsStmt->fetchAll();

// Fetch all mapping points for Leaflet map (capped at 500 points for frontend rendering performance)
$mapPointsStmt = $pdo->prepare('
    SELECT
        locality,
        individual_count,
        latitude,
        longitude,
        observation_date
    FROM observations
    WHERE gbif_species_key = :key AND latitude IS NOT NULL AND longitude IS NOT NULL
    ORDER BY id DESC
    LIMIT 500
');
$mapPointsStmt->execute([':key' => $speciesKey]);
$mapPoints = $mapPointsStmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<p style="margin-top: var(--spacing-md);"><a href="index.php">&larr; Back to all species</a></p>

<!-- Species Info Panel -->
<div class="grid" style="grid-template-columns: 1fr 2fr; gap: var(--spacing-xl); align-items: start; margin-top: var(--spacing-md); margin-bottom: var(--spacing-xl);">
    <div>
        <?php if (!empty($species['image_url'])): ?>
            <a href="<?php echo e($species['image_url']); ?>" class="modal-trigger" title="Click to view full size">
                <img src="<?php echo e($species['image_url']); ?>" alt="<?php echo e($species['common_name']); ?>" style="width: 100%; border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm); cursor: zoom-in;">
            </a>
        <?php else: ?>
            <div style="width: 100%; height: 250px; background-color: var(--color-border); border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">No Image Available</div>
        <?php endif; ?>
        <p style="font-size: 0.85rem; color: var(--color-text-muted); text-align: center; margin-top: var(--spacing-xs);">🔍 Click image to enlarge</p>
    </div>
    
    <div>
        <h2><?php echo e($species['common_name']); ?></h2>
        <h4 style="font-style: italic; color: var(--color-text-muted); margin-top: -10px; margin-bottom: var(--spacing-lg);">Scientific Name: <?php echo e($species['species_name']); ?></h4>
        
        <dl class="form-grid" style="margin-top: var(--spacing-md);">
            <div>
                <dt style="font-weight: 700; color: var(--color-primary);">Conservation Status</dt>
                <dd style="margin-left: 0; margin-bottom: var(--spacing-md);">
                    <span class="badge badge-user" style="background-color: #faf0d8; color: #b08a1c; border-color: #f5e4bd;">
                        <?php echo $species['iucn_red_list_category'] ? e($species['iucn_red_list_category']) : 'LC'; ?>
                    </span>
                </dd>
                
                <dt style="font-weight: 700; color: var(--color-primary);">Dietary Category</dt>
                <dd style="margin-left: 0; margin-bottom: var(--spacing-md);"><?php echo e($species['dietary_category']); ?></dd>
                
                <dt style="font-weight: 700; color: var(--color-primary);">UK Protection Status</dt>
                <dd style="margin-left: 0; margin-bottom: var(--spacing-md);"><?php echo $species['uk_protection_status'] ? e($species['uk_protection_status']) : 'Basic legal protection'; ?></dd>
            </div>
            
            <div>
                <dt style="font-weight: 700; color: var(--color-primary);">Body Mass</dt>
                <dd style="margin-left: 0; margin-bottom: var(--spacing-md);"><?php echo e($species['body_mass_kg']); ?> kg</dd>
                
                <dt style="font-weight: 700; color: var(--color-primary);">Primary Habitat</dt>
                <dd style="margin-left: 0; margin-bottom: var(--spacing-md);"><?php echo e($species['habitat']); ?></dd>
            </div>
        </dl>
    </div>
</div>

<!-- Interactive Maps Section -->
<?php if (!empty($mapPoints)): ?>
    <section class="map-section">
        <h3>Observation Occurrence Map</h3>
        <p style="margin-bottom: var(--spacing-md);">Geographic sightings of <?php echo e($species['common_name']); ?> plotted across Scotland.</p>
        <div id="speciesMap" class="map-container"></div>
    </section>
<?php endif; ?>

<!-- Observations Table Section -->
<h3>Occurrence Sighting Log</h3>
<p style="margin-bottom: var(--spacing-lg);">A total of <?php echo e($totalObservations); ?> scientific observation records loaded from the GBIF database.</p>

<?php if (empty($observations)): ?>
    <div class="alert alert-info">No observations recorded for this species.</div>
<?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Sighting ID</th>
                    <th>Locality / Region</th>
                    <th>Date Observed</th>
                    <th>Count</th>
                    <th>Coordinates (Lat, Lng)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($observations as $obs): ?>
                    <tr>
                        <td>#<?php echo e($obs['id']); ?></td>
                        <td><?php echo $obs['locality'] ? e($obs['locality']) : '<em style="color: var(--color-text-muted);">Location not recorded</em>'; ?></td>
                        <td><?php echo $obs['observation_date'] ? e(date('d M Y', strtotime($obs['observation_date']))) : '<em style="color: var(--color-text-muted);">Date not recorded</em>'; ?></td>
                        <td><strong style="color: var(--color-primary);"><?php echo e($obs['individual_count']); ?></strong></td>
                        <td><?php echo $obs['latitude'] !== null ? e(round($obs['latitude'], 4)) . ', ' . e(round($obs['longitude'], 4)) : '<em style="color: var(--color-text-muted);">Coordinates omitted</em>'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-bottom: var(--spacing-xl);">
            <?php if ($page > 1): ?>
                <a href="species.php?key=<?php echo $speciesKey; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">&laquo; Prev</a>
            <?php endif; ?>

            <?php
            // Simple smart pagination logic (displays current page, 2 pages before, and 2 pages after)
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1) {
                echo '<a href="species.php?key='.$speciesKey.'&page=1" class="pagination-link">1</a>';
                if ($startPage > 2) echo '<span class="pagination-link" style="border:none;background:none;cursor:default;">...</span>';
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = $i === $page ? 'active' : '';
                echo '<a href="species.php?key='.$speciesKey.'&page='.$i.'" class="pagination-link '.$activeClass.'">'.$i.'</a>';
            }

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) echo '<span class="pagination-link" style="border:none;background:none;cursor:default;">...</span>';
                echo '<a href="species.php?key='.$speciesKey.'&page='.$totalPages.'" class="pagination-link">'.$totalPages.'</a>';
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="species.php?key=<?php echo $speciesKey; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Leaflet Map Script Init -->
<?php if (!empty($mapPoints)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const points = <?php echo json_encode($mapPoints); ?>;
        
        // Initial center on the first observation coordinate or general Scotland bounds
        const initialLat = points[0] ? parseFloat(points[0].latitude) : 57.0;
        const initialLng = points[0] ? parseFloat(points[0].longitude) : -4.0;
        
        const map = L.map('speciesMap').setView([initialLat, initialLng], 7);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        // Plot circle markers for performance and aesthetics
        points.forEach(pt => {
            if (pt.latitude && pt.longitude) {
                const locality = pt.locality ? pt.locality : 'Location not recorded';
                const obsDate = pt.observation_date ? pt.observation_date : 'Date not recorded';
                const count = pt.individual_count ? pt.individual_count : '1';
                
                const circle = L.circleMarker([parseFloat(pt.latitude), parseFloat(pt.longitude)], {
                    color: '#2b5c8f', // Steel Blue badge color
                    fillColor: '#2b5c8f',
                    fillOpacity: 0.5,
                    radius: 6,
                    weight: 1.5
                }).addTo(map);
                
                circle.bindPopup(`
                    <div style="font-family: var(--font-body); font-size: 0.9rem;">
                        <strong>${locality}</strong><br>
                        <span style="color: var(--color-text-muted);">Date: ${obsDate}</span><br>
                        <strong>Count:</strong> ${count}
                    </div>
                `);
            }
        });
    });
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
