const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
    console.log('Starting supervisor walkthrough recording...');

    // Ensure output directories exist
    const videoDir = path.join(__dirname, '../public/assets/videos');
    if (!fs.existsSync(videoDir)) {
        fs.mkdirSync(videoDir, { recursive: true });
    }

    // Launch Chromium headless in WSL
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

    // Register error and log listeners
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.error('PAGE ERROR:', err.message));

    try {
        // Step 1: Navigate to Home
        console.log('Navigating to Homepage...');
        await page.goto('http://localhost:8000/index.php');
        await page.waitForTimeout(2000);

        // Scroll down home page to show Stats and Map
        console.log('Scrolling homepage...');
        await page.evaluate(() => window.scrollTo({ top: 350, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        await page.evaluate(() => window.scrollTo({ top: 850, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        await page.evaluate(() => window.scrollTo({ top: 1400, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Step 2: Navigate to Species Directory
        console.log('Navigating to Species Directory...');
        await page.click('a[href="/species.php"]');
        await page.waitForTimeout(2000);

        // Search for Otter
        console.log('Searching for Otter...');
        await page.fill('#q', 'Otter');
        await page.waitForTimeout(2000);

        // Click View Full Profile on Eurasian Otter card (ensuring we target only the visible card)
        console.log('Viewing Otter details...');
        await page.locator('text=View Full Profile >> visible=true').first().click();
        await page.waitForTimeout(2000);

        // Scroll down Otter profile
        await page.evaluate(() => window.scrollTo({ top: 400, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        await page.evaluate(() => window.scrollTo({ top: 1000, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Step 3: Navigate to Recent Sightings
        console.log('Navigating to Recent Sightings...');
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        await page.waitForTimeout(500);
        await page.click('a[href="/observations.php"]');
        await page.waitForTimeout(2000);

        // Filter by Scientific Records
        console.log('Filtering by Scientific Records...');
        await page.selectOption('#type', 'imported');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2500);

        // Scroll sightings map and table
        await page.evaluate(() => window.scrollTo({ top: 300, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        await page.evaluate(() => window.scrollTo({ top: 800, behavior: 'smooth' }));
        await page.waitForTimeout(2500);

        // Step 4: Admin Login
        console.log('Navigating to Admin Login...');
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
        await page.waitForTimeout(500);
        await page.click('a[href="/login.php"]');
        await page.waitForTimeout(2000);

        console.log('Logging in as Admin...');
        await page.waitForSelector('#username_or_email');
        await page.evaluate(() => {
            document.getElementById('username_or_email').value = 'admin';
            document.getElementById('password').value = 'Highlands2026!';
            document.querySelector('form').submit();
        });
        await page.waitForTimeout(3000);

        // Admin Dashboard
        console.log('Inside Admin Dashboard...');
        await page.evaluate(() => window.scrollTo({ top: 400, behavior: 'smooth' }));
        await page.waitForTimeout(2000);

        // Moderate Sightings
        console.log('Navigating to Sighting Moderation...');
        await page.click('a[href="/admin/observations-manage.php"]');
        await page.waitForTimeout(2000);

        await page.evaluate(() => window.scrollTo({ top: 300, behavior: 'smooth' }));
        await page.waitForTimeout(2000);

        // Log out
        console.log('Logging out...');
        await page.click('text=Log Out');
        await page.waitForTimeout(2000);

        console.log('Walkthrough successfully recorded!');
    } catch (err) {
        console.error('Walkthrough recording error: ', err);
        try {
            await page.screenshot({ path: path.join(videoDir, 'failure_screenshot.png') });
            console.log('Saved failure screenshot to: public/assets/videos/failure_screenshot.png');
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
            fs.renameSync(oldPath, newPath);
            console.log(`Video saved and renamed to: ${newPath}`);
        } else {
            console.log('Warning: No video file was found in output directory.');
        }
    }
})();
