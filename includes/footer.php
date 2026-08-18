    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>Scottish Wildlife Platform</h4>
                    <p>A data-driven platform dedicated to monitoring and visualizing the distribution of terrestrial mammals across Scotland. Promoting education, wildlife protection, and citizen science.</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <nav>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--spacing-xs);">
                            <li><a href="<?php echo $pathPrefix; ?>index.php">Species Catalog</a></li>
                            <li><a href="<?php echo $pathPrefix; ?>about.php">About Project</a></li>
                            <li><a href="<?php echo $pathPrefix; ?>contact.php">Contact Us</a></li>
                            <li><a href="<?php echo $pathPrefix; ?>login.php">Admin Login</a></li>
                        </ul>
                    </nav>
                </div>
                <div>
                    <h4>Data Ingestion & Provenance</h4>
                    <p>Species records and observations are sourced from the Global Biodiversity Information Facility (GBIF) under Creative Commons licenses (CC0, CC-BY, CC-BY-NC).</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Scottish Wildlife. Built for SET08101 Web Technologies Coursework.</p>
                <p>Licensed under Open Government Licence (OGL) & CC-BY 4.0.</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo $pathPrefix; ?>js/main.js"></script>
</body>
</html>
