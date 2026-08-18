<?php
/**
 * Scottish Mammal Observations - Data Sources & Credits
 * Displays external biodiversity dataset citations, licenses, and attributions.
 */

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/validation.php';

$pageTitle = "Data Sources & Credits | Scottish Mammal Observations";
$pageDescription = "Attribution statements and license information for the public biodiversity datasets used on the Scottish Mammal Observations platform.";
require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="container" style="max-width: 900px; margin-bottom: var(--spacing-xxl);">
    <div style="margin-bottom: var(--spacing-xl);">
        <h1>Data Sources & Credits</h1>
        <p style="color: var(--color-text-muted); font-size: 1.1rem; line-height: 1.6;">
            The Scottish Mammal Observations platform uses biodiversity occurrence data from external public sources. 
            Imported records remain subject to their original licences and attribution requirements. We do not claim ownership of external data.
        </p>
    </div>

    <!-- Alert Statement -->
    <div class="alert alert-info" style="margin-bottom: var(--spacing-xl);" role="note">
        <p style="margin: 0; font-weight: 500; line-height: 1.6;">
            <strong>Attribution Notice:</strong> All scientific record occurrences on our interactive maps and sighting logs are dynamically synchronized from the <strong>Global Biodiversity Information Facility (GBIF)</strong> API. This platform supports the open access sharing of environmental metrics for educational and conservation research purposes.
        </p>
    </div>

    <!-- Datasets Attribution Table -->
    <section style="margin-top: var(--spacing-xxl);">
        <h2>Imported Datasets & Licenses</h2>
        <div class="table-responsive" style="margin-top: var(--spacing-lg);">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Dataset Name</th>
                        <th scope="col">Data Provider</th>
                        <th scope="col">License</th>
                        <th scope="col">Source URL</th>
                        <th scope="col">Attribution Statement</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 600;">GBIF Occurrence Dataset (Scottish Terrestrial Mammals)</td>
                        <td>GBIF Network publishers, including NatureScot datasets and local ecological record groups.</td>
                        <td>
                            <span class="badge badge-imported" style="display:block; margin-bottom:4px; text-align:center;">CC0 1.0</span>
                            <span class="badge badge-imported" style="display:block; margin-bottom:4px; text-align:center;">CC-BY 4.0</span>
                            <span class="badge badge-imported" style="display:block; text-align:center;">CC-BY-NC 4.0</span>
                        </td>
                        <td>
                            <a href="https://www.gbif.org" target="_blank" rel="noopener" style="color: #2b5c8f; text-decoration: underline;">gbif.org</a>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--color-text-muted);">
                            "GBIF Occurrence Download (17 August 2026). Occurrences filtered by country=GB, geometry polygon covering Scotland, and species Sciurus vulgaris, Martes martes, Felis silvestris, Cervus elaphus, Castor fiber, Lutra lutra, Phoca vitulina."
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Photo and Media Credits -->
    <section style="margin-top: var(--spacing-xxl);">
        <h2>Media Credits & Fallbacks</h2>
        <p style="line-height: 1.6;">
            To ensure visual performance and respect intellectual property rights, the species catalogue uses inline vector SVG illustration fallbacks styled under HSL Highland Earth Tone palettes.
        </p>
        <ul style="line-height: 1.8; padding-left: var(--spacing-md);">
            <li><strong>Interface Icons:</strong> Hand-crafted semantic inline SVG paths.</li>
            <li><strong>Cartography Base Map:</strong> <a href="https://www.openstreetmap.org/" target="_blank" rel="noopener">OpenStreetMap contributors</a>, styled under Leaflet rendering standards.</li>
            <li><strong>Design Palette:</strong> Scottish Highlands vegetation tones inspired by NatureScot landscape design guidelines.</li>
        </ul>
    </section>

    <!-- Return Link -->
    <div style="margin-top: var(--spacing-xl); border-top: 1px solid var(--color-border); padding-top: var(--spacing-lg);">
        <a href="/index.php" class="btn btn-primary">&larr; Return to Home</a>
    </div>
</div>

<?php
require_once __DIR__ . '/../views/layouts/footer.php';
?>
