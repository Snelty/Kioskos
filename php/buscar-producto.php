<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$password = "";
$database = "kioskos";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$product_name = isset($_GET['product_name']) ? $conn->real_escape_string($_GET['product_name']) : '';
$user_lat = isset($_GET['user_lat']) ? floatval($_GET['user_lat']) : null;
$user_lng = isset($_GET['user_lng']) ? floatval($_GET['user_lng']) : null;

if (empty($product_name)) {
    echo "<h1>Por favor, ingresa un nombre de producto.</h1>";
    exit;
}

// Consulta base sin ordenamiento por distancia
$query_base = "SELECT DISTINCT k.id AS kiosko_id, k.name_kiosko, k.image_url, k.latitude, k.longitude
               FROM kiosko_inventory ki
               JOIN products p ON ki.product_id = p.id
               JOIN kioskos k ON ki.kiosko_id = k.id
               WHERE p.name LIKE ?";

// Si tenemos la ubicación del usuario, añadimos el cálculo de distancia
if ($user_lat !== null && $user_lng !== null) {
    $query = "SELECT *, 
              (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
              cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
              sin(radians(latitude)))) AS distance
              FROM ($query_base) AS subquery
              ORDER BY distance";
    $stmt = $conn->prepare($query);
    $product_name = "%$product_name%";
    $stmt->bind_param("ddds", $user_lat, $user_lng, $user_lat, $product_name);
} else {
    $stmt = $conn->prepare($query_base);
    $product_name = "%$product_name%";
    $stmt->bind_param("s", $product_name);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $kiosk_info = $result->fetch_all(MYSQLI_ASSOC);

    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Resultados de Búsqueda</title>";
    echo "<link rel='stylesheet' href='/proyectin/css/buscarProductos.css'>";
    echo "<link rel='stylesheet' href='/proyectin/css/checkbox.css'>";
    
    // Estilos mejorados
    echo "<style>
        .kiosko-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .kiosko-item {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        .kiosko-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .kiosko-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .kiosko-item h3 {
            margin: 10px 0;
            color: #333;
            font-size: 1.2em;
        }
        .distance {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .maps-button {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .maps-button:hover {
            background-color: #45a049;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
                   .navigation-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            gap: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .navigation-button {
            padding: 10px 20px;
            background-color: rgb(192, 40, 40);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .navigation-button:hover {
            background-color: rgb(162, 30, 30);
        }
        .navigation-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>";

    // Scripts
    echo "<script>
        // Función para obtener la ubicación del usuario
        function getUserLocation() {
            document.querySelector('.loading').style.display = 'block';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        window.location.href = window.location.pathname + 
                            '?product_name=" . urlencode(trim($product_name, '%')) . 
                            "&user_lat=' + position.coords.latitude + 
                            '&user_lng=' + position.coords.longitude;
                    },
                    function(error) {
                        console.error('Error obteniendo ubicación:', error);
                        document.querySelector('.loading').style.display = 'none';
                        alert('No se pudo obtener tu ubicación. Los resultados se mostrarán sin ordenar por distancia.');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            }
        }

        // Función para abrir Google Maps
        function abrirEnMaps(lat, lng, nombreKiosko) {
            lat = parseFloat(lat).toFixed(6);
            lng = parseFloat(lng).toFixed(6);
            nombreKiosko = encodeURIComponent(nombreKiosko);
            
            const url = 'https://www.google.com/maps?q=' + lat + ',' + lng + '&z=17';
            window.open(url, '_blank');
        }

        // Llamar a getUserLocation si no tenemos las coordenadas
        window.onload = function() {
            if (!" . ($user_lat ? 'true' : 'false') . ") {
                getUserLocation();
            }
        };
    </script>";
    
    echo "</head>";
    echo "<body>";

    // Indicador de carga
    echo '<div class="loading">Obteniendo tu ubicación...</div>';

    // Menú
    echo '<input type="checkbox" id="checkbox" />';
    echo '<div class="toggle" onclick="document.getElementById(\'checkbox\').click();">';
    echo '    <div class="bars" id="bar1"></div>';
    echo '    <div class="bars" id="bar2"></div>';
    echo '    <div class="bars" id="bar3"></div>';
    echo '</div>';
    
    echo "<div class='resultado-container'>";
    echo '<h1>Resultados de Búsqueda</h1>';
    echo '<div class="menu">';
    echo '<a href="/proyectin/html/iniciarsesion.html">Iniciar Sesión</a>';
    echo '<a href="../php/mi-cuenta.php">Mi cuenta</a>';
    echo '<a href="/proyectin/html/sobrenosotros.html">Sobre Nosotros</a>';
    echo '<a href="/proyectin/html/info.html">Informacion</a>';
    echo '</div>';

    if (count($kiosk_info) > 0) {
        echo "<h2>Kioskos que tienen el producto '" . htmlspecialchars(trim($product_name, '%')) . "':</h2>";
        echo "<div class='kiosko-grid'>";

        foreach ($kiosk_info as $kiosko) {
            echo "<div class='kiosko-item'>";
            echo "<img src='" . htmlspecialchars($kiosko['image_url']) . "' alt='Imagen del kiosko'>";
            echo "<h3>" . htmlspecialchars($kiosko['name_kiosko']) . "</h3>";
            
            if (isset($kiosko['distance'])) {
                echo "<p class='distance'>Distancia: " . 
                     number_format($kiosko['distance'], 2) . " km</p>";
            }
            
            echo "<button class='maps-button' onclick='abrirEnMaps(" . 
                 floatval($kiosko['latitude']) . ", " . 
                 floatval($kiosko['longitude']) . ", \"" . 
                 htmlspecialchars($kiosko['name_kiosko'], ENT_QUOTES) . 
                 "\")'>Ver en Google Maps</button>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<p class='no-results'>No se encontraron kioscos con el producto '" . 
             htmlspecialchars(trim($product_name, '%')) . "'.</p>";
    }

    echo "</div>";
    echo "</body>";
    echo "</html>";
} else {
    echo "<p>Error al ejecutar la consulta: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>