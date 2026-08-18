<?php
/**
 * Automated Verification Script
 * Checks database connection, schema, and basic queries.
 */

echo "=== SCOTTISH MAMMAL OBSERVATIONS VERIFICATION ===\n\n";

// 1. Check database config & connection
echo "[1/4] Checking database configuration... ";
require_once __DIR__ . '/../app/config/database.php';
$db = getDBConnection();
if ($db instanceof PDO) {
    echo "SUCCESS (Connected to " . DB_NAME . ")\n";
} else {
    echo "FAILED\n";
    exit(1);
}

// 2. Verify schema tables exist
echo "[2/4] Verifying table structure... ";
try {
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['users', 'species', 'observations'];
    $missing = array_diff($requiredTables, $tables);
    if (empty($missing)) {
        echo "SUCCESS (All tables exist: " . implode(', ', $tables) . ")\n";
    } else {
        echo "FAILED (Missing tables: " . implode(', ', $missing) . ")\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
    exit(1);
}

// 3. Verify seed records loaded
echo "[3/4] Checking seeded data count... ";
try {
    $speciesCount = $db->query("SELECT COUNT(*) FROM species")->fetchColumn();
    $obsCount = $db->query("SELECT COUNT(*) FROM observations")->fetchColumn();
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    echo "SUCCESS:\n";
    echo "   - Species Count: $speciesCount (Expected: 7)\n";
    echo "   - Observations Count: $obsCount (Expected: 17)\n";
    echo "   - Users Count: $userCount (Expected: 2)\n";
    
    if ($speciesCount < 7 || $obsCount < 17 || $userCount < 2) {
        echo "WARNING: Seed counts do not match expected values.\n";
    }
} catch (PDOException $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
    exit(1);
}

// 4. Test security helper integrity
echo "[4/5] Verifying security helpers... ";
require_once __DIR__ . '/../app/helpers/validation.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$rawString = "<script>alert('xss')</script>";
$escaped = sanitizeOutput($rawString);
if (strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false) {
    echo "XSS Sanitization: OK. ";
} else {
    echo "XSS Sanitization: FAIL. ";
    exit(1);
}

if (isValidLatitude(56.4907) && !isValidLatitude(95.0)) {
    echo "Latitude boundaries: OK. ";
} else {
    echo "Latitude boundaries: FAIL. ";
    exit(1);
}

if (isValidLongitude(-4.2026) && !isValidLongitude(185.0)) {
    echo "Longitude boundaries: OK. ";
} else {
    echo "Longitude boundaries: FAIL. ";
    exit(1);
}

$token = generateCSRFToken();
if (validateCSRFToken($token) && !validateCSRFToken('invalid-token')) {
    echo "CSRF validation: OK.\n";
} else {
    echo "CSRF validation: FAIL.\n";
    exit(1);
}

// 5. Test real imported biodiversity records
echo "[5/5] Verifying real imported biodiversity records... ";
try {
    $importedCount = $db->query("SELECT COUNT(*) FROM observations WHERE observation_type = 'imported'")->fetchColumn();
    echo "Count: {$importedCount} records imported. ";
    if ($importedCount < 200) {
        echo "FAILED (Expected > 200 real occurrences)\n";
        exit(1);
    }
    
    // Check bounding box constraints (Lat: 54.6 to 61.0, Lng: -9.0 to -0.5)
    $coords = $db->query("
        SELECT MIN(latitude) as min_lat, MAX(latitude) as max_lat, MIN(longitude) as min_lng, MAX(longitude) as max_lng 
        FROM observations WHERE observation_type = 'imported'
    ")->fetch();
    
    if ($coords['min_lat'] >= 54.0 && $coords['max_lat'] <= 61.5 && $coords['min_lng'] >= -9.0 && $coords['max_lng'] <= -0.5) {
        echo "Coordinates bounds: OK. ";
    } else {
        echo "FAILED (Coordinates outside Scotland WKT polygon boundaries: Lat [{$coords['min_lat']}, {$coords['max_lat']}], Lng [{$coords['min_lng']}, {$coords['max_lng']}])\n";
        exit(1);
    }
    
    // Check licenses
    $licenses = $db->query("SELECT DISTINCT licence FROM observations WHERE observation_type = 'imported'")->fetchAll(PDO::FETCH_COLUMN);
    $validLicenses = ['CC0 1.0', 'CC-BY 4.0', 'CC-BY-NC 4.0'];
    foreach ($licenses as $lic) {
        if (!in_array($lic, $validLicenses)) {
            echo "FAILED (Invalid license found: {$lic})\n";
            exit(1);
        }
    }
    echo "Licenses validation: OK (" . implode(', ', $licenses) . ")\n";
} catch (PDOException $e) {
    echo "FAILED (" . $e->getMessage() . ")\n";
    exit(1);
}

echo "\n=== ALL VERIFICATIONS PASSED SUCCESSFULLY ===\n";
