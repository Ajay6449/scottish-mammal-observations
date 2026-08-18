# Real Biodiversity Data Integration - Implementation Plan

Extend the **Scottish Mammal Observations** platform by integrating real, publicly available Scottish mammal observation data from an authoritative biodiversity source (GBIF Occurrence API). This will replace the majority of fabricated historical sightings with verified scientific records while maintaining the public user submission functionality.

---

## Proposed Changes

### 1. Database Schema Extension
We will alter the `observations` table to support data provenance tracking.

#### [MODIFY] [schema.sql](file:///e:/webdesign-part2/database/schema.sql)
We will add columns to the schema to track the source of the observation:
*   `observation_type` ENUM('imported', 'user_submitted') DEFAULT 'user_submitted'
*   `source_dataset` VARCHAR(255) NULL
*   `source_record_id` VARCHAR(100) NULL
*   `source_url` VARCHAR(255) NULL
*   `licence` VARCHAR(100) NULL
*   `data_provider` VARCHAR(255) NULL

We will also add an index on `observation_type` and `source_record_id` for optimized query performance.

---

### 2. Data Importer Pipeline (`database/imports/`)
We will create a reproducible, automated import pipeline.

#### [NEW] [import_mammals.php](file:///e:/webdesign-part2/database/imports/import_mammals.php)
This PHP script will run inside WSL and:
1.  Query the **GBIF Occurrence API** for each of the 7 registered species.
2.  Filter the search to the United Kingdom (`country=GB`) and a bounding box strictly enclosing Scotland:
    `POLYGON((-8.6 54.6, -0.7 54.6, -0.7 60.9, -8.6 60.9, -8.6 54.6))`
3.  Filter out records with missing coordinates or coordinate uncertainty above 10km.
4.  Assert license safety, checking if the record's license is open (CC0, CC-BY, or CC-BY-NC 4.0 if appropriate for education).
5.  Clean and normalize data values (mapping dates, location names, and coordinates).
6.  Safely insert the records into the database using PDO prepared statements, preventing duplicates via the unique `source_record_id` index.
7.  Display an import summary report.

#### [NEW] [README.md](file:///e:/webdesign-part2/database/imports/README.md)
Documenting dataset descriptions, download dates, query configurations, licenses, and provenance requirements.

---

### 3. Frontend UI Updates & Provenance Indicators
We will adapt the UI to distinguish between imported scientific data and community sightings.

#### [MODIFY] [style.css](file:///e:/webdesign-part2/assets/css/style.css)
*   Define styles for "imported" vs "user-submitted" tags.
*   Configure marker color rules for Leaflet (e.g. green for community sightings, steel blue for scientific records).

#### [MODIFY] [index.php](file:///e:/webdesign-part2/public/index.php)
*   Include counts of user-submitted sightings and verified scientific records in the platform summary.
*   Update the map preview to show both categories with unique pins.

#### [MODIFY] [observations.php](file:///e:/webdesign-part2/public/observations.php)
*   Display a column indicating "Sighting Type" (Imported Datasets vs User Sighting).
*   Add a filter to let users browse only imported data or only user-submitted sightings.

#### [MODIFY] [species-detail.php](file:///e:/webdesign-part2/public/species-detail.php)
*   Display observation markers and records on the species page with distinct provenance indicators and license attributions.

#### [MODIFY] [map-view.js](file:///e:/webdesign-part2/assets/js/map-view.js)
*   Update Leaflet pin styles based on the `observation_type` field.
*   Render license and data provider links in the marker popups.

#### [MODIFY] [stats.js](file:///e:/webdesign-part2/assets/js/stats.js)
*   Ensure Chart.js graphs include all imported sightings in aggregate counts, and update summary calculations dynamically.

---

### 4. Admin Portal Updates
#### [MODIFY] [admin/observations-manage.php](file:///e:/webdesign-part2/public/admin/observations-manage.php)
*   Ensure the moderation panel lets administrators delete or edit imported records safely, displaying their source details and locking them from unauthorized status modifications (imported records remain pre-approved).

---

## Open Questions / Review

> [!NOTE]
> - **License Inclusion**: The GBIF API returns license URIs. We will map them to readable strings (e.g., `CC-BY 4.0` or `CC0 1.0`) and display them in popups and lists to meet attribution requirements.
> - **Volume of Data**: We propose fetching **35 records per species** (totaling ~245 real records). This keeps the local database compact, loads quickly on Leaflet map markers, and avoids API or server timeout issues.

---

## Verification Plan

### Automated Checks
*   Verify PHP importer syntax: `php -l database/imports/import_mammals.php`.
*   Validate DB updates inside `tests/verify.php` to assert that `observation_type` is populated, coordinates are within bounds, and license/provenance fields are correctly inserted.

### Manual Verification
1.  Run `import_mammals.php` inside WSL and check console summary output.
2.  Open the Home page and check the platform summary count update (shows imported + user records).
3.  Open the Sighting Map and verify that both types of markers (green and steel blue) display with correct attribution text in popups.
4.  Filter the Observations Directory by "Sighting Type: Imported" and confirm correct filtering and pagination.
