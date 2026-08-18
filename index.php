<?php
/**
 * Scottish Mammal Observations Database - Home Page
 * Displays a list of all Scottish mammal species with search, filters, sorting, and Chart.js stats.
 *
 * SET08101 Web Technologies Coursework
 */

require_once 'includes/db.php';

$pageTitle = 'All Species';
$pageDescription = 'Search, filter, and monitor native Scottish mammal species, and analyze population statistics.';
$currentPage = 'home';
$loadCharts = true; // Signals header.php to load Chart.js

$pdo = getDbConnection();

// Fetch distinct categories for filter dropdowns
$dietCategories = $pdo->query('SELECT DISTINCT dietary_category FROM species WHERE dietary_category IS NOT NULL ORDER BY dietary_category')->fetchAll(PDO::FETCH_COLUMN);
$iucnCategories = $pdo->query('SELECT DISTINCT iucn_red_list_category FROM species WHERE iucn_red_list_category IS NOT NULL ORDER BY iucn_red_list_category')->fetchAll(PDO::FETCH_COLUMN);

// Build search and filter query
$where = [];
$params = [];

if (!empty($_GET['q'])) {
    $where[] = '(common_name LIKE :q1 OR species_name LIKE :q2)';
    $params[':q1'] = '%' . $_GET['q'] . '%';
    $params[':q2'] = '%' . $_GET['q'] . '%';
}

if (!empty($_GET['diet'])) {
    $where[] = 'dietary_category = :diet';
    $params[':diet'] = $_GET['diet'];
}

if (!empty($_GET['status'])) {
    $where[] = 'iucn_red_list_category = :status';
    $params[':status'] = $_GET['status'];
}

if (!empty($_GET['habitat'])) {
    $where[] = 'habitat LIKE :habitat';
    $params[':habitat'] = '%' . $_GET['habitat'] . '%';
}

// Define sorting parameters securely
$allowedSortColumns = ['common_name', 'species_name', 'iucn_red_list_category', 'dietary_category', 'body_mass_kg', 'habitat'];
$sort = in_array($_GET['sort'] ?? '', $allowedSortColumns) ? $_GET['sort'] : 'common_name';
$dir = ($_GET['dir'] ?? '') === 'desc' ? 'DESC' : 'ASC';
$nextDir = $dir === 'ASC' ? 'desc' : 'asc';

// Fetch species records
$sql = 'SELECT gbif_species_key, species_name, common_name, iucn_red_list_category, body_mass_kg, dietary_category, uk_protection_status, habitat, image_url FROM species';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY $sort $dir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$species = $stmt->fetchAll();

// Fetch statistics for dashboard
$totalSpecies = $pdo->query('SELECT COUNT(*) FROM species')->fetchColumn();
$totalObservations = $pdo->query('SELECT COUNT(*) FROM observations')->fetchColumn();

// Fetch Chart.js diet distribution data
$chartStmt = $pdo->query('SELECT dietary_category, COUNT(*) as count FROM species GROUP BY dietary_category');
$chartData = $chartStmt->fetchAll();
$chartLabels = [];
$chartCounts = [];
foreach ($chartData as $row) {
    $chartLabels[] = $row['dietary_category'] ?: 'Unclassified';
    $chartCounts[] = (int)$row['count'];
}

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<section class="hero">
    <div class="container">
        <h1>Scottish Mammal Directory</h1>
        <p>Explore occurrence data, conservation details, and geographic maps for terrestrial mammals across the Scottish Highlands.</p>
    </div>
</section>

<!-- Stats Dashboard Section -->
<div class="stats-grid">
    <div class="chart-card">
        <h3>Species Count by Dietary Category</h3>
        <div style="height: 220px; position: relative;">
            <canvas id="dietChart"></canvas>
        </div>
    </div>
    
    <div class="stats-summary-card">
        <div class="stat-number"><?php echo e($totalSpecies); ?></div>
        <div class="stat-label">Total Mammal Species</div>
        
        <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.15); margin: var(--spacing-md) 0;">
        
        <div class="stat-number"><?php echo number_format($totalObservations); ?></div>
        <div class="stat-label">Ingested GBIF Observations</div>
    </div>
</div>

<h2>Mammal Species Listing</h2>
<p style="margin-bottom: var(--spacing-lg);">Search and filter through the active catalog below. You can toggle between Grid View and Table View.</p>

<!-- Search and Filter Controls -->
<form method="GET" action="index.php" class="filters-bar">
    <div class="form-group">
        <label for="q">Search Name</label>
        <input type="text" id="q" name="q" class="form-control" placeholder="e.g. Otter, Badger..." value="<?php echo e($_GET['q'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="diet">Dietary Category</label>
        <select id="diet" name="diet" class="form-control">
            <option value="">All Diets</option>
            <?php foreach ($dietCategories as $cat): ?>
                <option value="<?php echo e($cat); ?>" <?php echo ($_GET['diet'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="status">Conservation Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">All Statuses</option>
            <?php foreach ($iucnCategories as $cat): ?>
                <option value="<?php echo e($cat); ?>" <?php echo ($_GET['status'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group" style="flex-grow: 0; min-width: auto; display: flex; gap: var(--spacing-sm);">
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="index.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<!-- View Toggles -->
<div style="display: flex; justify-content: flex-end; gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
    <button id="viewGridBtn" class="btn btn-secondary btn-sm" style="padding: var(--spacing-xs) var(--spacing-sm); font-size: 0.85rem;">Grid View</button>
    <button id="viewTableBtn" class="btn btn-primary btn-sm" style="padding: var(--spacing-xs) var(--spacing-sm); font-size: 0.85rem;">Table View</button>
</div>

<?php if (empty($species)): ?>
    <div class="alert alert-info">No species found matching the current search parameters.</div>
<?php else: ?>
    <!-- Table View Container -->
    <div id="tableView" class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>
                        <a href="index.php?sort=common_name&dir=<?php echo $sort === 'common_name' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Common Name <?php echo $sort === 'common_name' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="index.php?sort=species_name&dir=<?php echo $sort === 'species_name' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Scientific Name <?php echo $sort === 'species_name' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="index.php?sort=iucn_red_list_category&dir=<?php echo $sort === 'iucn_red_list_category' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Status <?php echo $sort === 'iucn_red_list_category' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="index.php?sort=dietary_category&dir=<?php echo $sort === 'dietary_category' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Diet <?php echo $sort === 'dietary_category' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="index.php?sort=body_mass_kg&dir=<?php echo $sort === 'body_mass_kg' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Weight <?php echo $sort === 'body_mass_kg' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="index.php?sort=habitat&dir=<?php echo $sort === 'habitat' ? $nextDir : 'asc'; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?>">
                            Habitat <?php echo $sort === 'habitat' ? ($dir === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($species as $sp): ?>
                    <tr>
                        <td style="width: 80px; padding: var(--spacing-sm);">
                            <?php if (!empty($sp['image_url'])): ?>
                                <img src="<?php echo e($sp['image_url']); ?>" alt="<?php echo e($sp['common_name']); ?>" style="width: 60px; height: 45px; object-fit: cover; border-radius: var(--border-radius-sm);">
                            <?php else: ?>
                                <div style="width: 60px; height: 45px; background: #e6e2db; border-radius: var(--border-radius-sm); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: var(--color-text-muted);">No Img</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo e($sp['common_name']); ?></strong></td>
                        <td><em><?php echo e($sp['species_name']); ?></em></td>
                        <td>
                            <span class="badge badge-user" style="background-color: #faf0d8; color: #b08a1c; border-color: #f5e4bd;">
                                <?php echo $sp['iucn_red_list_category'] ? e($sp['iucn_red_list_category']) : 'LC'; ?>
                            </span>
                        </td>
                        <td><?php echo e($sp['dietary_category']); ?></td>
                        <td><?php echo e($sp['body_mass_kg']); ?> kg</td>
                        <td><?php echo e($sp['habitat']); ?></td>
                        <td><a href="species.php?key=<?php echo e($sp['gbif_species_key']); ?>" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">View Profile</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Grid View Container (Initially Hidden) -->
    <div id="gridView" class="grid" style="display: none;">
        <?php foreach ($species as $sp): ?>
            <div class="card">
                <div class="card-img-container">
                    <?php if (!empty($sp['image_url'])): ?>
                        <img src="<?php echo e($sp['image_url']); ?>" alt="<?php echo e($sp['common_name']); ?>">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: #e6e2db; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">No Image Available</div>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <h3><?php echo e($sp['common_name']); ?></h3>
                    <div class="scientific-name"><?php echo e($sp['species_name']); ?></div>
                    <div class="habitat-tag"><?php echo e($sp['habitat']); ?></div>
                    <p>
                        <strong>Diet:</strong> <?php echo e($sp['dietary_category']); ?><br>
                        <strong>Conservation:</strong> <?php echo $sp['iucn_red_list_category'] ? e($sp['iucn_red_list_category']) : 'LC'; ?><br>
                        <strong>Body Mass:</strong> <?php echo e($sp['body_mass_kg']); ?> kg
                    </p>
                    <a href="species.php?key=<?php echo e($sp['gbif_species_key']); ?>" class="btn btn-primary" style="text-align: center; margin-top: auto;">View Full Profile</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Chart initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Toggling layouts
    const viewGridBtn = document.getElementById('viewGridBtn');
    const viewTableBtn = document.getElementById('viewTableBtn');
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');

    if (viewGridBtn && viewTableBtn && gridView && tableView) {
        viewGridBtn.addEventListener('click', () => {
            gridView.style.display = 'grid';
            tableView.style.display = 'none';
            viewGridBtn.classList.remove('btn-secondary');
            viewGridBtn.classList.add('btn-primary');
            viewTableBtn.classList.remove('btn-primary');
            viewTableBtn.classList.add('btn-secondary');
        });

        viewTableBtn.addEventListener('click', () => {
            gridView.style.display = 'none';
            tableView.style.display = 'block';
            viewTableBtn.classList.remove('btn-secondary');
            viewTableBtn.classList.add('btn-primary');
            viewGridBtn.classList.remove('btn-primary');
            viewGridBtn.classList.add('btn-secondary');
        });
    }

    // Chart.js Configuration
    const ctx = document.getElementById('dietChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Number of Species',
                data: <?php echo json_encode($chartCounts); ?>,
                backgroundColor: [
                    'rgba(74, 112, 60, 0.75)',  // Moss Green
                    'rgba(30, 63, 32, 0.75)',   // Forest Green
                    'rgba(207, 168, 68, 0.75)',  // Amber Gold
                    'rgba(158, 42, 43, 0.75)',   // Maroon Red
                    'rgba(43, 92, 143, 0.75)'   // Steel Blue
                ],
                borderColor: [
                    '#4a703c', '#1e3f20', '#cfa844', '#9e2a2b', '#2b5c8f'
                ],
                borderWidth: 1.5,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
