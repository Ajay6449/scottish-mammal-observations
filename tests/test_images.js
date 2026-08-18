const { chromium } = require('playwright');

(async () => {
    console.log('=== STARTING SPECIES IMAGE AUDIT THROUGH BROWSER ===\n');

    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();

    // Listen to network responses to capture status codes
    const responseStatuses = new Map();
    page.on('response', response => {
        const url = response.url();
        const status = response.status();
        responseStatuses.set(url, status);
    });

    try {
        console.log('Navigating to Homepage...');
        await page.goto('http://localhost:8000/index.php');
        await page.waitForTimeout(3000); // Give Varnish/network time to load

        // Query all images in the table
        const images = await page.evaluate(() => {
            const imgEls = Array.from(document.querySelectorAll('table tbody tr img'));
            return imgEls.map(img => {
                const tr = img.closest('tr');
                const commonName = tr ? tr.querySelector('td:nth-child(2)').innerText : 'Unknown';
                return {
                    commonName,
                    src: img.src,
                    complete: img.complete,
                    naturalWidth: img.naturalWidth,
                    naturalHeight: img.naturalHeight
                };
            });
        });

        console.log(`Found ${images.length} species images on homepage.\n`);
        console.log('| Species | Image URL | HTTP Status | Browser Loaded | naturalWidth | naturalHeight | Result |');
        console.log('| ------- | --------- | -----------: | -------------- | -----------: | ------------: | ------ |');

        let passed = 0;
        let failed = 0;

        for (const img of images) {
            const status = responseStatuses.get(img.src) || 'N/A';
            const loaded = img.complete && img.naturalWidth > 0 && img.naturalHeight > 0;
            const result = loaded ? 'PASS' : 'FAIL';
            
            if (loaded) passed++;
            else failed++;

            console.log(`| ${img.commonName} | ${img.src} | ${status} | ${img.complete ? 'Yes' : 'No'} | ${img.naturalWidth} | ${img.naturalHeight} | ${result} |`);
        }

        console.log(`\n=== IMAGE AUDIT COMPLETE: ${passed} PASSED, ${failed} FAILED ===`);
    } catch (err) {
        console.error('Error during image test: ', err);
    } finally {
        await browser.close();
    }
})();
