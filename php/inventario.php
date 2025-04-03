<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'kiosko_owners') {
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

// Obtener el kiosko_id del dueño
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM kioskos WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$kiosko_result = $stmt->get_result();
$kiosko = $kiosko_result->fetch_assoc();
$kiosko_id = $kiosko['id'];

// Manejo de la actualización AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product']) && isset($_POST['quantity'])) {
    $product_name = $_POST['product'];
    $new_quantity = (int)$_POST['quantity'];

    $stmt = $conn->prepare("UPDATE kiosko_inventory ki
                            JOIN products p ON ki.product_id = p.id
                            SET ki.quantity = ?
                            WHERE ki.kiosko_id = ? AND p.name = ?");
    $stmt->bind_param("iis", $new_quantity, $kiosko_id, $product_name);

    if ($stmt->execute()) {
        echo "Cantidad actualizada exitosamente";
    } else {
        echo "Error al actualizar la cantidad";
    }
    exit(); // Terminar el script después de manejar la solicitud AJAX
}

// Búsqueda de productos
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

$stmt = $conn->prepare("SELECT ki.quantity, p.name AS product_name, p.image_url 
                         FROM kiosko_inventory ki 
                         JOIN products p ON ki.product_id = p.id 
                         WHERE ki.kiosko_id = ? AND p.name LIKE ?");
$like_query = "%" . $search_query . "%";
$stmt->bind_param("is", $kiosko_id, $like_query);
$stmt->execute();
$inventory_result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Kiosko</title>
    <link rel="stylesheet" href="../css/inv.css">
</head>
<body>
    <header>
        <h1>Inventario de Kiosko</h1>
    </header>

    <form method="POST" action="">
        <input type="text" name="search" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit">Buscar</button>
    </form>

    <div class="inventory">
        <h2>Productos en Inventario</h2>
        <?php
        if ($inventory_result->num_rows > 0) {
            while ($row = $inventory_result->fetch_assoc()) {
                echo "<div class='product'>";
                echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Imagen de " . htmlspecialchars($row['product_name']) . "'>";
                echo "<p><strong>Nombre del Producto:</strong> " . htmlspecialchars($row['product_name']) . "</p>";
                echo "<div class='quantity-control'>";
                echo "<button class='decrement' data-product='" . htmlspecialchars($row['product_name']) . "'>-</button>";
                echo "<span class='quantity' data-product='" . htmlspecialchars($row['product_name']) . "'>" . htmlspecialchars($row['quantity']) . "</span>";
                echo "<button class='increment' data-product='" . htmlspecialchars($row['product_name']) . "'>+</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No se encontraron productos en el inventario.</p>";
        }
        ?>
    </div>

    <a href="mi-cuenta.php">Volver a Mi Cuenta</a>

    <script src="../java/inventory.js"></script>
</body>
</html>

<?php
$conn->close();
?>
