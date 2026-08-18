/**
 * Map Coordinates Picker (Leaflet.js)
 * Enables visual location selection for observation reports.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('picker-map');
    if (!mapContainer) return; // Exit if picker map container is not present

    // Form Coordinate Input Elements
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (!latInput || !lngInput) return;

    // Fetch initial coordinates from form values, fallback to Scotland center
    let initialLat = parseFloat(latInput.value);
    let initialLng = parseFloat(lngInput.value);

    if (isNaN(initialLat) || isNaN(initialLng)) {
        initialLat = 56.4907;
        initialLng = -4.2026;
    }

    const defaultZoom = 7;

    // Initialize Map
    const map = L.map('picker-map', {
        scrollWheelZoom: true,
        tap: true
    }).setView([initialLat, initialLng], defaultZoom);

    // Add Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);

    // Create a draggable marker at initial coordinates
    const marker = L.marker([initialLat, initialLng], {
        draggable: true,
        title: "Sighting Location"
    }).addTo(map);

    // Update form coordinates helper
    function updateFormCoordinates(lat, lng) {
        latInput.value = parseFloat(lat).toFixed(8);
        lngInput.value = parseFloat(lng).toFixed(8);
    }

    // Marker Drag Event
    marker.on('dragend', (e) => {
        const position = marker.getLatLng();
        updateFormCoordinates(position.lat, position.lng);
        map.panTo(position);
    });

    // Map Click Event
    map.on('click', (e) => {
        const clickLatLng = e.latlng;
        marker.setLatLng(clickLatLng);
        updateFormCoordinates(clickLatLng.lat, clickLatLng.lng);
        map.panTo(clickLatLng);
    });

    // Input changes watcher to reposition marker dynamically if user types
    function syncMarkerWithInputs() {
        const typedLat = parseFloat(latInput.value);
        const typedLng = parseFloat(lngInput.value);

        if (!isNaN(typedLat) && !isNaN(typedLng) && typedLat >= -90 && typedLat <= 90 && typedLng >= -180 && typedLng <= 180) {
            const newPos = [typedLat, typedLng];
            marker.setLatLng(newPos);
            map.panTo(newPos);
        }
    }

    latInput.addEventListener('input', syncMarkerWithInputs);
    lngInput.addEventListener('input', syncMarkerWithInputs);
});
