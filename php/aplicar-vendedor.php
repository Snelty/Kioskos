<?php 

$conn = mysqli_connect("localhost", "root", "", "kioskos");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$nombre_completo = $_POST['nombre-completo'];
$gmail = $_POST['gmail'];
$telefono = $_POST['telefono'];
$ubicacion_kiosko = $_POST['ubicacion-kiosko'];

$sql = "INSERT INTO solicitudes_vendedores (nombre_completo, gmail, telefono, ubicacion_kiosko) VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $nombre_completo, $gmail, $telefono, $ubicacion_kiosko);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>
            alert('Gracias por aplicar! Hemos recibido tu solicitud. Serás redirigido al menú principal.');
            window.location.href = '../index.html';
          </script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
