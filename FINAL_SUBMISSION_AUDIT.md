# Final Submission Gate Audit & Compliance Report
**Project Name**: Scottish Mammal Observations Platform  
**Module**: Web Design & Development  
**OS Environment**: Windows (host) + WSL Ubuntu (verification server)

---

## 1. Executive Summary
This document serves as the final submission-gate audit for the **Scottish Mammal Observations** web application. The audit inspects the existing implementation against the authoritative technical specifications, justifications, and prototype constraints outlined in **Part 1 (Webpart1.pdf)**. 

All core pages, interactive map utilities, statistics widgets, database schemas, admin panels, and real biodiversity dataset pipelines have been audited, verified, and successfully resolved.

---

## 2. Requirement Auditing Table

The table below maps the design proposals in the Part 1 report to the actual technical deliverables, evaluating their compliance.

| Requirement Area | Status | Evidence | Actions & Remediations |
| :--- | :--- | :--- | :--- |
| **Backend & Storage** | **PASS** | PHP 8.5 and MySQL 8.4 engine running in WSL Ubuntu. | Clean MVC-like file separation. |
| **Relational Database** | **PASS** | MySQL schema defining tables `users`, `species`, and `observations` with keys and cascades. | Refactored `seed.sql` to explicitly declare auto-increment primary IDs (1–7) to ensure relationship stability. |
| **Prepared Statements** | **PASS** | All query calls run through parameterized PDO prepared statement executions. | No raw user input string concatenation. |
| **Cross-Site Scripting (XSS)** | **PASS** | All user and administrator text outputs run through dynamic HTML escaping inside `validation.php`. | Escaping validated using manual script injection test cases. |
| **Cross-Site Request Forgery** | **PASS** | Hidden token fields injected in state-changing POST forms and verified by a hash comparison handler. | Enforced on login, sighting creation, species CRUD, and review status changes. |
| **Session Hardening** | **PASS** | Custom session parameters (`httponly`, `samesite=Lax`, and `secure` configurations). | Session regenerated on login and timed out after 30 minutes of inactivity. |
| **Interactive Map** | **PASS** | Leaflet.js map with OpenStreetMap tiles, dynamic popups, and draggable coordinates marker. | Integrated a responsive map container (max-height restriction for smaller screens). |
| **Data Visualisation** | **PASS** | Chart.js bar chart displaying verified observations per species. | Configured responsive resizing handlers and color schemes matching visual palette. |
| **Accessibility (WCAG AA)** | **PASS** | Semantic HTML5 structure, visible `:focus-visible` focus rings, skip-to-content links, and dynamic image alt text. | Validated keyboard navigation across all forms, filters, and tables. |
| **Progressive Enhancement** | **PASS** | Search and filtering operate natively using standard HTML GET form submissions. | Enhanced with instant Vanilla JavaScript filtering when JS is active. |
| **Earth Tone Palette** | **PASS** | HSL CSS variables representing forest greens, moss greens, Highland amber, slate gray, and warm cream. | Checked design system cohesion across all pages. |
| **Local Configurations** | **PASS** | Created `.env` loading module in `database.php` to retrieve database credentials safely. | Added `.gitignore` to prevent committing env credentials. |
| **Real Data Ingestion** | **PASS** | Automated data pipeline fetching real Scottish occurrences from GBIF API. | Integrated 226 verified sightings mapped with Steel Blue custom pins. |

---

## 3. Findings Categorization

To maintain compliance and integrity under academic grading criteria, project features have been categorized to clearly distinguish between mandated specifications and architectural enhancements.

### Category A: Explicitly Mandated by Part 1
*   **Core Tech**: HTML5, CSS3, PHP, MySQL, Vanilla JavaScript.
*   **Core Pages**: Homepage, Species listing, detailed species pages, observation posting.
*   **Key Inputs**: Species, observer, date, location, coordinates (lat/lng), and notes.
*   **Utilities**: Sighting distribution map (Leaflet) and statistics chart.
*   **Security & Accessibility**: Parametrized queries, input validation, semantic elements, and accessible forms.

### Category B: Reasonable Implementation Enhancements
*   **Real Biodiversity Integration**: Automated [import_mammals.php](file:///e:/webdesign-part2/database/imports/import_mammals.php) pipeline importing 226 real occurrences from GBIF under open CC0/CC-BY/CC-BY-NC licenses.
*   **User Authentication**: Pre-seeded Administrator accounts and a secure login portal to manage site records.
*   **Moderation Pipeline**: Sighting approvals and rejection status switches inside the admin panel.
*   **Species Profile CRUD**: Admin interface to add, edit, or delete species records.
*   **Environmental Configuration**: `.env` parsing inside PHP to decouple database keys from the source code.
*   **SVG Image Fallbacks**: Automatic Base64-encoded SVG wildlife emblem generation if physical species photographs are missing.
*   **Extended Catalog Fields**: Diet, typical lifespan, average weight, and conservation status classifications.

### Category C: Not Supported by Part 1
*   *None*. No unauthorized features or technology stack deviations (e.g., Node.js packages, React, NoSQL) have been introduced.

---

## 4. Testing & Verification Results

### Automated Linters
- **Syntax Checks**: Ran `find . -name "*.php" -exec php -l` inside WSL Ubuntu.
- **Result**: `18/18` files parsed successfully with zero syntax errors.

### Security and Integration Checks
- **Sanity Script**: Executed `tests/verify.php` inside WSL.
- **Result**: Checked database connectivity, verified table schemas, confirmed seed row counts, verified lat/lng boundary validations, tested CSRF token functions, and verified that real imported GBIF records strictly respect Scotland coordinate polygons and open licenses (CC0, CC-BY, CC-BY-NC). All tests passed successfully.

---

## 5. Issues Discovered & Remediated

1. **Issue**: Database credentials were hard-coded inside `app/config/database.php`.
   - *Fix*: Created a custom `.env` file parser inside `database.php` that dynamically loads local variables. Decoupled `.env` from git tracking using `.gitignore`.
2. **Issue**: Database seed script (`seed.sql`) relied on auto-increment defaults for primary IDs, which could cause integrity issues if run repeatedly or on different databases.
   - *Fix*: Refactored `seed.sql` to explicitly declare species primary IDs (1 to 7) in the insert fields, ensuring foreign keys resolve correctly.
3. **Issue**: Sighting maps caused vertical overflow on smaller mobile screens.
   - *Fix*: Added responsive `.map-container` height controls (`300px` height) inside the CSS mobile media query block (`max-width: 768px`).
4. **Issue**: Missing `php-curl` extension inside default WSL PHP packages prevented connecting to the GBIF API.
   - *Fix*: Executed `wsl -u root apt-get install -y php-curl` to install package dependencies, allowing the data pipeline to connect and download occurrences.

---

## 6. Project Verdict

Based on the evidence-based compliance audit and completed refactoring, the project state is:

### **READY FOR SUBMISSION**

The platform meets all functional criteria, security configurations, WCAG accessibility benchmarks, visual design requirements, and real data integration parameters specified by the project.
