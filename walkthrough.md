# Walkthrough - Scottish Mammal Observations Platform (Rebuilt)

The complete, production-quality implementation of the **Scottish Mammal Observations** web application has been rebuilt and verified using the official Edinburgh Napier University coursework starter code and database schema (`database/species.sql`).

---

## 📹 Walkthrough Video & Presentation
We have recorded a full-length user journey walkthrough video showing all platform features:
*   **Gemini Artifact Video**: ![Supervisor Walkthrough Video](/supervisor_walkthrough.webm)
*   **Downloads Video**: [C:\Users\shaik\Downloads\supervisor_walkthrough.webm](file:///C:/Users/shaik/Downloads/supervisor_walkthrough.webm)
*   **Local Web Server URL**: [http://localhost:8000/videos/supervisor_walkthrough.webm](http://localhost:8000/videos/supervisor_walkthrough.webm)

---

## 🛠️ Implemented Enhancements (Excellent Tier - 70%+)

### 1. Basic Enhancements (Complete)
*   **Species Image Ingestion**: Created [database/populate_images.php](file:///e:/webdesign-part2/database/populate_images.php) to update all 34 mammal profiles with working Wikimedia Commons image URLs in the database.
*   **Professional External Stylesheet**: Created a mobile-first CSS design system [css/style.css](file:///e:/webdesign-part2/css/style.css) styled in HSL earth tones (Forest Green, Moss, Amber Gold, Warm Cream).
*   **Academic Static Page**: Added an informative [about.php](file:///e:/webdesign-part2/about.php) page describing the module, coursework details, and technical stack.
*   **Contact Form & Validation**: Added a styled contact form [contact.php](file:///e:/webdesign-part2/contact.php) with client-side JavaScript regex checking and secure server-side PHP processing.

### 2. Intermediate Enhancements (Complete)
*   **Linked Observations Sighting Log**: Modified [species.php](file:///e:/webdesign-part2/species.php) to query and render observations linked to the species' foreign key (`gbif_species_key`).
*   **Graceful NULL Value Handling**: Programmatically check for missing `locality` and `observation_date` values in observations and render appropriate placeholders ("Location not recorded" and "Date not recorded").
*   **Image Modal Zoom**: Integrated Javascript popups in [js/main.js](file:///e:/webdesign-part2/js/main.js) allowing users to click thumbnails and inspect full-size wildlife photographs.
*   **Progressive Search & Filters**: Added text query and dropdown filter controls (Diet, IUCN category, and Habitat) to the homepage [index.php](file:///e:/webdesign-part2/index.php).
*   **Column Sorting Controls**: Integrated sort query parameters in table headers to sort species by body mass, name, or habitat.

### 3. Advanced Enhancements (Complete)
*   **Interactive Leaflet.js Mapping**: Plotted occurrence coordinates as circle markers on a map in [species.php](file:///e:/webdesign-part2/species.php) with popups detailing localities, counts, and dates.
*   **Smart Observations Pagination**: Implemented paginated limits (10 items per page) for observations tables to keep detailed pages loading instantly under high volume loads (such as Red Deer's 1,174 sightings).
*   **Chart.js Statistical Visualization**: Plotted an interactive bar chart of species counts grouped by dietary habits on the homepage dashboard.
*   **Secure Session Authentication**: Built a secure moderator login portal [login.php](file:///e:/webdesign-part2/login.php) with password BCRYPT hash matching and CSRF token defenses.
*   **Sighting Log CRUD Manager**: Created an admin editor panel [admin/observations.php](file:///e:/webdesign-part2/admin/observations.php) enabling authorized users to Create, Read, Update, and Delete observation records.

---

## 🔍 QA Audit & Verification Results

### Automated Linters
We ran a full compilation syntax linter (`php -l`) on all PHP files:
- **Result**: `14/14` files verified with zero syntax errors.

### Security and Integration Checks
We executed our automated QA test suite [tests/verify.php](file:///e:/webdesign-part2/tests/verify.php) in WSL:
- **Database Connection**: Passed (connected to `scottish_mammals` via user `mammals_user`).
- **Schema Validation**: Passed (confirmed existence of `users`, `species`, and `observations` tables).
- **Data Seed Integrity**: Passed (retrieved 34 species, 3,863 observations, and 1 admin account).
- **Security Audits**: Passed (CSRF token verification, HTML escaping, and prepared statement parameter bindings).
