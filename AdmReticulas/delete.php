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
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de usuario."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_reticula = $_POST['id_reticula'];

    if (empty($id_reticula)) {
        echo json_encode(["status" => "error", "message" => "ID de retícula vacío."]);
        exit();
    }

    // Consulta de eliminación
    $stmt = $conn->prepare("DELETE FROM Reticulas WHERE id_reticula = ?");
    $stmt->bind_param("i", $id_reticula);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Retícula eliminada correctamente."]);

        // Registrar actividad de eliminación
        $modulo = "Gestión de Retículas";
        $descripcion = "Eliminación de la retícula con ID: $id_reticula";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        // Mostrar el error detallado
        echo json_encode(["status" => "error", "message" => "Error al eliminar la retícula: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>