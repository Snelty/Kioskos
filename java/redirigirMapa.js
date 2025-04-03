function redirigirMapa(destLat, destLon) {
    obtenerUbicacionActual()
      .then((posicion) => {
        const userLat = posicion.coords.latitude;
        const userLon = posicion.coords.longitude;
        
        const url = `/proyectin/html/mapa.html?userLat=${userLat}&userLon=${userLon}&destLat=${destLat}&destLon=${destLon}`;
        console.log(`Redirigiendo a: ${url}`);
        window.location.href = url;
      })
      .catch((error) => {
        console.error('Error al obtener la ubicación:', error);
        alert('Error al obtener la ubicación. Asegúrate de que has permitido el acceso a tu ubicación.');
      });
  }
  