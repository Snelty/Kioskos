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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kiosko_id = $_POST['kiosko_id'];

    // Verificar si se ha subido un archivo
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['nueva_imagen']['tmp_name'];
        $file_name = $_FILES['nueva_imagen']['name'];
        $file_size = $_FILES['nueva_imagen']['size'];
        $file_type = $_FILES['nueva_imagen']['type'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Directorio donde se guardarán las imágenes
        $upload_dir = '../uploads/kioskos/';
        $new_file_name = uniqid() . '.' . $file_extension;  // Nombre único para evitar conflictos
        $dest_path = $upload_dir . $new_file_name;

        // Verificar si el archivo es una imagen válida
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_extensions)) {
            // Mover el archivo a la ubicación final
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                // Actualizar la URL de la imagen en la base de datos
                $image_url = $dest_path;  // Ruta a la imagen subida
                
                $stmt = $conn->prepare("UPDATE kioskos SET image_url = ? WHERE id = ?");
                $stmt->bind_param("si", $image_url, $kiosko_id);
                if ($stmt->execute()) {
                    // Redirigir de vuelta a la página de cuenta después de la actualización exitosa
                    header("Location: mi-cuenta.php");
                    exit();
                } else {
                    echo "Error al actualizar la imagen en la base de datos.";
                }
            } else {
                echo "Error al mover el archivo a la carpeta de destino.";
            }
        } else {
            echo "Formato de archivo no permitido. Solo se permiten archivos JPG, JPEG, PNG y GIF.";
        }
    } else {
        echo "No se ha subido ninguna imagen o ha ocurrido un error al subirla.";
    }
}

$conn->close();
?>
