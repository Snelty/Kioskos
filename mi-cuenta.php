<?php
session_start();

// Verifica si la sesión está iniciada como cliente, kiosko_owner o staff
if (!isset($_SESSION['user_id']) || 
    ($_SESSION['user_type'] !== 'client' && $_SESSION['user_type'] !== 'kiosko_owner' && $_SESSION['user_type'] !== 'staffs')) {
    header("Location: ../html/iniciarsesion.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kioskos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Verifica el tipo de usuario y obtiene la información correspondiente
if ($_SESSION['user_type'] === 'client') {
    $stmt = $conn->prepare("SELECT name, email FROM clients WHERE id = ?");
} elseif ($_SESSION['user_type'] === 'kiosko_owner') {
    $stmt = $conn->prepare("SELECT name, email FROM kiosko_owners WHERE id = ?");
} elseif ($_SESSION['user_type'] === 'staffs') {
    $stmt = $conn->prepare("SELECT name, email FROM staffs WHERE id = ?");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$table_exists = $conn->query("SHOW TABLES LIKE 'reviews'")->num_rows > 0;

if ($table_exists) {
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE client_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $reviews = $stmt->get_result();
} else {
    $reviews = null;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Kioscos</title>
    <link rel="stylesheet" href="../css/checkbox.css"> <!-- Verifica esta ruta -->
    <style>
        /* Tu CSS adicional */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            padding: 20px;
        }
        header {
            background: #50b3a2;
            color: white;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #e8491d 3px solid;
        }
        header h1 {
            margin: 0;
            text-align: center;
            padding-bottom: 10px;
        }
        .content {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .info-section, .reviews-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-section {
            flex: 1;
            margin-right: 20px;
        }
        .reviews-section {
            flex: 1;
        }
        h2 {
            color: #50b3a2;
        }
        .review {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        /* Estilos adicionales */
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Mi Cuenta</h1>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <div class="info-section">
                <h2>Información Personal</h2>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="reviews-section">
                <h2>Mis Reseñas</h2>
                <?php
                if ($reviews->num_rows > 0) {
                    while ($review = $reviews->fetch_assoc()) {
                        echo "<div class='review'>";
                        echo "<p><strong>Fecha:</strong> " . htmlspecialchars($review['fecha']) . "</p>";
                        echo "<p><strong>Comentario:</strong> " . htmlspecialchars($review['comentario']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Aún no has hecho ninguna reseña.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <input type="checkbox" id="checkbox" />
    <div class="toggle" onclick="document.getElementById('checkbox').click();">
        <div class="bars" id="bar1"></div>
        <div class="bars" id="bar2"></div>
        <div class="bars" id="bar3"></div>
    </div>

    <div class="menu">
        <a href="../index.html">Menu principal</a>

        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="mi-cuenta.php">Mi cuenta</a>';
            echo '<a href="logout.php">Cerrar sesión</a>'; 
        } else {
            echo '<a href="iniciarsesion.html">Iniciar sesión</a>';
        }
        ?>

        <a href="/html/sobrenosotros.html">Sobre Nosotros</a>
        <a href="/html/info.html">Información</a>
    </div>

</body>
</html>
