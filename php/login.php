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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        echo "Por favor, completa todos los campos.";
    } else {
        // Intentar iniciar sesión como cliente
        $stmt = $conn->prepare("SELECT id, name, password FROM clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_type'] = 'client';
                header("Location: ../php/mi-cuenta.php");
                exit();
            }
        }
        $stmt->close();

        // Intentar iniciar sesión como kiosko_owner
        $stmt = $conn->prepare("SELECT id, name, password FROM kiosko_owners WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_type'] = 'kiosko_owners';
                header("Location: ../php/mi-cuenta.php");
                exit();
            }
        }
        $stmt->close();

        $stmt = $conn->prepare("SELECT id, name, password FROM staffs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_type'] = 'staffs';
                header("Location: ../php/mi-cuenta.php");
                exit();
            }
        }
        $stmt->close();

        echo "Correo electrónico o contraseña incorrectos.";
    }
}

$conn->close();
?>
