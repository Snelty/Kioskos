let map;
let currentKioskoIndex = 0;
let kioskosData = [];
let userLocation = null;
let currentRoute = null;
let markers = [];

function initMap() {
    const urlParams = new URLSearchParams(window.location.search);
    const userLat = parseFloat(urlParams.get('userLat'));
    const userLon = parseFloat(urlParams.get('userLon'));
    
    // Obtener todos los kioskos de la URL
    const kioskos = JSON.parse(decodeURIComponent(urlParams.get('kioskos') || '[]'));
    
    if (!userLat || !userLon || kioskos.length === 0) {
        console.error('Faltan parámetros necesarios en la URL');
        return;
    }

    userLocation = { lat: userLat, lon: userLon };
    kioskosData = kioskos;

    // Inicializar el mapa
    map = L.map('map').setView([userLat, userLon], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Marcador de la ubicación del usuario
    const userMarker = L.marker([userLat, userLon], {
        icon: L.divIcon({
            html: '<div style="background-color: blue; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white;"></div>',
            className: 'user-marker'
        })
    }).addTo(map);
    userMarker.bindPopup('Tu ubicación');

    // Inicializar la navegación
    actualizarBotonesNavegacion();
    mostrarKioskoActual();
}

function mostrarKioskoActual() {
    // Limpiar marcadores y rutas anteriores
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
    if (currentRoute) {
        map.removeLayer(currentRoute);
    }

    const kiosko = kioskosData[currentKioskoIndex];
    
    // Crear marcador del kiosko
    const marker = L.marker([kiosko.lat, kiosko.lon]).addTo(map);
    marker.bindPopup(`<b>${kiosko.nombre}</b>`).openPopup();
    markers.push(marker);

    // Actualizar contador
    document.getElementById('kiosko-counter').textContent = 
        `${currentKioskoIndex + 1} de ${kioskosData.length}`;

    // Actualizar detalles del kiosko
    document.getElementById('kiosko-details').innerHTML = `
        <p><strong>Nombre:</strong> ${kiosko.nombre}</p>
        <p><strong>Distancia:</strong> ${kiosko.distancia.toFixed(2)} km</p>
    `;

    // Calcular y mostrar ruta
    calcularRuta(userLocation, kiosko);
}

function calcularRuta(origen, destino) {
    const apiKey = '5b3ce3597851110001cf62481e6fceac8e4f4f1a94128681efff6266';
    const url = `https://api.openrouteservice.org/v2/directions/foot-walking?api_key=${apiKey}&start=${origen.lon},${origen.lat}&end=${destino.lon},${destino.lat}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.features && data.features[0] && data.features[0].geometry) {
                const routeCoordinates = data.features[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                
                if (currentRoute) {
                    map.removeLayer(currentRoute);
                }
                
                currentRoute = L.polyline(routeCoordinates, {
                    color: 'blue',
                    weight: 4,
                    opacity: 0.7
                }).addTo(map);

                map.fitBounds(currentRoute.getBounds(), { padding: [50, 50] });
            }
        })
        .catch(error => {
            console.error('Error al obtener la ruta:', error);
            dibujarRutaDirecta(origen, destino);
        });
}

function dibujarRutaDirecta(origen, destino) {
    if (currentRoute) {
        map.removeLayer(currentRoute);
    }
    
    currentRoute = L.polyline([
        [origen.lat, origen.lon],
        [destino.lat, destino.lon]
    ], {
        color: 'red',
        dashArray: '5, 10',
        weight: 3
    }).addTo(map);

    map.fitBounds(currentRoute.getBounds(), { padding: [50, 50] });
}

function cambiarKiosko(direccion) {
    const nuevoIndex = currentKioskoIndex + direccion;
    
    if (nuevoIndex >= 0 && nuevoIndex < kioskosData.length) {
        currentKioskoIndex = nuevoIndex;
        mostrarKioskoActual();
        actualizarBotonesNavegacion();
    }
}

function actualizarBotonesNavegacion() {
    document.getElementById('anterior').disabled = currentKioskoIndex === 0;
    document.getElementById('siguiente').disabled = currentKioskoIndex === kioskosData.length - 1;
}

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('map')) {
        initMap();
    } else {
        console.error('El elemento con id "map" no se encontró en el DOM');
    }
});