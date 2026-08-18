# Supervisor Presentation Walkthrough & Script
**Project**: Scottish Mammal Observations Platform  
**Purpose**: Walkthrough script to guide the user in recording a professional demo video for their supervisor.

---

## 🚀 Recording Setup Tips
1.  **Browser**: Open Google Chrome or Microsoft Edge on your host machine.
2.  **Server**: Ensure the server is running (visit `http://localhost:8000`).
3.  **Recording Tool**: Press `Win + G` (Windows Game Bar) and click the **Record** button, or use OBS / Loom.
4.  **Audio**: Speak clearly at a moderate pace.

---

## 🎙️ Step-by-Step Script & Walkthrough

### Part 1: Homepage & Visual Design System
*   **What to do on screen**: 
    1. Start at the top of the Home page (`http://localhost:8000/index.php`).
    2. Hover over the navigation links (Home, Species Directory, Sightings, Submit Sighting, Admin Login).
    3. Slowly scroll down to show the **Observations by Species** bar chart and the **Platform Summary** card.
*   **What to say**:
    > "Hello. Today I am presenting the completed Scottish Mammal Observations web application. The platform is built using a clean PHP backend and MySQL database, following the Part 1 design justifications.
    > 
    > As you can see on the homepage, we have implemented a responsive Highland Earth Tone visual system using custom CSS properties for forest greens, moss, and warm cream. 
    > 
    > In the statistics dashboard, we render a responsive Chart.js bar chart of observations per species, alongside a Platform Summary. To elevate the platform's academic value, we integrated real scientific sightings from the GBIF network. Out of 243 total sightings, 226 are verified scientific occurrences, and 17 are community submissions."

---

### Part 2: Interactive Mapping & Provenance Indicators
*   **What to do on screen**:
    1. Scroll down to the **Interactive Sighting Map** on the homepage.
    2. Zoom in/out of the map to show coordinates clustering.
    3. Click on a **Steel Blue pin** (Scientific Record) and show the popup.
    4. Click on a **Moss Green pin** (Community Sighting) and show the popup.
*   **What to say**:
    > "Our interactive distribution map leverages Leaflet.js rendering OpenStreetMap tiles. We have established clear visual distinction between data sources:
    > 
    > Steel Blue pins represent verified scientific records imported from GBIF, showing the data provider, creative commons license, and a direct link to the original GBIF database occurrence. 
    > 
    > Moss Green pins represent community sightings submitted directly by users of our platform. All coordinates are handled safely; we exclude generalized records with uncertainty bounds exceeding 10 kilometers to protect spatial precision."

---

### Part 3: Species Catalog & Details
*   **What to do on screen**:
    1. Click the **Species Directory** navigation link.
    2. Search for 'Otter' in the search box, and click **Apply Filters** (demonstrating progressive search).
    3. Click **View Full Profile** on the Eurasian Otter card.
    4. Scroll down the Otter profile page showing stats, the distribution map, and the detailed sightings log table.
*   **What to say**:
    > "In the Species Directory, users can browse mammal profiles. We implement progressive search and filters. Clicking on the Eurasian Otter profile opens its detailed view, showing its scientific name, typical lifespan, diet, and conservation status. 
    > 
    > For missing photos, we programmatically generate base64-encoded SVG wildlife emblems matching our Highland earth-tone palette. Below the profile, the map plots all sightings specific to this species, followed by a paginated logs table citing dates, coordinates, and attributions."

---

### Part 4: Sighting Directories & User Submissions
*   **What to do on screen**:
    1. Click the **Sightings** navigation link.
    2. Filter the Sighting Type dropdown by **Scientific Records (GBIF)** and click **Apply**.
    3. Click the **Submit Sighting** navigation link.
    4. Click on the map in the submission form to demonstrate that coordinate inputs (Lat, Lng) populate automatically.
*   **What to say**:
    > "The main Sightings Log lets users search notes and filter records by sighting type. We can view scientific records and community reports separately. 
    > 
    > Our Submit Sighting form makes coordinate selection simple. Clicking on the map pins a marker and automatically populates the latitude and longitude inputs, which are protected by server-side boundary checks."

---

### Part 5: Admin Panel & Data Sources Credits
*   **What to do on screen**:
    1. Click **Admin Login** in the header.
    2. Log in using `admin` / `Highlands2026!`.
    3. Scroll the Admin Dashboard, click **Moderate Sightings** in the sidebar, and hover over the Action buttons.
    4. Log out, scroll to the footer, and click the **Data Sources & Credits** link.
*   **What to say**:
    > "Authorized moderators can access the admin panel. Here, they can approve or reject community sightings, manage species profiles, and audit imported records. 
    > 
    > Finally, we respect data ownership. The Data Sources and Credits page cites all public dataset references, download dates, licensing constraints, and data-cleaning guidelines. 
    > 
    > The application is fully audited, secure against SQL Injection and XSS, and runs on parameterized PHP queries. Thank you."
