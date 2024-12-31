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
    $nombre = $_POST['nombre'];
    $id_carrera = $_POST['id_carrera'];

    if (empty($id_especialidad) || empty($nombre) || empty($id_carrera)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit();
    }

    // Preparar la consulta SQL
    $stmt = $conn->prepare("UPDATE Especialidades SET nombre = ?, id_carrera = ? WHERE id_especialidad = ?");
    $stmt->bind_param("sii", $nombre, $id_carrera, $id_especialidad);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Especialidad actualizada correctamente."]);

        // Registrar actividad
        $modulo = "Gestión de Especialidades";
        $descripcion = "Actualización de la especialidad: $nombre, en la carrera con ID: $id_carrera.";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

    } else {
        // Mostrar el error detallado
        echo json_encode(["status" => "error", "message" => "Error al actualizar la especialidad: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>


