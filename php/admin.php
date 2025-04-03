<?php 
session_start();

// Verifica si el usuario está autenticado como admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staffs') {
    header("Location: ../html/iniciarsesion.html");
    exit();
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kioskos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_vendor'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_BCRYPT) : '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM kiosko_owners WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Insertar nuevo vendedor
            $stmt = $conn->prepare("INSERT INTO kiosko_owners (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "Nuevo vendedor añadido correctamente.";
            } else {
                echo "Error al añadir vendedor.";
            }
        } else {
            echo "El correo electrónico ya está registrado.";
        }

        $stmt->close();
    } else {
        echo "Por favor, completa todos los campos.";
    }
}

// Obtener la lista de vendedores actuales
$stmt = $conn->prepare("SELECT id, name, email FROM kiosko_owners");
$stmt->execute();
$vendors = $stmt->get_result();
// Obtener todas las reseñas
$reviews_query = "SELECT * FROM reviews ORDER BY created_at DESC";
$reviews_result = $conn->query($reviews_query);

// Obtener todos los usuarios
$users_query = "SELECT * FROM clients ORDER BY id DESC";
$users_result = $conn->query($users_query);

// Obtener todas las solicitudes de vendedor
$applications_query = "SELECT * FROM solicitudes_vendedores ORDER BY id DESC";
$applications_result = $conn->query($applications_query);

// Obtener todos los staffs
$staffs_query = "SELECT * FROM staffs ORDER BY id DESC";
$staffs_result = $conn->query($staffs_query);


$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../css/checkbox.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: auto;
            overflow: hidden;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #50b3a2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #50b3a2;
            color: white;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="email"], select {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
        button, .action-link {
            padding: 8px 12px;
            margin: 5px 0;
            background-color: #50b3a2;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        button:hover, .action-link:hover {
            background-color: #3d8b7d;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .edit-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .edit-form input[type="text"], .edit-form input[type="email"] {
            width: auto;
            flex: 1;
        }
    </style>
</head>
<body>
<?php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
    echo "<div style='background-color: " . ($message_type === 'error' ? '#f8d7da' : '#d4edda') . "; color: " . ($message_type === 'error' ? '#721c24' : '#155724') . "; padding: 10px; margin-bottom: 20px; border: 1px solid " . ($message_type === 'error' ? '#f5c6cb' : '#c3e6cb') . "; border-radius: 5px;'>
            " . htmlspecialchars($message) . "
          </div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

    <div class="container">
        <h1>Panel de Administración</h1>

        <!-- Reseñas -->
        <h2>Reseñas</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente ID</th>
                <th>Comentario</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            <?php if ($reviews_result->num_rows > 0) {
                while ($review = $reviews_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($review['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['client_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['comentario']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['created_at']) . "</td>";
                    echo "<td>
                            <a href='edit_review.php?id=" . $review['id'] . "' class='action-link'>Editar</a> |
                            <a href='admin_panel.php?delete_review=" . $review['id'] . "' class='action-link' onclick='return confirm(\"¿Estás seguro de eliminar esta reseña?\")'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay reseñas disponibles.</td></tr>";
            } ?>
        </table>

        <!-- Usuarios -->
        <h2>Usuarios</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
            <?php if ($users_result->num_rows > 0) {
                while ($user = $users_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>
                            <a href='edit_user.php?id=" . $user['id'] . "' class='action-link'>Editar</a> |
                            <a href='admin_panel.php?delete_user=" . $user['id'] . "' class='action-link' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No hay usuarios disponibles.</td></tr>";
            } ?>
        </table>

        <!-- Añadir nuevo usuario -->
        <h2>Añadir Nuevo Usuario</h2>
        <form action="admin_panel.php" method="POST">
            <input type="hidden" name="action" value="add_user">
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <button type="submit">Añadir Usuario</button>
        </form>

        <!-- Solicitudes de Vendedor -->
        <h2>Solicitudes de Vendedor</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Gmail</th>
                <th>Teléfono</th>
                <th>Ubicación Kiosko</th>
                <th>Status</th>
                <th>Acciones</th>
            </tr>
            <?php if ($applications_result->num_rows > 0) {
                while ($application = $applications_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($application['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($application['nombre_completo']) . "</td>";
                    echo "<td>" . htmlspecialchars($application['gmail']) . "</td>";
                    echo "<td>" . htmlspecialchars($application['telefono']) . "</td>";
                    echo "<td>" . htmlspecialchars($application['ubicacion_kiosko']) . "</td>";
                    echo "<td>" . htmlspecialchars($application['status']) . "</td>";
                    echo "<td>";
                    
                    if ($application['status'] === 'PENDIENTE') {
                        echo "<form action='admin_panel.php' method='POST' style='display:inline-block;'>
                                <input type='hidden' name='application_id' value='" . $application['id'] . "'>
                                <input type='hidden' name='action' value='accept_application'>
                                <button type='submit'>Aceptar</button>
                              </form>
                              <form action='admin_panel.php' method='POST' style='display:inline-block; margin-left:5px;'>
                                <input type='hidden' name='application_id' value='" . $application['id'] . "'>
                                <input type='hidden' name='action' value='deny_application'>
                                <button type='submit'>Denegar</button>
                              </form>";
                    } else {
                        echo "-";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay solicitudes de vendedor disponibles.</td></tr>";
            } ?>
        </table>

        <!-- Staffs -->
        <h2>Staffs</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
            <?php if ($staffs_result->num_rows > 0) {
                while ($staff = $staffs_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($staff['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($staff['email']) . "</td>";
                    echo "<td>
                            <a href='edit_staff.php?id=" . $staff['id'] . "' class='action-link'>Editar</a> |
                            <a href='admin_panel.php?delete_staff=" . $staff['id'] . "' class='action-link' onclick='return confirm(\"¿Estás seguro de eliminar este staff?\")'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No hay staffs disponibles.</td></tr>";
            } ?>
        </table>

        <!-- Añadir nuevo staff -->
        <h2>Añadir Nuevo Staff</h2>
        <form action="admin_panel.php" method="POST">
            <input type="hidden" name="action" value="add_staff">
            <label for="staff_name">Nombre:</label>
            <input type="text" id="staff_name" name="staff_name" required>
            
            <label for="staff_email">Email:</label>
            <input type="email" id="staff_email" name="staff_email" required>
            
            <button type="submit">Añadir Staff</button>
        </form>
    </div>
    <div class="container">
        <h1>Lista de Vendedores</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vendor = $vendors->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vendor['id']); ?></td>
                        <td><?php echo htmlspecialchars($vendor['name']); ?></td>
                        <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h1>Añadir Nuevo Vendedor</h1>
        <div class="form-section">
        <form action="admin.php" method="POST">
    <input type="hidden" name="action" value="add_vendor">
    <label for="name">Nombre:</label><br>
    <input type="text" name="name" id="name" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" id="email" required><br><br>

    <label for="password">Contraseña:</label><br>
    <input type="password" name="password" id="password" required><br><br>

    <button type="submit">Añadir Vendedor</button>
</form>

    </div>
</body>
</html>
