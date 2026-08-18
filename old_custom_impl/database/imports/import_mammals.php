<?php
/**
 * Scottish Mammal Observations - Biodiversity Data Importer
 * Queries the GBIF Occurrence API for real Scottish mammal sighting data.
 */

// Enable console-friendly error reporting
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Ensure run from CLI only
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

echo "========================================================\n";
echo "SCOTTISH MAMMAL OBSERVATIONS - DATA PIPELINE IMPORTER   \n";
echo "Source: Global Biodiversity Information Facility (GBIF) \n";
echo "========================================================\n\n";

$db = getDBConnection();

// Fetch species profiles currently in DB to map taxonomic occurrences
try {
    $stmt = $db->query("SELECT id, common_name, scientific_name FROM species");
    $speciesList = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Failed to fetch species list from database: " . $e->getMessage() . "\n");
}

if (empty($speciesList)) {
    die("No species records found in the database. Please seed species first.\n");
}

// Bounding box strictly covering Scotland (WKT Polygon)
// coordinates ordered counter-clockwise for GBIF winding rules
$scotlandGeometry = "POLYGON((-8.6 54.6, -0.7 54.6, -0.7 60.9, -8.6 60.9, -8.6 54.6))";

$importedTotal = 0;
$skippedTotal = 0;
$errorsTotal = 0;

// Loop through each species in database and fetch records from GBIF API
foreach ($speciesList as $spec) {
    $speciesId = $spec['id'];
    $commonName = $spec['common_name'];
    $scientificName = $spec['scientific_name'];
    
    echo "Processing species: '{$commonName}' ({$scientificName})...\n";
    
    // Construct GBIF search query URL
    // We request up to 35 occurrences for each species to build a solid local dataset
    $queryParams = [
        'scientificName' => $scientificName,
        'country' => 'GB',
        'geometry' => $scotlandGeometry,
        'hasCoordinate' => 'true', // exclude records without coordinates
        'hasGeospatialIssue' => 'false', // exclude bad coordinates
        'limit' => '35'
    ];
    
    $url = "https://api.gbif.org/v1/occurrence/search?" . http_build_query($queryParams);
    
    echo " -> Fetching data from GBIF API... ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ScottishMammalObservationsPlatform/1.0 (Contact: shaikbashah20@gmail.com)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200 || !$response) {
        echo "FAILED (HTTP Code: {$httpCode})\n";
        $errorsTotal++;
        continue;
    }
    
    $data = json_decode($response, true);
    if (!isset($data['results'])) {
        echo "FAILED (Malformed JSON response)\n";
        $errorsTotal++;
        continue;
    }
    
    $records = $data['results'];
    $count = count($records);
    echo "SUCCESS (Found {$count} records)\n";
    
    $speciesImported = 0;
    $speciesSkipped = 0;
    
    foreach ($records as $record) {
        $gbifId = $record['key'] ?? null;
        if (!$gbifId) {
            $speciesSkipped++;
            continue;
        }
        
        // 1. License Check
        // GBIF licenses are usually creative commons URIs
        $licenseUri = $record['license'] ?? '';
        $licenseName = 'Unknown';
        
        if (strpos($licenseUri, 'publicdomain/zero') !== false || strpos($licenseUri, '/zero/') !== false) {
            $licenseName = 'CC0 1.0';
        } elseif (strpos($licenseUri, '/by/4.0') !== false) {
            $licenseName = 'CC-BY 4.0';
        } elseif (strpos($licenseUri, '/by-nc/4.0') !== false) {
            $licenseName = 'CC-BY-NC 4.0'; // Acceptable for educational demo platform
        } else {
            // If license is restricted/unknown, skip it to ensure legal safety
            $speciesSkipped++;
            continue;
        }
        
        // 2. Coordinate Extraction and precision check
        $lat = $record['decimalLatitude'] ?? null;
        $lng = $record['decimalLongitude'] ?? null;
        
        if ($lat === null || $lng === null) {
            $speciesSkipped++;
            continue;
        }
        
        // Skip sensitive records where exact coordinates have been obscured
        if (isset($record['coordinateUncertaintyInMeters']) && $record['coordinateUncertaintyInMeters'] > 10000) {
            // Skip coordinates with > 10km uncertainty range (coarse data)
            $speciesSkipped++;
            continue;
        }
        
        // 3. Location Name parsing
        $location = $record['locality'] ?? '';
        if (empty($location)) {
            $location = $record['stateProvince'] ?? '';
        }
        if (empty($location)) {
            $location = 'Scotland (Generalized Location)';
        }
        $location = trim(preg_replace('/\s+/', ' ', $location));
        
        // 4. Date Normalization
        $dateStr = $record['eventDate'] ?? '';
        if (empty($dateStr) && isset($record['year'])) {
            $month = str_pad($record['month'] ?? '01', 2, '0', STR_PAD_LEFT);
            $day = str_pad($record['day'] ?? '01', 2, '0', STR_PAD_LEFT);
            $dateStr = "{$record['year']}-{$month}-{$day}";
        }
        
        // Parse date to clean SQL format
        $timestamp = strtotime($dateStr);
        if (!$timestamp) {
            // Default fallback if date parsing fails
            $timestamp = time();
        }
        $formattedDate = date('Y-m-d', $timestamp);
        
        // Ensure date is not in the future
        if (strtotime($formattedDate) > time()) {
            $formattedDate = date('Y-m-d');
        }
        
        // 5. Observer/Publisher Attribution
        $observer = $record['recordedBy'] ?? '';
        if (empty($observer)) {
            $observer = $record['institutionCode'] ?? '';
        }
        if (empty($observer)) {
            $observer = $record['publishingOrgName'] ?? 'GBIF Public Dataset';
        }
        // Truncate to match DB column size
        $observer = substr(trim($observer), 0, 100);
        
        // 6. Dataset Provenance Metadata
        $datasetKey = $record['datasetKey'] ?? '';
        $datasetName = $record['datasetName'] ?? 'Public GBIF Occurrence Dataset';
        $datasetName = substr(trim($datasetName), 0, 255);
        $recordUrl = "https://www.gbif.org/occurrence/" . $gbifId;
        $dataProvider = $record['publisher'] ?? 'GBIF Publisher';
        
        // 7. Field Notes
        $notes = [];
        if (!empty($record['individualCount'])) {
            $notes[] = "Count: " . $record['individualCount'];
        }
        if (!empty($record['basisOfRecord'])) {
            $notes[] = "Basis: " . $record['basisOfRecord'];
        }
        if (!empty($record['occurrenceRemarks'])) {
            $notes[] = "Remarks: " . $record['occurrenceRemarks'];
        }
        $notesText = implode(" | ", $notes);
        if (empty($notesText)) {
            $notesText = "Verified scientific occurrence report from GBIF network.";
        }
        
        // 8. Safely Insert/Update MySQL using PDO Prepared Statement
        try {
            // Check if record exists
            $checkStmt = $db->prepare("SELECT id FROM observations WHERE source_record_id = :src_id");
            $checkStmt->execute(['src_id' => $gbifId]);
            $exists = $checkStmt->fetch();
            
            if ($exists) {
                // If it already exists, do not duplicate
                $speciesSkipped++;
                continue;
            }
            
            // Insert
            $insertStmt = $db->prepare("
                INSERT INTO observations 
                (species_id, observer_name, observation_date, latitude, longitude, location_name, notes, status, observation_type, source_dataset, source_record_id, source_url, licence, data_provider) 
                VALUES 
                (:species_id, :observer, :date, :lat, :lng, :loc_name, :notes, 'approved', 'imported', :src_dataset, :src_record_id, :src_url, :licence, :provider)
            ");
            
            $insertStmt->execute([
                'species_id' => $speciesId,
                'observer' => $observer,
                'date' => $formattedDate,
                'lat' => $lat,
                'lng' => $lng,
                'loc_name' => $location,
                'notes' => $notesText,
                'src_dataset' => $datasetName,
                'src_record_id' => $gbifId,
                'src_url' => $recordUrl,
                'licence' => $licenseName,
                'provider' => $dataProvider
            ]);
            
            $speciesImported++;
            $importedTotal++;
        } catch (PDOException $e) {
            // Log insert errors and continue
            echo "   [ERROR] Failed to insert record ID {$gbifId}: " . $e->getMessage() . "\n";
            $errorsTotal++;
        }
    }
    
    echo " -> Completed: {$speciesImported} imported, {$speciesSkipped} skipped.\n\n";
}

echo "========================================================\n";
echo "IMPORT COMPLETED SUMMARY                                \n";
echo "========================================================\n";
echo "Total Records Imported: {$importedTotal}\n";
echo "Total Records Skipped:  {$skippedTotal}\n";
echo "Total Insertion Errors: {$errorsTotal}\n";
echo "========================================================\n";
