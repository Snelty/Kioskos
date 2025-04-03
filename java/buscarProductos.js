document.addEventListener("DOMContentLoaded", function() {
  const productoInput = document.getElementById('productoInput');
  const buscarProductoButton = document.getElementById('buscarProducto');

  if (!productoInput || !buscarProductoButton) {
      console.error('Error: Uno o más elementos no se encontraron en el DOM');
      return; 
  }

  buscarProductoButton.addEventListener('click', buscarProducto);

  function buscarProducto(e) {
      e.preventDefault();
      const producto = productoInput.value.trim();
      
      if (!producto) {
          alert("Por favor, ingrese un nombre de producto.");
          return;
      }

    
      window.location.href = `,,/html/buscarProducto.html?product_name=${encodeURIComponent(producto)}`;
  }
});
