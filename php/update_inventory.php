<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'kiosko_owners') {
    echo "Acceso denegado";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kioskos";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $product_name = $_POST['product'];
    $new_quantity = (int)$_POST['quantity'];

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM kioskos WHERE owner_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $kiosko_result = $stmt->get_result();
    $kiosko = $kiosko_result->fetch_assoc();
    $kiosko_id = $kiosko['id'];

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

    $stmt->close();
    $conn->close();
} else {
    echo "Método de solicitud no permitido";
}
