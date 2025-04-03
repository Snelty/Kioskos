<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kioskos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';

    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "Por favor, completa todos los campos.";
    } elseif ($password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
    } else {

        $stmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "Este correo electrónico ya está registrado.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO clients (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $email, $hashed_password);
            
            if ($stmt->execute()) {
                echo "<script>
                        alert('Registro exitoso. Ahora puedes iniciar sesión.');
                        window.location.href = '../html/iniciarsesion.html';
                      </script>";
            } else {
                echo "Error al registrar: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
