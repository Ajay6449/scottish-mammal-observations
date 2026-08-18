const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
    console.log('Starting coursework walkthrough recording...');

    // Ensure output directories exist
    const videoDir = path.join(__dirname, '../videos');
    if (!fs.existsSync(videoDir)) {
        fs.mkdirSync(videoDir, { recursive: true });
    }

    // Launch Chromium headless
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    // Setup recording context
    const context = await browser.newContext({
        recordVideo: {
            dir: videoDir,
            size: { width: 1280, height: 720 }
        },
        viewport: { width: 1280, height: 720 }
    });

    const page = await context.newPage();

    // Register log listeners
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.error('PAGE ERROR:', err.message));

    try {
        // Step 1: Navigate to Home
        console.log('Navigating to Homepage...');
        await page.goto('http://localhost:8000/index.php');
        await page.waitForTimeout(2000);

        // Transition to Mobile Viewport to show the Hamburger Menu
        console.log('Switching to mobile viewport...');
        await page.setViewportSize({ width: 375, height: 720 });
        await page.waitForTimeout(1500);

        // Click Hamburger Toggle to Expand Menu
        console.log('Opening mobile hamburger menu...');
        await page.click('#navToggle');
        await page.waitForTimeout(2000);

        // Click Hamburger Toggle to Collapse Menu
        console.log('Closing mobile hamburger menu...');
        await page.click('#navToggle');
        await page.waitForTimeout(1500);

        // Switch back to Desktop Viewport
        console.log('Switching back to desktop viewport...');
        await page.setViewportSize({ width: 1280, height: 720 });
        await page.waitForTimeout(1000);

        // Scroll to show Stats Chart and summary
        console.log('Scrolling dashboard stats...');
        await page.evaluate(() => window.scrollTo({ top: 300, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Scroll down to filters and table
        console.log('Scrolling catalog filters...');
        await page.evaluate(() => window.scrollTo({ top: 700, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Toggle Grid View
        console.log('Toggling Grid View...');
        await page.click('#viewGridBtn');
        await page.waitForTimeout(2000);

        // Toggle Table View
        console.log('Toggling Table View...');
        await page.click('#viewTableBtn');
        await page.waitForTimeout(1500);

        // Search for Otter
        console.log('Searching for Otter...');
        await page.fill('#q', 'Otter');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2000);

        // Click View Profile on European Otter
        console.log('Viewing Otter details...');
        await page.click('text=View Profile');
        await page.waitForTimeout(2000);

        // Scroll down Otter profile details
        console.log('Scrolling Otter profile details...');
        await page.evaluate(() => window.scrollTo({ top: 400, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Scroll down to map
        console.log('Scrolling map occurrence view...');
        await page.evaluate(() => window.scrollTo({ top: 750, behavior: 'smooth' }));
        await page.waitForTimeout(3000);

        // Scroll down to observations log table
        console.log('Scrolling paginated sighting logs...');
        await page.evaluate(() => window.scrollTo({ top: 1200, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Click page 2 in observations log
        console.log('Navigating observations page 2...');
        await page.click('text=2');
        await page.waitForTimeout(2500);

        // Navigate to Admin Login
        console.log('Navigating to Admin Login...');
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        await page.waitForTimeout(500);
        await page.click('text=Admin Login');
        await page.waitForTimeout(2000);

        // Perform login
        console.log('Logging in as admin...');
        await page.fill('#username', 'admin');
        await page.fill('#password', 'Highlands2026!');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2000);

        // Inside Admin Dashboard
        console.log('Inside Admin Dashboard...');
        await page.evaluate(() => window.scrollTo({ top: 400, behavior: 'smooth' }));
        await page.waitForTimeout(2000);

        // Navigate to Manage Observations CRUD
        console.log('Navigating to observations CRUD manager...');
        await page.click('text=Manage Observations');
        await page.waitForTimeout(2000);

        // Scroll down observations list
        await page.evaluate(() => window.scrollTo({ top: 350, behavior: 'smooth' }));
        await page.waitForTimeout(2000);

        // Click record sighting to show Add Form
        console.log('Opening Add Sighting Form...');
        await page.click('text=+ Record Sighting');
        await page.waitForTimeout(2500);

        // Invalidate session
        console.log('Logging out...');
        await page.click('text=Logout');
        await page.waitForTimeout(2000);

        console.log('Walkthrough successfully recorded!');
    } catch (err) {
        console.error('Walkthrough recording error: ', err);
        try {
            await page.screenshot({ path: path.join(videoDir, 'failure_screenshot.png') });
            console.log('Saved failure screenshot to: videos/failure_screenshot.png');
        } catch (screenshotErr) {
            console.error('Failed to save failure screenshot: ', screenshotErr);
        }
    } finally {
        // Close browser to finalize video file write
        await context.close();
        await browser.close();

        // Find the generated video file and rename it to a clean name
        const files = fs.readdirSync(videoDir);
        const videoFile = files.find(f => f.endsWith('.webm'));
        if (videoFile) {
            const oldPath = path.join(videoDir, videoFile);
            const newPath = path.join(videoDir, 'supervisor_walkthrough.webm');
            if (fs.existsSync(newPath)) {
                fs.unlinkSync(newPath); // Delete old walkthrough
            }
            fs.renameSync(oldPath, newPath);
            console.log(`Video saved and renamed to: ${newPath}`);
        } else {
            console.log('Warning: No video file was found in output directory.');
        }
    }
})();
