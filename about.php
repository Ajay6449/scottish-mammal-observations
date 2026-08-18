<?php
/**
 * Scottish Mammal Observations Database - About Page
 * Describes the project, dataset, and technology stack
 *
 * SET08101 Web Technologies Coursework
 */

$pageTitle = 'About Project';
$pageDescription = 'Learn about the Scottish Mammal Observations project, its objectives, datasets, technology stack, and academic integrity declarations.';
$currentPage = 'about';

require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="hero" style="margin-bottom: var(--spacing-xl);">
    <div class="container">
        <h1>About the Project</h1>
        <p>Documenting, protecting, and understanding the terrestrial mammal species of Scotland through data integration and mapping.</p>
    </div>
</section>

<div class="about-grid">
    <div>
        <h2>Project Scope</h2>
        <p>
            The Scottish Mammal Observations platform is an interactive, academic web portal designed to visualize occurrences, conservation statuses, and biological classifications of terrestrial mammals across Scotland. 
        </p>
        <p>
            Initially provided as a basic directory, this enhanced implementation features a responsive earth-tone design system, query filters, dynamic column sorting, and paginated observations lists to manage heavy record volumes (such as the Red Deer with over 1,100 records).
        </p>
        
        <h3 style="margin-top: var(--spacing-lg);">Technology Stack</h3>
        <p>
            The platform is built strictly on the module's prescribed technologies:
        </p>
        <ul>
            <li><strong>HTML5 & CSS3:</strong> Built with semantic tags (header, main, footer, section) and a modern mobile-first grid using custom HSL properties.</li>
            <li><strong>Vanilla JavaScript:</strong> Client-side validation, progressive filter queries, Leaflet.js map layer rendering, and image modal popups.</li>
            <li><strong>PHP 8.0+:</strong> Server-side page templates, prepared database queries, and secure session authentications.</li>
            <li><strong>MySQL Database:</strong> Relational model using index keys on species scientific names, dietary habits, and foreign keys connecting observations.</li>
        </ul>
    </div>
    
    <div>
        <div class="stats-summary-card">
            <div class="stat-number">34</div>
            <div class="stat-label">Terrestrial Species</div>
            
            <div class="stat-number" style="margin-top: var(--spacing-md);">3,863</div>
            <div class="stat-label">Scientific Observations</div>
            
            <div class="stat-number" style="margin-top: var(--spacing-md);">100%</div>
            <div class="stat-label">Responsive Layout</div>
        </div>
    </div>
</div>

<div class="chart-card" style="margin-top: var(--spacing-xl); padding: var(--spacing-xl);">
    <h2>Academic Integrity & Provenance</h2>
    <p>
        This coursework implementation strictly respects dataset provenance. The occurrence observations and mammal classifications are sourced from the <strong>Global Biodiversity Information Facility (GBIF)</strong>, representing real-world ecological data collected between 1981 and 2016.
    </p>
    <p>
        All scientific records retain licensing references (CC0, CC-BY, and CC-BY-NC) and are properly distinguished from user-submitted community sightings in compliance with academic guidelines.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
