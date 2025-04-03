document.querySelectorAll('.increment').forEach(button => {
    button.addEventListener('click', function() {
        const product = this.getAttribute('data-product');
        const quantityElement = document.querySelector(`.quantity[data-product="${product}"]`);
        let quantity = parseInt(quantityElement.textContent);

        quantity += 1;
        quantityElement.textContent = quantity;

        // Llamada AJAX para actualizar la base de datos
        updateQuantity(product, quantity);
    });
});

document.querySelectorAll('.decrement').forEach(button => {
    button.addEventListener('click', function() {
        const product = this.getAttribute('data-product');
        const quantityElement = document.querySelector(`.quantity[data-product="${product}"]`);
        let quantity = parseInt(quantityElement.textContent);

        if (quantity > 0) {
            quantity -= 1;
            quantityElement.textContent = quantity;

            // Llamada AJAX para actualizar la base de datos
            updateQuantity(product, quantity);
        }
    });
});

function updateQuantity(product, newQuantity) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true); // La misma página 'inventory.php'
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log(xhr.responseText); // Aquí puedes manejar la respuesta si es necesario
        }
    };

    xhr.send('product=' + encodeURIComponent(product) + '&quantity=' + newQuantity);
}
