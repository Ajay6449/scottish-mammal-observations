# Coursework Submission Package Setup & Run Instructions
**Project**: Scottish Mammal Observations Platform (Rebuilt)  
**Purpose**: Guidance for supervisor/examiner to deploy, run, and verify the official coursework ZIP package.

---

## 📋 System Prerequisites
Before running, ensure the target system has the following installed:
1.  **PHP 8.0+** (with `pdo_mysql` extension enabled).
2.  **MySQL 8.0+** (or MariaDB equivalent running locally).
3.  **Modern Web Browser** (Chrome, Firefox, Safari, or Edge).

---

## 🛠️ Step-by-Step Deployment Guide

### Step 1: Extract the Package
1. Copy the `scottish_mammal_observations_submission.zip` file to a working directory.
2. Extract/unzip the file using your native OS tools (e.g. Right-click -> *Extract All* on Windows, or `unzip` in Linux terminal).

### Step 2: Initialize the MySQL Database
Create a fresh database and seed the schema, mammal profiles, and occurrence sightings:
1. Open your MySQL terminal/client (e.g. MySQL Workbench, phpMyAdmin, or CLI) as root.
2. Execute the following SQL commands:
   ```sql
   -- Create a fresh database
   CREATE DATABASE scottish_mammals CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

   -- Create a dedicated database user (as configured in .env.example)
   CREATE USER 'mammals_user'@'localhost' IDENTIFIED BY 'ScotWild2026!';
   GRANT ALL PRIVILEGES ON scottish_mammals.* TO 'mammals_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Import the unified database backup file containing all 3 tables (`species` with image URLs, `observations` with auto-increment, and `users` admin credentials):
   *   **Import Command**: `mysql -u mammals_user -pScotWild2026! scottish_mammals < database/species.sql`

### Step 3: Configure Environment Variables
1. Locate the `.env.example` file in the root directory.
2. Duplicate it and rename the copy to `.env`.
3. Open `.env` and verify/adjust the database configuration. The default credentials match the user created above:
   ```env
   DB_HOST=localhost
   DB_NAME=scottish_mammals
   DB_USER=mammals_user
   DB_PASS=ScotWild2026!
   DB_CHARSET=utf8mb4
   ```

### Step 4: Start the Local Development Server
Launch the PHP built-in web server pointing to the extracted root directory:
1. Open a command line interface (CLI) in the extracted root directory.
2. Run the server command:
   ```bash
   php -S localhost:8000
   ```
3. The application is now serving at **[http://localhost:8000](http://localhost:8000)**.

---

## 🔍 Step 5: Verification & Testing

### 1. Run the Verification Script
To guarantee that the environment, database connection, tables (species, observations, users), and record counts (34 species, 3,863 observations) are fully functional:
```bash
php tests/verify.php
```
*Expected Output*: You should see `=== ALL VERIFICATIONS PASSED SUCCESSFULLY ===` printed on the terminal.

### 2. Administrator Login Credentials
To access the management dashboard and perform CRUD operations:
*   **Navigation Link**: Click "Admin Login" in the header menu (or go to `http://localhost:8000/login.php`).
*   **Username**: `admin`
*   **Password**: `Highlands2026!`

### 3. Exploring the Key Features
*   **Interactive Sighting Map**: Open a species detailed profile (e.g., European Otter or Red Deer) to view its distribution map using Leaflet.js. small styled circle markers represent density. Click them to see locality popups.
*   **Observations Pagination**: Scroll down a species page (like Red Deer) to see the paginated sighting logs (10 items per page), which prevents layout lag on high record volumes.
*   **Visualisations**: Review the Chart.js species diet distribution bar chart on the homepage.
*   **Grid/Table View Toggle**: Switch between Grid View (cards layout) and Table View (sortable logs layout) using the toggles above the species catalog.
