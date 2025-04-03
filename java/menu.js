document.addEventListener('DOMContentLoaded', function() {
    const menuContainer = document.querySelector('.menu');

    const isLoggedIn = () => {
        return Boolean(sessionStorage.getItem('user_id')); 
    };

    const links = [
        { href: '/proyectin/html/sobrenosotros.html', text: 'Sobre Nosotros' },
        { href: '/proyectin/html/info.html', text: 'Información' },
    ];


    links.forEach(link => {
        const a = document.createElement('a');
        a.href = link.href;
        a.textContent = link.text;
        menuContainer.appendChild(a);
    });

    const accountLink = document.createElement('a');
    if (isLoggedIn()) {
        accountLink.href = '/php/mi-cuenta.php';
        accountLink.textContent = 'Mi cuenta';
    } else {
        accountLink.href = 'iniciarsesion.html';
        accountLink.textContent = 'Iniciar sesión';
    }
    menuContainer.appendChild(accountLink);
});
