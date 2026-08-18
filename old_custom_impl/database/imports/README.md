# Scottish Mammal Sighting Data Pipeline

This directory contains the automated data pipeline code used to fetch, filter, clean, and ingest real, authoritative Scottish mammal occurrence sightings from the Global Biodiversity Information Facility (GBIF).

---

## 1. Pipeline Importer Script
The script [import_mammals.php](file:///e:/webdesign-part2/database/imports/import_mammals.php) is written in PHP CLI. It loops through the species profiles registered in the MySQL database and queries the **GBIF Occurrence Search API** using taxonomic filters, geographical bounding envelopes, and strict licensing parameters.

### Execution Command
Run the importer from the command line:
```bash
wsl php /mnt/e/webdesign-part2/database/imports/import_mammals.php
```

---

## 2. API Search Configuration
*   **Source Platform**: [GBIF Network](https://www.gbif.org/) (Global Biodiversity Information Facility API v1)
*   **Geographic Coverage**: Scotland bounding box coordinate polygon:
    `POLYGON((-8.6 54.6, -0.7 54.6, -0.7 60.9, -8.6 60.9, -8.6 54.6))`
*   **Country Filter**: `GB` (Great Britain)
*   **Coordinate Filter**: `hasCoordinate = true` & `hasGeospatialIssue = false`
*   **Volume Limit**: Max `35` records per species, totaling up to ~245 verified records.

---

## 3. Data Cleaning & Normalization Steps
1.  **Duplicate Detection**: Uses the unique GBIF key `key` as the `source_record_id` with a database unique index constraint to prevent duplicate occurrence logs.
2.  **Date Alignment**: Event occurrence timestamps are extracted and formatted into SQL standard `YYYY-MM-DD` strings.
3.  **Coordinate Quality Assertion**: Checks `coordinateUncertaintyInMeters` metadata. Records with mapping uncertainty ranges exceeding 10km (generalized sensitive coordinates) are skipped to protect spatial precision.
4.  **License Verification**: The script inspects the license URI metadata. Only records carrying open-access Creative Commons licenses are parsed:
    *   `CC0 1.0` (Public Domain)
    *   `CC-BY 4.0` (Attribution Required)
    *   `CC-BY-NC 4.0` (Non-commercial Attribution - acceptable for this academic platform)
    *   *All other restricted or unknown licenses are skipped.*
5.  **Attribution Mapping**: Resolves publisher names, institution codes, dataset titles, and publisher keys to store clear licensing provenance in the `observations` database table.
