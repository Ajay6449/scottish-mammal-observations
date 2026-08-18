# Real Biodiversity Data Integration Report

This document reports on the integration of real, public biodiversity occurrence records for Scottish mammals into the **Scottish Mammal Observations** platform, replacing historical placeholder sightings.

---

## 1. Selected Dataset Details
*   **Dataset Selected**: GBIF Occurrences containing Scottish terrestrial mammal records
*   **Primary Candidate Source**: Global Biodiversity Information Facility (GBIF)
*   **Licensing Profile**: CC0 (Public Domain), CC-BY 4.0 (Attribution Required), and CC-BY-NC 4.0 (Non-commercial Attribution)
*   **Data Providers**: Multiple global and regional environmental agencies contributing to GBIF, including NatureScot, the British Trust for Ornithology, and local biological recording societies.
*   **Dataset URL**: [https://www.gbif.org/](https://www.gbif.org/)
*   **DOI / Search Citation**: [GBIF Occurrence Search API](https://api.gbif.org/v1/occurrence/search)
*   **Access/Download Date**: 17 August 2026

---

## 2. Selection Rationale
GBIF was selected over other candidates because:
1.  **Reliability & Authority**: GBIF is the leading international open-access infrastructure for species occurrence logs.
2.  **Geographic Precision**: Features high-fidelity GPS coordinate logging covering the Scottish mainland, Highlands, and islands.
3.  **Strict License Metadata**: Every record returns structured license URIs, letting the data pipeline programmatically assert compliance with open licenses.
4.  **Taxonomic Integrity**: Standardized scientific naming schema mapping directly to our existing database catalog.

---

## 3. Database Ingestion Pipeline

### Schema Extension
The `observations` database table was altered using the migration script [migrate.sql](file:///e:/webdesign-part2/database/migrate.sql) to support:
*   `observation_type`: `ENUM('imported', 'user_submitted')` to differentiate scientific data from community sightings.
*   `source_dataset`: The parent dataset name.
*   `source_record_id`: The unique occurrence key in GBIF (used for deduplication).
*   `source_url`: Link directly to the occurrence details on GBIF.org.
*   `licence`: The creative commons license name (e.g. `CC-BY 4.0`).
*   `data_provider`: The original publisher of the record.

Unique database index `uq_source_record (source_record_id)` was added to prevent duplication on multiple script runs.

---

## 4. Pipeline Ingestion Summary
*   **Total Records Queried**: 35 per species (7 species)
*   **Total Records Successfully Imported**: **226 records**
*   **Total Records Skipped/Deduplicated**: 19 records
*   **Total Errors**: 0

### Cleaning and Quality Assertion Steps
1.  **Geographic Boundaries Filtering**: Query parameters constrained coordinates to a bounding polygon strictly covering Scotland: `POLYGON((-8.6 54.6, -0.7 54.6, -0.7 60.9, -8.6 60.9, -8.6 54.6))`.
2.  **Location Normalization**: Locality names were cleaned of double spaces and falling back to administrative counties if local names were empty.
3.  **Coordinate Quality Filters**: Records showing coordinate uncertainty metrics exceeding 10km (`coordinateUncertaintyInMeters > 10000`) were skipped to protect mapping accuracy.
4.  **License Safety Assertion**: Inspected license URIs. Unknown or restrictive licenses were automatically ignored.
5.  **Date Normalization**: Re-aligned inconsistent calendar attributes into standard SQL `YYYY-MM-DD` strings.

---

## 5. Verification & Testing
*   **Automated Tests**: Ran [verify.php](file:///e:/webdesign-part2/tests/verify.php). Step 5 verified:
    *   Total imported rows >= 200.
    *   Coordinates are strictly inside Scotland's geographical bounds.
    *   Licenses are strictly open CC0, CC-BY, or CC-BY-NC.
    *   **Result**: All checks passed successfully.
*   **Map Rendering**: Verified custom interactive marker styling (Steel Blue pins for Scientific Records, Green pins for Community Sightings) and detailed popup attribution links.
