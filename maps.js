const lugares = [
  [-16.503806, -68.134561],
  [-16.504111, -68.133528],
  [-16.504674, -68.132667],
  [-16.504778, -68.132528],
  [-16.541426, -68.083522],
  [-16.505375, -68.132444],
  [-16.505361, -68.132278],
  [-16.505333, -68.132250],
  [-16.541426, -68.083522],
  [-16.505778, -68.132333],
  [-16.506222, -68.130694],
  [-16.506389, -68.130500],
  [-16.506528, -68.130444],
  [-16.506528, -68.130250],
  [-16.506722, -68.130222],
  [-16.507389, -68.129278],
  [-16.507361, -68.129278],
  [-16.541426, -68.083522],
  [-16.507833, -68.128361],
  [-16.507806, -68.128417],
  [-16.508056, -68.127750],
  [-16.541426, -68.083522],
  [-16.510028, -68.126083],
  [-16.510666, -68.126000],
  [-16.510833, -68.126333],
  [-16.511389, -68.126167],
];

function obtenerUbicacionActual() {
  return new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        console.log('Ubicación obtenida:', position.coords);
        resolve(position);
      },
      (error) => {
        console.error('Error al obtener la ubicación:', error);
        reject(error);
      },
      {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0
      }
    );
  });
}
//calc dist entre 2 puntos
function calcularDistancia(lat1, lon1, lat2, lon2) {
  const R = 6371; 
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c;
}

async function encontrarLugarMasCercano() {
  try {
    const posicion = await obtenerUbicacionActual();
    const userLat = posicion.coords.latitude;
    const userLon = posicion.coords.longitude;

    console.log(`Ubicación del usuario: ${userLat}, ${userLon}`);

    // Transformar lugares en formato de kioskos
    const kioskos = lugares.map((lugar, index) => {
      const distancia = calcularDistancia(userLat, userLon, lugar[0], lugar[1]);
      return {
        lat: lugar[0],
        lon: lugar[1],
        nombre: `Kiosko ${index + 1}`,
        distancia: distancia
      };
    });

    // Ordenar kioskos por distancia
    kioskos.sort((a, b) => a.distancia - b.distancia);

    // Construir la URL con los kioskos
    const url = `./html/mapa.html?userLat=${userLat}&userLon=${userLon}&kioskos=${encodeURIComponent(JSON.stringify(kioskos))}`;
    
    console.log(`Redirigiendo a: ${url}`);
    window.location.href = url;
  } catch (error) {
    console.error('Error al obtener la ubicación:', error);
    alert('Error al obtener la ubicación. Asegúrate de que has permitido el acceso a tu ubicación.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const boton = document.getElementById('botonEncontrarCercano');
  if (boton) {
    boton.addEventListener('click', encontrarLugarMasCercano);
  } else {
    console.error('El botón con ID "botonEncontrarCercano" no se encontró en el DOM');
  }
});