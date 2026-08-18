<?php
/**
 * Automated Verification Script - Scottish Mammal Observations Database
 * Verifies system configuration, database tables, and seeded content.
 *
 * SET08101 Web Technologies Coursework
 */

require_once __DIR__ . '/../includes/db.php';

echo "=== STARTING SYSTEM INTEGRATION CHECKS ===\n\n";

try {
    // Check 1: Database Connection
    echo "Check 1: Database Connection... ";
    $pdo = getDbConnection();
    echo "CONNECTED!\n";

    // Check 2: Table Existence Checks
    echo "Check 2: Relational Tables Validation... ";
    $requiredTables = ['species', 'observations', 'users'];
    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
        $stmt->execute([DB_NAME, $table]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Required table '$table' is missing in database.");
        }
    }
    echo "VALID!\n";

    // Check 3: Species Count Check (Expects 34)
    echo "Check 3: Species Record Volume... ";
    $speciesCount = $pdo->query('SELECT COUNT(*) FROM species')->fetchColumn();
    if ($speciesCount != 34) {
        throw new Exception("Expected 34 species, found $speciesCount.");
    }
    echo "PASS ($speciesCount species)\n";

    // Check 4: Observations Count Check (Expects 3863)
    echo "Check 4: Occurrence Sighting Records Volume... ";
    $obsCount = $pdo->query('SELECT COUNT(*) FROM observations')->fetchColumn();
    if ($obsCount != 3863) {
         throw new Exception("Expected 3,863 observations, found $obsCount.");
    }
    echo "PASS ($obsCount records)\n";

    // Check 5: Admin Account Verification
    echo "Check 5: Administrator Credentials Presence... ";
    $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($adminCount != 1) {
        throw new Exception("Admin user 'admin' not found in database.");
    }
    echo "PASS (admin account active)\n";

    echo "\n=== ALL VERIFICATIONS PASSED SUCCESSFULLY ===\n";
    exit(0);

} catch (Exception $e) {
    echo "FAILED!\n";
    echo "Error Detail: " . $e->getMessage() . "\n";
    exit(1);
}
