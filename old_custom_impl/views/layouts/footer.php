<?php
/**
 * Footer Layout Template
 * Provides structured closing content, licensing, links, and scripting loads.
 */
?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>Scottish Mammal Observations</h4>
                    <p style="max-width: 400px; color: #a8a39d; line-height: 1.6;">
                        A dedicated platform for cataloging, mapping, and monitoring the mammal species of Scotland. Empowering field researchers, citizens, and conservationists with reliable, real-time biodiversity insights.
                    </p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; line-height: 2;">
                        <li><a href="/index.php">Home</a></li>
                        <li><a href="/species.php">Species Directory</a></li>
                        <li><a href="/observations.php">Recent Sightings</a></li>
                        <li><a href="/observation-create.php">Submit Sighting</a></li>
                        <li><a href="/credits.php" style="font-weight: 600; color: var(--color-accent);">Data Sources & Credits</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Project Credits & Tech Stack</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; line-height: 2; color: #a8a39d;">
                        <li>Backend: PHP 8.5 & MySQL 8.4</li>
                        <li>Mapping: <a href="https://leafletjs.com/" target="_blank" rel="noopener">Leaflet.js</a></li>
                        <li>Charts: <a href="https://www.chartjs.org/" target="_blank" rel="noopener">Chart.js</a></li>
                        <li>Fonts: Google Fonts Outfit & Playfair</li>
                        <li>Standard: HTML5 & WCAG AA Accessible</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Scottish Mammal Observations. Built under open academic licensing terms.</p>
                <p style="color: #6b665f;">Designed for Scotland's wildlife protection and habitat research.</p>
            </div>
        </div>
    </footer>

    <!-- Leaflet JS Library (Only if needed) -->
    <?php if (isset($loadMap) && $loadMap === true): ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <?php endif; ?>

    <!-- Chart.js Library (Only if needed) -->
    <?php if (isset($loadStats) && $loadStats === true): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <!-- Page Specific Script Loading -->
    <?php if (isset($scriptFile) && !empty($scriptFile)): ?>
        <script src="<?php echo htmlspecialchars($scriptFile, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endif; ?>
</body>
</html>
