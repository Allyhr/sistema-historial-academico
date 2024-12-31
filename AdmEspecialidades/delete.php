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
    $id_especialidad = $_POST['id_especialidad'];

    if (empty($id_especialidad)) {
        echo json_encode(["status" => "error", "message" => "ID de especialidad vacío."]);
        exit();
    }

    // Consulta de eliminación
    $stmt = $conn->prepare("DELETE FROM Especialidades WHERE id_especialidad = ?");
    $stmt->bind_param("i", $id_especialidad);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Especialidad eliminada correctamente."]);

        // Registrar actividad de eliminación
        $modulo = "Gestión de Especialidades";
        $descripcion = "Eliminación de la especialidad con ID: $id_especialidad";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        // Mostrar el error detallado
        echo json_encode(["status" => "error", "message" => "Error al eliminar la especialidad: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
