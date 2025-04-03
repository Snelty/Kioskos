document.addEventListener("DOMContentLoaded", function() {
    const productoInput = document.getElementById('productoInput');
    const buscarProductoButton = document.getElementById('buscarProducto');
    const resultadoDiv = document.getElementById('resultado');
  
    if (!productoInput || !buscarProductoButton || !resultadoDiv) {
      console.error('Error: Uno o más elementos no se encontraron en el DOM');
      return;
    }
  
    buscarProductoButton.addEventListener('click', buscarProducto);
  
    function buscarProducto(e) {
      e.preventDefault();
      const producto = productoInput.value.trim();
      
      if (!producto) {
        mostrarResultado("Por favor, ingrese un nombre de producto.");
        return;
      }
  
      mostrarResultado("Buscando...");
  
      fetch('./php/buscar-producto.php?product_name=' + encodeURIComponent(producto))
        .then(response => {
          if (!response.ok) {
            throw new Error('Error de red: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          console.log('Datos recibidos:', data); // Para ver errorsillos
          if (data.error) {
            mostrarResultado(`Error: ${data.error}`);
          } else if (Array.isArray(data) && data.length > 0) {
            let resultadoHTML = `<h2>Kioscos que tienen el producto "${producto}":</h2><ul>`;
            data.forEach(kiosko => {
              resultadoHTML += `<li>Kiosco: ${kiosko.name_kiosko} (ID: ${kiosko.kiosko_id})</li>`;
            });
            resultadoHTML += '</ul>';
            mostrarResultado(resultadoHTML);
          } else if (data.message) {
            mostrarResultado(data.message);
          } else {
            mostrarResultado(`No se encontraron kioscos con el producto "${producto}".`);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarResultado("Ocurrió un error al buscar el producto: " + error.message);
        });
    }
  
  
    function mostrarResultado(mensaje) {
      const contenidoActual = resultadoDiv.innerHTML;
      const nuevoContenido = `
        <h1>KiosKos</h1>
        <button id="botonEncontrarCercano">Encontrar KiosKo más cercano</button>
        <hr>
        <div class="buscar-cuadradito">
          <input type="text" id="productoInput" placeholder="Wafer">
          <button id="buscarProducto">Buscar</button>
        </div>
        <div>${mensaje}</div>
      `;
      resultadoDiv.innerHTML = nuevoContenido;
      
      // nuevo boton ayuda!
      const newBuscarProductoButton = document.getElementById('buscarProducto');
      if (newBuscarProductoButton) {
        newBuscarProductoButton.addEventListener('click', buscarProducto);
      }
    }
  });