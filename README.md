# Scottish Mammal Observations

An interactive, secure, and production-quality database-driven web application to catalog, map, and monitor mammal species populations across Scotland. Designed under strict WCAG AA accessibility principles and optimized for biodiversity research and community engagement.

---

## 1. Technology Stack
The platform is built on standard, justified core web technologies:
*   **Frontend**: Semantic HTML5, CSS3 Custom Properties (Vanilla CSS), Vanilla JavaScript (no framework dependencies)
*   **Interactive Maps**: [Leaflet.js](https://leafletjs.com/) (rendered via OpenStreetMap tiles)
*   **Data Visualisation**: [Chart.js](https://www.chartjs.org/) (for responsive statistics)
*   **Backend**: PHP 8+ (object-oriented, PDO prepared statements, modular design)
*   **Database**: MySQL 8+ / MariaDB (relational design, constraints, and optimized index mappings)

---

## 2. Architecture & File Structure

The project follows a clean separation of concerns, separating views (layouts and templates) from app business logic (database config, helpers, and verification):

```
/
├── public/                     # Public web-accessible directory
│   ├── index.php               # Home Page & dashboard summary
│   ├── species.php             # Species Directory listing with search/filter
│   ├── species-detail.php      # Species Profile details & distribution mapping
│   ├── observations.php        # Paginated sighting browser with filters
│   ├── observation-create.php  # Sighting submission form with Leaflet location pin
│   ├── login.php               # Secure Administrator login portal
│   ├── logout.php              # Session destruction controller
│   └── admin/                  # Protected Administration Area
│       ├── index.php           # Admin panel overview & statistics
│       ├── species-manage.php  # CRUD manager for species profiles
│       └── observations-manage.php # Moderation interface for user sightings
│
├── app/                        # Core backend application logic
│   ├── config/
│   │   └── database.php        # PDO database connection handler
│   └── helpers/
│       ├── auth.php            # Secure session management, timeout & roles
│       ├── csrf.php            # CSRF token generator and validator
│       ├── media.php           # Dynamic high-quality SVG image fallbacks
│       └── validation.php      # Coordinate, date, email verification & XSS escaping
│
├── views/                      # Reusable visual layouts
│   └── layouts/
│       ├── header.php          # Global header navigation & meta metadata
│       └── footer.php          # Global footer, credits, and script injections
│
├── assets/                     # Frontend static assets
│   ├── css/
│   │   └── style.css           # Global CSS variables, grids, and design tokens
│   └── js/
│       ├── map-picker.js       # Form coordinates map pin handler
│       ├── map-view.js         # Leaflet sighting map visualizer
│       └── stats.js            # Chart.js statistics bar renderer
│
├── database/
│   ├── schema.sql              # Relational SQL schema definitions
│   └── seed.sql                # Pre-populated realistic Scottish mammal sightings
│
└── tests/
    └── verify.php              # Automated QA sanity checks
```

---

## 3. Installation & Local Setup

### Requirements
*   PHP 8.0 or higher (with `pdo_mysql` and `mbstring` extensions enabled)
*   MySQL 8.0 or higher / MariaDB
*   Modern web browser supporting ES6 JavaScript

### Database Import Setup
1. Open your MySQL client interface (e.g., terminal or phpMyAdmin) and create the database:
   ```sql
   CREATE DATABASE scottish_mammals DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Create a dedicated database user with secure permissions:
   ```sql
   CREATE USER 'mammals_user'@'localhost' IDENTIFIED BY 'ScotWild2026!';
   GRANT ALL PRIVILEGES ON scottish_mammals.* TO 'mammals_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Import the schema definitions to establish database tables:
   ```bash
   mysql -u mammals_user -p scottish_mammals < database/schema.sql
   ```
4. Seed the database with realistic species profiles and historical sightings:
   ```bash
   mysql -u mammals_user -p scottish_mammals < database/seed.sql
   ```

### Running Locally
To launch the development server, run the built-in PHP server pointing to the `public/` directory:
```bash
php -S localhost:8000 -t public
```
Now, open your web browser and navigate to `http://localhost:8000` to interact with the platform.

### Admin Account Credentials
To access the secure administrator portal (`/admin/index.php`), use the pre-seeded admin user details:
*   **Username**: `admin`
*   **Password**: `Highlands2026!`

---

## 4. Part 1 &rarr; Part 2 Implementation Mapping

This implementation corresponds directly to the design justifications and prototype specifications outlined in the Part 1 report:

### Direct Decisional Mapping
*   **Part 1: HTML5/CSS3** &rarr; semantic responsive frontend ([style.css](file:///e:/webdesign-part2/public/assets/css/style.css))
*   **Part 1: PHP/MySQL** &rarr; backend/database implementation ([database.php](file:///e:/webdesign-part2/app/config/database.php) & [schema.sql](file:///e:/webdesign-part2/database/schema.sql))
*   **Part 1: Species listing** &rarr; species directory ([species.php](file:///e:/webdesign-part2/public/species.php))
*   **Part 1: Detailed species pages** &rarr; species detail routes ([species-detail.php](file:///e:/webdesign-part2/public/species-detail.php))
*   **Part 1: Observation posting** &rarr; observation submission form ([observation-create.php](file:///e:/webdesign-part2/public/observation-create.php))
*   **Part 1: Search/filtering** &rarr; species and observation filtering ([species.php](file:///e:/webdesign-part2/public/species.php) & [observations.php](file:///e:/webdesign-part2/public/observations.php))
*   **Part 1: Mapping** &rarr; Leaflet observation map ([map-view.js](file:///e:/webdesign-part2/public/assets/js/map-view.js))
*   **Part 1: Visualisation** &rarr; Chart.js statistics ([stats.js](file:///e:/webdesign-part2/public/assets/js/stats.js))
*   **Part 1: Accessibility** &rarr; semantic HTML, labels, keyboard support, contrast ([header.php](file:///e:/webdesign-part2/views/layouts/header.php) & [footer.php](file:///e:/webdesign-part2/views/layouts/footer.php))
*   **Part 1: Progressive enhancement** &rarr; server-rendered functionality + JavaScript enhancement ([species.php](file:///e:/webdesign-part2/public/species.php))

### Implementation Consistency and Traceability
The integration of real public biodiversity occurrences from GBIF strengthens the delivery of the Part 1 design guidelines by replacing fabricated data with scientifically verified reports. This enhancement directly enriches:
*   **Observation Records**: Generates authentic historical database tables mapping directly to Part 1 schemas.
*   **Search & Filtering**: Extends observations with a Sighting Type filter, verifying query bounds.
*   **Interactive Maps**: populates Leaflet charts with real coordinate points covering the Cairngorms, Loch Lomond, and the Hebrides.
*   **Data Visualisation**: Drives Chart.js distributions with real occurrence numbers across species.
*   **Scottish Context**: Situates the platform in a real-world Scottish biodiversity monitoring context.

### Detailed Implementation Details
| Part 1 Design Decision | Part 2 Actual Technical Implementation |
| :--- | :--- |
| **Server-Side Core Stack (PHP/MySQL)** | Developed modular PHP controllers and PDO-based MySQL storage. Free from complex frontend builders or Node.js runtime bloat. |
| **Relational Database Design** | Formulated a normalized relational model (Users, Species, Observations) with strict foreign key constraints and indexed lookup queries. |
| **Leaflet Sighting Maps** | Integrated dynamic Leaflet.js rendering OpenStreetMap tiles. Markers are loaded directly from the database and feature custom accessible information popups. |
| **Chart.js Statistics** | Built a responsive species sighting bar chart reflecting active database observation metrics via Canvas. |
| **Highland Earth Tone Visuals** | Created a bespoke CSS design system utilizing variables for Forest Green, Olive Moss, Highland Amber Gold, and warm cream. |
| **Progressive Enhancement** | Core search and filtering work natively via standard HTML GET forms. JavaScript hooks enrich this with instant, responsive DOM filtering. |
| **Accessibility (WCAG AA)** | Implemented semantic section tags, keyboard skip links, clear focus indicators (`:focus-visible`), alt attributes, and high text-contrast profiles. |
| **Prepared Statements Security** | Leveraged parameterized PDO statements across all database queries to prevent SQL injections. |
| **CSRF & Session Protection** | Guarded state-changing forms (observation creation and admin panels) with cryptographically secure CSRF tokens and secure session parameters. |

---

## 5. External Libraries, Credits, and Attributions

*   **Leaflet.js**: Used under BSD-2-Clause license for interactive map layers. [leafletjs.com](https://leafletjs.com/)
*   **Chart.js**: Used under MIT license for statistics visualization. [chartjs.org](https://www.chartjs.org/)
*   **OpenStreetMap**: Mapping tiles provider under Open Database License (ODbL). [openstreetmap.org](https://www.openstreetmap.org/)
*   **Google Fonts**: Outfit (body font) and Playfair Display (heading font) under Open Font License (OFL). [fonts.google.com](https://fonts.google.com/)
*   **Mammal Factual Data**: Extracted from public conservation directories, matching native UK biodiversity classifications.
*   **Images / Icons Fallbacks**: Programmatically generated inside `media.php` as base64 HSL SVG emblems, representing forest wildlife to maintain licensing integrity and fast load times.

## 6. Data Sources & Credits

The Scottish Mammal Observations platform uses biodiversity occurrence data from external public sources. Imported records remain subject to their original licences and attribution requirements. We do not claim ownership of external data.

*   **Dataset Name**: GBIF Terrestrial Occurrence Records (Filtered to Scotland)
*   **Data Provider**: GBIF Network publishers, including NatureScot and local biological recording groups.
*   **Source Platform**: Global Biodiversity Information Facility (GBIF)
*   **Licenses**: Creative Commons CC0, CC-BY 4.0, and CC-BY-NC 4.0.
*   **Access/Download Date**: 17 August 2026
*   **Attribution Statement**: *"GBIF Occurrence Download (17 August 2026). Occurrences filtered by country=GB, geometry polygon covering Scotland, and species Sciurus vulgaris, Martes martes, Felis silvestris, Cervus elaphus, Castor fiber, Lutra lutra, Phoca vitulina."*

### Ingestion Data-Cleaning & Coordinate Rules
1.  **Geographic Constraints**: Occurrences are filtered using a WKT polygon matching Scotland's spatial coverage: `POLYGON((-8.6 54.6, -0.7 54.6, -0.7 60.9, -8.6 60.9, -8.6 54.6))`.
2.  **Date Normalization**: Raw calendar metrics are parsed and normalized into standardized database `YYYY-MM-DD` strings.
3.  **Deduplication**: Enforced via a unique key constraint on `source_record_id` (GBIF key), preventing double insertion.
4.  **Coordinate-Uncertainty Rule**: Any record carrying a spatial mapping uncertainty exceeding 10km (`coordinateUncertaintyInMeters > 10000`) is automatically skipped to prevent general/obscured coordinate locations from polluting our local dataset. We respect coordinate generalization provided by the source and do not attempt to recover hidden coordinates.

---

## 7. Known Limitations
1. **Local Image Assets**: Species photos utilize programmatically-generated inline SVG graphics representing clean wildlife emblems. This ensures the app loads immediately and respects asset copyright terms. Users can drop real image assets directly into the `/assets/images/` folder matching the database filenames (e.g. `red_squirrel.jpg`).
2. **Mail Servers**: No verification mail server is set up. Sighting logs display immediately, and admins are pre-seeded in the database schema.
