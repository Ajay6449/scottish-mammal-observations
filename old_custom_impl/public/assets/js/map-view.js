/**
 * Sighting Maps Renderer (Leaflet.js)
 * Plots observations on an interactive map centered on Scotland.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('recent-map');
    if (!mapContainer) return; // Exit if no map container on this page

    // Default center point of Scotland
    const scotlandLat = 56.7000;
    const scotlandLng = -4.2000;
    const defaultZoom = 6.2;

    // Initialize Leaflet Map
    const map = L.map('recent-map', {
        scrollWheelZoom: false, // Prevent accidental scrolling while zooming page
        tap: true
    }).setView([scotlandLat, scotlandLng], defaultZoom);

    // Add OpenStreetMap tile layers
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);

    // Get markers from global variable
    const markersData = window.mapMarkersData || [];

    if (markersData.length === 0) {
        // Center map on Scotland and do nothing else
        return;
    }

    const bounds = [];

    // Custom styling pins and metadata attribution popups
    markersData.forEach(item => {
        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);
        
        if (isNaN(lat) || isNaN(lng)) return;

        bounds.push([lat, lng]);

        // Select marker pin class based on type
        const isImported = (item.observation_type === 'imported');
        const pinClass = isImported ? 'marker-pin-imported' : 'marker-pin-user';
        const typeLabel = isImported ? 'Scientific Record (GBIF)' : 'Community Sighting';
        const badgeClass = isImported ? 'badge-imported' : 'badge-user';
        
        // Define Custom Div Icon
        const markerIcon = L.divIcon({
            className: 'custom-map-marker',
            html: `<div class="marker-pin ${pinClass}" title="${typeLabel}"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });

        // Construct HTML content for popup bubble
        let popupContent = `
            <div style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; line-height: 1.4; color: #2c2924; min-width: 180px;">
                <h4 style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.1rem; color: #1e3f20; margin: 0 0 6px 0; border-bottom: 1px solid #e6e2db; padding-bottom: 4px; display: flex; flex-direction: column; gap: 4px;">
                    ${escapeHtml(item.common_name)}
                    <span class="badge ${badgeClass}" style="font-size: 0.65rem; align-self: flex-start; padding: 1px 6px;">${typeLabel}</span>
                </h4>
                <p style="margin: 0 0 4px 0;"><strong>Location:</strong> ${escapeHtml(item.location_name)}</p>
                <p style="margin: 0 0 4px 0;"><strong>Date:</strong> ${formatDate(item.observation_date)}</p>
        `;

        if (isImported) {
            popupContent += `
                <p style="margin: 0 0 4px 0;"><strong>Provider:</strong> ${escapeHtml(item.data_provider || 'GBIF network')}</p>
                <p style="margin: 0 0 8px 0;"><strong>Licence:</strong> <span style="font-weight: 500; color: var(--color-primary);">${escapeHtml(item.licence || 'CC-BY 4.0')}</span></p>
                <div style="border-top: 1px solid #e6e2db; padding-top: 6px; margin-top: 6px;">
                    <a href="${escapeHtml(item.source_url)}" target="_blank" rel="noopener" style="color: #2b5c8f; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 2px;">
                        View GBIF Record 
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            `;
        } else {
            popupContent += `
                <p style="margin: 0;"><strong>Observed By:</strong> <span style="color: #4a703c; font-weight: 500;">${escapeHtml(item.observer_name)}</span></p>
            `;
        }

        popupContent += `</div>`;

        // Create Marker with custom icon
        L.marker([lat, lng], { icon: markerIcon })
            .addTo(map)
            .bindPopup(popupContent, { maxWidth: 300 });
    });

    // Auto-fit map zoom to markers boundary if we have multiple
    if (bounds.length > 0) {
        if (bounds.length === 1) {
            map.setView(bounds[0], 9);
        } else {
            // Fit to markers bounds with padding
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Helper: Escape HTML strings to protect against cross-site scripting (XSS)
    function escapeHtml(unsafeStr) {
        if (!unsafeStr) return '';
        return unsafeStr
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper: format ISO date to readable string
    function formatDate(dateString) {
        if (!dateString) return '';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', options);
    }
});
