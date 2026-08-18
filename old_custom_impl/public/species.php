<?php
/**
 * Scottish Mammal Observations - Species Directory
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/media.php';

$db = getDBConnection();

// Get filters from GET request
$searchQuery = isset($_GET['q']) ? cleanInput($_GET['q']) : '';
$habitatFilter = isset($_GET['habitat']) ? cleanInput($_GET['habitat']) : '';

// Build SQL query based on filters
$sql = "SELECT * FROM species WHERE 1=1";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (common_name LIKE :search OR scientific_name LIKE :search OR description LIKE :search)";
    $params['search'] = '%' . $searchQuery . '%';
}

if (!empty($habitatFilter)) {
    $sql .= " AND habitat = :habitat";
    $params['habitat'] = $habitatFilter;
}

$sql .= " ORDER BY common_name ASC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $speciesList = $stmt->fetchAll();
} catch (\PDOException $e) {
    $speciesList = [];
}

// Fetch all distinct habitats for the filter dropdown
try {
    $stmt = $db->query("SELECT DISTINCT habitat FROM species ORDER BY habitat ASC");
    $habitats = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    $habitats = [];
}

$pageTitle = "Species Directory | Scottish Mammal Observations";
$pageDescription = "Browse and search Scotland's native mammal species, learn about their habitats, diet, size, and conservation status.";

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container">
    <div style="margin-bottom: var(--spacing-xl);">
        <h1>Scottish Mammal Directory</h1>
        <p style="color: var(--color-text-muted); font-size: 1.1rem; max-width: 800px;">
            Explore details about native Scottish land and marine mammals. Learn about their lifespans, diets, typical weights, and their habitats across Scotland.
        </p>
    </div>

    <!-- Search and Filters Bar (Progressive Enhancement ready) -->
    <form action="/species.php" method="GET" class="filters-bar" id="species-filter-form">
        <div class="form-group">
            <label for="q">Search Species</label>
            <input type="search" name="q" id="q" class="form-control" placeholder="Search by name, scientific name, description..." value="<?php echo sanitizeOutput($searchQuery); ?>" aria-label="Search species">
        </div>
        
        <div class="form-group">
            <label for="habitat">Filter by Habitat</label>
            <select name="habitat" id="habitat" class="form-control">
                <option value="">All Habitats</option>
                <?php foreach ($habitats as $hab): ?>
                    <option value="<?php echo sanitizeOutput($hab); ?>" <?php echo ($habitatFilter === $hab) ? 'selected' : ''; ?>>
                        <?php echo sanitizeOutput($hab); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; gap: var(--spacing-xs);">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <?php if (!empty($searchQuery) || !empty($habitatFilter)): ?>
                <a href="/species.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; height: 100%;">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Species Directory Listing Grid -->
    <div id="species-grid-container">
        <?php if (empty($speciesList)): ?>
            <div class="alert alert-info" role="alert" style="justify-content: center; padding: var(--spacing-xl); text-align: center; flex-direction: column;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: var(--spacing-sm);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <h3 style="margin-top: 0; color: inherit;">No Mammals Found</h3>
                <p style="margin-bottom: 0;">We couldn't find any species matching "<strong><?php echo sanitizeOutput($searchQuery); ?></strong>" <?php echo !empty($habitatFilter) ? 'in ' . sanitizeOutput($habitatFilter) : ''; ?>.</p>
                <a href="/species.php" class="btn btn-primary" style="margin-top: var(--spacing-md);">Reset Search Filters</a>
            </div>
        <?php else: ?>
            <div class="grid" id="species-cards-grid">
                <?php foreach ($speciesList as $spec): ?>
                    <article class="card species-item" data-name="<?php echo sanitizeOutput(strtolower($spec['common_name'])); ?>" data-scientific="<?php echo sanitizeOutput(strtolower($spec['scientific_name'])); ?>" data-habitat="<?php echo sanitizeOutput(strtolower($spec['habitat'])); ?>">
                        <div class="card-img-container">
                            <img src="<?php echo getSpeciesImage($spec['image_path'], $spec['common_name']); ?>" alt="Photograph of a wild <?php echo sanitizeOutput($spec['common_name']); ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo sanitizeOutput($spec['common_name']); ?></h3>
                            <div class="scientific-name"><?php echo sanitizeOutput($spec['scientific_name']); ?></div>
                            <span class="habitat-tag"><?php echo sanitizeOutput($spec['habitat']); ?></span>
                            <p><?php echo sanitizeOutput(substr($spec['description'], 0, 150)) . '...'; ?></p>
                            
                            <div style="border-top: 1px solid var(--color-border); margin-top: auto; padding-top: var(--spacing-md); display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--color-text-muted);">
                                <div><strong>Status:</strong> <?php echo sanitizeOutput($spec['conservation_status']); ?></div>
                                <div><strong>Weight:</strong> <?php echo sanitizeOutput($spec['average_weight']); ?></div>
                            </div>
                            
                            <a href="/species-detail.php?id=<?php echo $spec['id']; ?>" class="btn btn-primary" style="margin-top: var(--spacing-md); text-align: center;">View Full Profile</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Progressive Enhancement: Client-side instant filter enhancement -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('q');
    const habitatSelect = document.getElementById('habitat');
    const cards = document.querySelectorAll('.species-item');
    const noResultsAlert = document.querySelector('.alert-info');
    const gridContainer = document.getElementById('species-cards-grid');

    if (!gridContainer) return; // Skip if no cards loaded

    function filterCards() {
        const query = searchInput.value.toLowerCase().trim();
        const habitat = habitatSelect.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const cardName = card.getAttribute('data-name');
            const cardSci = card.getAttribute('data-scientific');
            const cardHab = card.getAttribute('data-habitat');

            const matchesSearch = !query || cardName.includes(query) || cardSci.includes(query);
            const matchesHabitat = !habitat || cardHab === habitat;

            if (matchesSearch && matchesHabitat) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle empty state if all cards are filtered out
        let alertEl = document.getElementById('js-no-results');
        if (visibleCount === 0) {
            if (!alertEl) {
                alertEl = document.createElement('div');
                alertEl.id = 'js-no-results';
                alertEl.className = 'alert alert-info';
                alertEl.role = 'alert';
                alertEl.style.justifyContent = 'center';
                alertEl.style.padding = 'var(--spacing-xl)';
                alertEl.style.textAlign = 'center';
                alertEl.style.flexDirection = 'column';
                alertEl.style.width = '100%';
                alertEl.innerHTML = `
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: var(--spacing-sm);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <h3 style="margin-top:0;">No Mammals Match Filters</h3>
                    <p style="margin-bottom:0;">Try adjusting your keyword search or habitat selection.</p>
                `;
                gridContainer.parentNode.insertBefore(alertEl, gridContainer);
            }
            gridContainer.style.display = 'none';
        } else {
            if (alertEl) alertEl.remove();
            gridContainer.style.display = '';
        }
    }

    // Attach instant keyup and change events for a responsive UX
    searchInput.addEventListener('input', filterCards);
    habitatSelect.addEventListener('change', filterCards);
});
</script>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
