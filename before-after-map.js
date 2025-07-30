document.addEventListener('DOMContentLoaded', function() {
    if (typeof beforeafter_map_data !== 'undefined' && beforeafter_map_data.lat && beforeafter_map_data.lng) {
        var lat = parseFloat(beforeafter_map_data.lat);
        var lng = parseFloat(beforeafter_map_data.lng);
        var zoom = parseInt(beforeafter_map_data.zoom, 10) || 13; // Default zoom to 13

        if (!isNaN(lat) && !isNaN(lng)) {
            var map = L.map('beforeafter-map').setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map);
        }
    }
});