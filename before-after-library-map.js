// --- NEW: Define Custom Icons ---
const redIcon = new L.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const yellowIcon = new L.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const greyIcon = new L.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

document.addEventListener('DOMContentLoaded', function () {
    
    // Get modal elements
    const modal = document.getElementById('all-sites-map-modal');
    const openBtn = document.getElementById('open-all-sites-map-modal');
    const closeBtn = document.getElementById('all-sites-map-modal-close');
    
    // Check if all elements are present on the page
    if (!modal || !openBtn || !closeBtn) {
        // Elements are not on this page, so do nothing.
        return;
    }

    let map; // Variable to hold the map instance
    let mapInitialized = false; // Flag to check if map is already initialized

    // --- Function to initialize the map ---
    function initMap() {
        if (mapInitialized) {
            // If map is already initialized, just ensure it's sized correctly
            map.invalidateSize();
            return;
        }

        // Initialize the map. We'll set a default center (Europe) and zoom.
        // It will be auto-fitted later if there are markers.
        map = L.map('all-sites-leaflet-map').setView([47.5, 14.0], 4);

        // Add the OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Check if our site data exists
        if (typeof beforeafter_all_sites_data !== 'undefined' && beforeafter_all_sites_data.sites && beforeafter_all_sites_data.sites.length > 0) {
            
            let markers = []; // Array to hold marker lat/lng for bounds fitting

            // Loop through each site and create a marker
           // Loop through each site and create a marker
            beforeafter_all_sites_data.sites.forEach(function(site) {
                if (site.lat && site.lng) {
                    const latLng = [site.lat, site.lng];
                    
                    // --- NEW: Select icon based on conclusion ---
                    let selectedIcon = greyIcon; // Default
                    if (site.conclusion === 'Probable Clearcut') {
                        selectedIcon = redIcon;
                    } else if (site.conclusion === 'Probable Thinning') {
                        selectedIcon = yellowIcon;
                    }
                    
                    // --- NEW: Build popup content ---
                    let popupContent = `<strong>${site.title}</strong>`;
                    if (site.sitename) {
                        popupContent += `<br><em>${site.sitename}</em>`;
                    }
                    if (site.disturbed_date) {
                        popupContent += `<br>Disturbed: ${site.disturbed_date}`;
                    }
                    popupContent += `<br><a href="${site.url}" target="_blank">View Details</a>`;
                    
                    // Create marker, add to map, and add to our markers array
                    L.marker(latLng, { icon: selectedIcon }) // Pass the selected icon here
                        .addTo(map)
                        .bindPopup(popupContent);
                    
                    markers.push(latLng);
                }
            });

            // If we have markers, fit the map bounds to show all of them
            if (markers.length > 0) {
                map.fitBounds(markers, { padding: [50, 50] }); // Add 50px padding
            }
        }
        
        mapInitialized = true; // Set flag
    }

    // --- Event Listeners ---

    // When the user clicks the button, open the modal and init the map
    openBtn.addEventListener('click', function () {
        modal.classList.add('is-visible'); // Use class for visibility
        
        // We initialize or refresh the map *after* the modal is visible.
        // Using setTimeout ensures the modal is rendered first.
        setTimeout(function() {
            initMap();
        }, 10); 
    });

    // When the user clicks on <span> (x), close the modal
    closeBtn.addEventListener('click', function () {
        modal.classList.remove('is-visible');
    });

    // When the user clicks anywhere outside of the modal content, close it
    modal.addEventListener('click', function (event) {
        if (event.target === modal) { // Check if click is on the modal background
            modal.classList.remove('is-visible');
        }
    });

});