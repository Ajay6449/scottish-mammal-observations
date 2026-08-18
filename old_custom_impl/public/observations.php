<?php
/**
 * Scottish Mammal Observations - Observations Browser
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';

$db = getDBConnection();

// Pagination Config
$recordsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Filters from GET
$searchQuery = isset($_GET['q']) ? cleanInput($_GET['q']) : '';
$speciesFilter = isset($_GET['species_id']) ? (int)$_GET['species_id'] : 0;
$typeFilter = isset($_GET['type']) ? cleanInput($_GET['type']) : '';
$startDate = isset($_GET['start_date']) ? cleanInput($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? cleanInput($_GET['end_date']) : '';

// Base query parts
$whereClause = "WHERE o.status = 'approved'";
$params = [];

if (!empty($searchQuery)) {
    $whereClause .= " AND (o.location_name LIKE :search OR o.observer_name LIKE :search OR o.notes LIKE :search)";
    $params['search'] = '%' . $searchQuery . '%';
}

if ($speciesFilter > 0) {
    $whereClause .= " AND o.species_id = :species_id";
    $params['species_id'] = $speciesFilter;
}

if (!empty($typeFilter)) {
    $whereClause .= " AND o.observation_type = :type";
    $params['type'] = $typeFilter;
}

if (!empty($startDate)) {
    $whereClause .= " AND o.observation_date >= :start_date";
    $params['start_date'] = $startDate;
}

if (!empty($endDate)) {
    $whereClause .= " AND o.observation_date <= :end_date";
    $params['end_date'] = $endDate;
}

// 1. Get total record count for pagination links
try {
    $countSql = "
        SELECT COUNT(*) 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        $whereClause
    ";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
} catch (\PDOException $e) {
    $totalRecords = 0;
}

$totalPages = max(1, ceil($totalRecords / $recordsPerPage));
$currentPage = min($currentPage, $totalPages); // boundary correction
$offset = ($currentPage - 1) * $recordsPerPage;

// 2. Fetch paginated observations
try {
    $dataSql = "
        SELECT o.*, s.common_name, s.scientific_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        $whereClause 
        ORDER BY o.observation_date DESC, o.id DESC 
        LIMIT $recordsPerPage OFFSET $offset
    ";
    $stmt = $db->prepare($dataSql);
    $stmt->execute($params);
    $observationsList = $stmt->fetchAll();
} catch (\PDOException $e) {
    $observationsList = [];
}

// 3. Fetch all approved observations for this filter to display on map (un-paginated, limit 150 for performance)
try {
    $mapSql = "
        SELECT o.latitude, o.longitude, o.location_name, o.observation_date, o.observer_name, o.observation_type, o.source_url, o.licence, o.data_provider, s.common_name 
        FROM observations o 
        JOIN species s ON o.species_id = s.id 
        $whereClause 
        ORDER BY o.observation_date DESC 
        LIMIT 150
    ";
    $stmt = $db->prepare($mapSql);
    $stmt->execute($params);
    $mapObservations = $stmt->fetchAll();
} catch (\PDOException $e) {
    $mapObservations = [];
}

$markersJson = json_encode($mapObservations);

// Fetch species list for dropdown
try {
    $stmt = $db->query("SELECT id, common_name FROM species ORDER BY common_name ASC");
    $speciesDropdown = $stmt->fetchAll();
} catch (\PDOException $e) {
    $speciesDropdown = [];
}

// Prepare URL query parameters for pagination links
$queryParams = $_GET;
unset($queryParams['page']); // page gets appended dynamically in loop

$pageTitle = "Observations Browser | Scottish Mammal Observations";
$pageDescription = "Search, filter, and review field observations of mammals across Scotland. View mapping data and date-specific sighting logs.";
$loadMap = true;

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl);">
        <h1>Field Observations Directory</h1>
        <p style="color: var(--color-text-muted); font-size: 1.1rem; max-width: 800px;">
            Search and filter sightings submitted by field researchers and the general public. Use the map to explore regions and the list below for detailed notes.
        </p>
    </div>

    <!-- Filters and Search Bar Form -->
    <form action="/observations.php" method="GET" class="filters-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); align-items: end; gap: var(--spacing-md);">
        <div class="form-group">
            <label for="q">Search Text</label>
            <input type="search" name="q" id="q" class="form-control" placeholder="Search location, observer, notes..." value="<?php echo sanitizeOutput($searchQuery); ?>">
        </div>
        
        <div class="form-group">
            <label for="species_id">Filter by Species</label>
            <select name="species_id" id="species_id" class="form-control">
                <option value="">All Species</option>
                <?php foreach ($speciesDropdown as $spec): ?>
                    <option value="<?php echo $spec['id']; ?>" <?php echo ($speciesFilter === (int)$spec['id']) ? 'selected' : ''; ?>>
                        <?php echo sanitizeOutput($spec['common_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="type">Sighting Type</label>
            <select name="type" id="type" class="form-control">
                <option value="">All Sightings</option>
                <option value="imported" <?php echo ($typeFilter === 'imported') ? 'selected' : ''; ?>>Scientific Records (GBIF)</option>
                <option value="user_submitted" <?php echo ($typeFilter === 'user_submitted') ? 'selected' : ''; ?>>Community Sightings</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo sanitizeOutput($startDate); ?>">
        </div>
        
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo sanitizeOutput($endDate); ?>">
        </div>
        
        <div style="display: flex; gap: var(--spacing-xs); margin-bottom: 0;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">Apply</button>
            <a href="/observations.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; height: 100%;">Reset</a>
        </div>
    </form>

    <!-- Map section showing filtered markers -->
    <section class="map-section">
        <h2>Sightings Distribution (<?php echo count($mapObservations); ?> plotted)</h2>
        <div class="map-container" id="recent-map"></div>
    </section>

    <!-- Paginated Sightings Table -->
    <section style="margin-top: var(--spacing-xxl);">
        <h2>Sighting Records Log (<?php echo $totalRecords; ?> total)</h2>
        
        <?php if (empty($observationsList)): ?>
            <div class="alert alert-info" role="alert">
                No observations match your filter criteria. Try expanding your search terms or date range.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Species</th>
                            <th scope="col">Sighting Type</th>
                            <th scope="col">Location Name</th>
                            <th scope="col">Coordinates</th>
                            <th scope="col">Date Spotted</th>
                            <th scope="col">Observer / Source</th>
                            <th scope="col">Field Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($observationsList as $obs): ?>
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
                                <td><code style="background: #f0ede6; padding: 2px 6px; border-radius: 3px; font-size: 0.85rem;"><?php echo number_format($obs['latitude'], 4); ?>, <?php echo number_format($obs['longitude'], 4); ?></code></td>
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
                                <td style="font-size: 0.85rem; color: var(--color-text-muted); max-width: 250px;"><?php echo sanitizeOutput($obs['notes']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Observations Pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="/observations.php?<?php echo http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])); ?>" class="pagination-link" aria-label="Previous Page">&laquo;</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/observations.php?<?php echo http_build_query(array_merge($queryParams, ['page' => $i])); ?>" class="pagination-link <?php echo ($currentPage === $i) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="/observations.php?<?php echo http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])); ?>" class="pagination-link" aria-label="Next Page">&raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<script>
    window.mapMarkersData = <?php echo $markersJson; ?>;
</script>
<script src="/assets/js/map-view.js"></script>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
