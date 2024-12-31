<?php
require_once '../config/connectdb.php';
include '../AdmActividades/registrar_actividad.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado."]);
    exit();
}

$db = new DB_Connect();
$conn = $db->connect();

// Obtener el idU del usuario logueado
$usuario = $_SESSION['username'];
$sql_user = "SELECT idU FROM Usuario WHERE nombre_usuario = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $usuario);
$stmt_user->execute();
$stmt_user->bind_result($idU);
$stmt_user->fetch();
$stmt_user->close();

if (!$idU) {
    echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de usuario."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idU_eliminar = $_POST['idU'];

    if (empty($idU_eliminar)) {
        echo json_encode(["status" => "error", "message" => "ID de usuario vacío."]);
        exit();
    }

    // Consulta de eliminación
    $stmt = $conn->prepare("DELETE FROM Usuario WHERE idU = ?");
    $stmt->bind_param("i", $idU_eliminar);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Usuario eliminado correctamente."]);

        // Registrar actividad de eliminación
        $modulo = "Gestión de Usuarios";
        $descripcion = "Eliminación del usuario con ID: $idU_eliminar";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar el usuario: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
