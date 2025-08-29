document.addEventListener('DOMContentLoaded', function() {
    if (typeof beforeafter_map_data === 'undefined' || !beforeafter_map_data.lat || !beforeafter_map_data.lng) {
        return; // Exit if essential map data is missing
    }

    var lat = parseFloat(beforeafter_map_data.lat);
    var lng = parseFloat(beforeafter_map_data.lng);
    var zoom = parseInt(beforeafter_map_data.zoom, 10) || 13;

    if (isNaN(lat) || isNaN(lng)) {
        return;
    }

    // --- 1. Initialize Map and Base Layers ---
    var map = L.map('beforeafter-map').setView([lat, lng], zoom);

    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri'
    });
    
    var baseLayers = {
        "Street Map": osm,
        "Satellite": satellite
    };
    
    var overlayLayers = {};
    var layerControl = L.control.layers(baseLayers, overlayLayers).addTo(map); // Create control once

    // --- 2. Add Marker ---
    L.marker([lat, lng]).addTo(map);

    // --- 3. Add Raster Image Overlay ---
    const bounds = beforeafter_map_data.raster_bounds;
    if (beforeafter_map_data.raster_url && bounds.ymin && bounds.ymax && bounds.xmin && bounds.xmax) {
        var imageUrl = beforeafter_map_data.raster_url;
        var imageBounds = [
            [parseFloat(bounds.ymin), parseFloat(bounds.xmin)],
            [parseFloat(bounds.ymax), parseFloat(bounds.xmax)]
        ];

        var rasterOverlay = L.imageOverlay(imageUrl, imageBounds, {
            opacity: 0.7,
            interactive: false
        }).addTo(map);
        
        rasterOverlay.bringToFront();
        layerControl.addOverlay(rasterOverlay, "Disturbance Raster");
    }

    // --- 4. Add GeoJSON Polygon Layer ---
    if (beforeafter_map_data.geojson_url) {
        fetch(beforeafter_map_data.geojson_url)
            .then(response => response.json())
            .then(data => {
                var geoJsonLayer = L.geoJSON(data, {
                    style: {
                        color: "#c8007a",
                        weight: 2,
                        opacity: 0.8,
                        fillOpacity: 0.15
                    }
                }).addTo(map);
                layerControl.addOverlay(geoJsonLayer, "Natura 2000 Site Boundary");
            })
            .catch(error => console.error('Error loading GeoJSON file:', error));
    }
});