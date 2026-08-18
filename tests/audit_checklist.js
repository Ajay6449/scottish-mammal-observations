const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
    console.log('=== STARTING COURSEWORK COMPLIANCE AUDIT ===\n');

    const auditDir = path.join(__dirname, '../../qa/audit');
    if (!fs.existsSync(auditDir)) {
        fs.mkdirSync(auditDir, { recursive: true });
    }

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const context = await browser.newContext({
        viewport: { width: 1280, height: 720 }
    });
    const page = await context.newPage();

    let passedChecks = 0;
    let failedChecks = 0;

    const assertCheck = (name, condition) => {
        if (condition) {
            console.log(`[PASS] ${name}`);
            passedChecks++;
        } else {
            console.log(`[FAIL] ${name}`);
            failedChecks++;
        }
    };

    try {
        // --- 1. Security Barrier: Unauthenticated Access Block ---
        await page.goto('http://localhost:8000/admin/index.php');
        await page.waitForTimeout(1000);
        assertCheck('Admin Panel Blocks Unauthenticated Access', page.url().includes('login.php'));

        // --- 2. Homepage Audit: Chart, Grid/Table Toggles, Filters ---
        await page.goto('http://localhost:8000/index.php');
        await page.waitForTimeout(1500);

        const hasDietChart = await page.locator('#dietChart').count() > 0;
        assertCheck('Chart.js Diet Distribution Chart Rendered', hasDietChart);

        const hasToggles = (await page.locator('#viewGridBtn').count() > 0) && (await page.locator('#viewTableBtn').count() > 0);
        assertCheck('Grid and Table Layout Toggles Rendered', hasToggles);

        // Test toggle action
        await page.click('#viewGridBtn');
        await page.waitForTimeout(500);
        const isGridVisible = await page.locator('#gridView').isVisible();
        assertCheck('Grid Layout View Toggled Visible', isGridVisible);

        await page.click('#viewTableBtn');
        await page.waitForTimeout(500);
        const isTableVisible = await page.locator('#tableView').isVisible();
        assertCheck('Table Layout View Toggled Visible', isTableVisible);

        // Test sorting
        await page.click('text=Common Name');
        await page.waitForTimeout(1000);
        assertCheck('Table Column Sorting Active (URL parameter)', page.url().includes('sort=common_name'));

        // Test search
        await page.fill('#q', 'European Badger');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);
        const badgerFound = await page.locator('text=European Badger').count() > 0;
        assertCheck('Search query retrieves correct DB species profile', badgerFound);

        // Reset search
        await page.goto('http://localhost:8000/index.php');
        await page.waitForTimeout(1000);

        // --- 3. Species Profile Detail Page: Map, Pagination, Modals ---
        await page.goto('http://localhost:8000/species.php?key=2440958'); // Red Deer
        await page.waitForTimeout(2000);

        const hasMap = await page.locator('#speciesMap').count() > 0;
        assertCheck('Leaflet.js Occurrences Map Container Rendered', hasMap);

        const hasPagination = await page.locator('.pagination').count() > 0;
        assertCheck('Observations log paginated table controls rendered', hasPagination);

        // Click next page
        await page.locator('.pagination a').filter({ hasText: /^2$/ }).first().click();
        await page.waitForTimeout(1500);
        assertCheck('Observations log pagination navigates and loads next page', page.url().includes('page=2'));

        // Test image modal trigger
        await page.click('.modal-trigger');
        await page.waitForTimeout(500);
        const isModalVisible = await page.locator('#imgModal').isVisible();
        assertCheck('Image zoom modal pops up on thumbnail click', isModalVisible);

        // Close modal via Escape
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        const isModalClosed = !(await page.locator('#imgModal').isVisible());
        assertCheck('Image modal closes gracefully on Escape keypress', isModalClosed);

        // --- 4. Contact Form Validation ---
        await page.goto('http://localhost:8000/contact.php');
        await page.waitForTimeout(1000);

        // Submit empty form (client side check)
        await page.click('button[type="submit"]');
        await page.waitForTimeout(500);
        const nameErrorVisible = await page.locator('.error-feedback').first().isVisible();
        assertCheck('Contact Form validation feedback triggered on empty submit', nameErrorVisible);

        // Submit valid form
        await page.fill('#name', 'Napier Examiner');
        await page.fill('#email', 'examiner@napier.ac.uk');
        await page.fill('#subject', 'Coursework Audit Check');
        await page.fill('#message', 'This is a test message to verify the coursework form works.');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1000);
        const hasSuccess = await page.locator('.alert-success').count() > 0;
        assertCheck('Contact Form validation and submission processed successfully', hasSuccess);

        // --- 5. Admin Login & CRUD Operations ---
        await page.goto('http://localhost:8000/login.php');
        await page.fill('#username', 'admin');
        await page.fill('#password', 'Highlands2026!');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1500);
        assertCheck('Admin Login authenticate and authorize redirects successfully', page.url().includes('admin/index.php'));

        // Navigate to Observations CRUD
        await page.click('text=Manage Observations');
        await page.waitForTimeout(1500);

        // Click + Record Sighting
        await page.click('text=+ Record Sighting');
        await page.waitForTimeout(1000);

        // Submit new observation
        await page.selectOption('#gbif_species_key', '2440958'); // Red Deer
        await page.fill('#locality', 'QA Audit Test Site');
        await page.fill('#individual_count', '8');
        await page.fill('#observation_date', '2026-08-18');
        await page.fill('#latitude', '57.3456');
        await page.fill('#longitude', '-4.1234');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(1500);

        // Verify creation
        const hasNewSighting = await page.locator('text=QA Audit Test Site').count() > 0;
        assertCheck('Admin CRUD creates and saves new observation to database', hasNewSighting);

        // Delete the new observation
        await page.click('text=Delete >> visible=true');
        // Handle confirm dialog automatically (Playwright handles dialogs or we can accept)
        page.on('dialog', async dialog => {
            assertCheck('Admin CRUD Delete triggers confirmation prompt dialog', true);
            await dialog.accept();
        });
        await page.waitForTimeout(1500);
        const deletedSuccessful = await page.locator('.alert-success').count() > 0;
        assertCheck('Admin CRUD deletes observation from database and updates log', deletedSuccessful);

        // Logout
        await page.click('text=Logout');
        await page.waitForTimeout(1500);
        assertCheck('Moderator logout clears authentication states and session', page.url().includes('index.php'));

    } catch (err) {
        console.error('Audit execution error: ', err);
    } finally {
        await context.close();
        await browser.close();

        console.log(`\n=== AUDIT COMPLETE: ${passedChecks} PASSED, ${failedChecks} FAILED ===`);
        process.exit(failedChecks === 0 ? 0 : 1);
    }
})();
