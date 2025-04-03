<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staffs') {
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

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    $user_query = "SELECT * FROM clients WHERE id='$id'";
    $user_result = $conn->query($user_query);
    
    if ($user_result->num_rows === 1) {
        $user = $user_result->fetch_assoc();
    } else {

        $_SESSION['message'] = "Usuario no encontrado.";
        $_SESSION['message_type'] = "error";
        header("Location: admin.php");
        exit();
    }
} else {

    $_SESSION['message'] = "ID de usuario no proporcionado.";
    $_SESSION['message_type'] = "error";
    header("Location: admin.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
    
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #50b3a2;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #3d8b7d;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Editar Usuario</h2>
        <form action="admin_panel.php" method="POST">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
            
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <button type="submit">Actualizar Usuario</button>
        </form>
    </div>
</body>
</html>
