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

function redirect_back($message = '', $type = 'success') {
    if ($message != '') {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    switch ($action) {
        case 'add_user':
            $name = $conn->real_escape_string($_POST['name']);
            $email = $conn->real_escape_string($_POST['email']);
            
            $insert_user_query = "INSERT INTO clients (name, email) VALUES ('$name', '$email')";
            if ($conn->query($insert_user_query) === TRUE) {
                redirect_back("Nuevo usuario añadido con éxito.");
            } else {
                redirect_back("Error al añadir usuario: " . $conn->error, 'error');
            }
            break;

        case 'edit_user':
            $id = $conn->real_escape_string($_POST['id']);
            $name = $conn->real_escape_string($_POST['name']);
            $email = $conn->real_escape_string($_POST['email']);
            
            $update_user_query = "UPDATE clients SET name='$name', email='$email' WHERE id='$id'";
            if ($conn->query($update_user_query) === TRUE) {
                redirect_back("Usuario actualizado con éxito.");
            } else {
                redirect_back("Error al actualizar usuario: " . $conn->error, 'error');
            }
            break;

        case 'accept_application':
            $application_id = $conn->real_escape_string($_POST['application_id']);
            
            $update_application_query = "UPDATE solicitudes_vendedores SET status='ACEPTADO' WHERE id='$application_id'";
            if ($conn->query($update_application_query) === TRUE) {
                redirect_back("Solicitud aceptada con éxito.");
            } else {
                redirect_back("Error al aceptar solicitud: " . $conn->error, 'error');
            }
            break;

        case 'deny_application':
            $application_id = $conn->real_escape_string($_POST['application_id']);
            
            $update_application_query = "UPDATE solicitudes_vendedores SET status='DENEGADO' WHERE id='$application_id'";
            if ($conn->query($update_application_query) === TRUE) {
                redirect_back("Solicitud denegada con éxito.");
            } else {
                redirect_back("Error al denegar solicitud: " . $conn->error, 'error');
            }
            break;

        case 'add_staff':
            $staff_name = $conn->real_escape_string($_POST['staff_name']);
            $staff_email = $conn->real_escape_string($_POST['staff_email']);
            
            $insert_staff_query = "INSERT INTO staffs (name, email) VALUES ('$staff_name', '$staff_email')";
            if ($conn->query($insert_staff_query) === TRUE) {
                redirect_back("Nuevo staff añadido con éxito.");
            } else {
                redirect_back("Error al añadir staff: " . $conn->error, 'error');
            }
            break;

        default:
            redirect_back("Acción no reconocida.", 'error');
            break;
    }
}

// Manejar acciones de eliminación vía GET (reseñas, staffs)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['delete_review'])) {
        $id = $conn->real_escape_string($_GET['delete_review']);
        
        $delete_review_query = "DELETE FROM reviews WHERE id='$id'";
        if ($conn->query($delete_review_query) === TRUE) {
            redirect_back("Reseña eliminada con éxito.");
        } else {
            redirect_back("Error al eliminar reseña: " . $conn->error, 'error');
        }
    }

    if (isset($_GET['delete_user'])) {
        $id = $conn->real_escape_string($_GET['delete_user']);
        
        $delete_user_query = "DELETE FROM clients WHERE id='$id'";
        if ($conn->query($delete_user_query) === TRUE) {
            redirect_back("Usuario eliminado con éxito.");
        } else {
            redirect_back("Error al eliminar usuario: " . $conn->error, 'error');
        }
    }

    if (isset($_GET['delete_staff'])) {
        $id = $conn->real_escape_string($_GET['delete_staff']);
        
        $delete_staff_query = "DELETE FROM staffs WHERE id='$id'";
        if ($conn->query($delete_staff_query) === TRUE) {
            redirect_back("Staff eliminado con éxito.");
        } else {
            redirect_back("Error al eliminar staff: " . $conn->error, 'error');
        }
    }
}

$conn->close();
?>
