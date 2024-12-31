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
    $id_especialidad = $_POST['id_especialidad'];
    $id_materia = $_POST['id_materia'];
    $semestre = $_POST['semestre'];

    if (empty($id_reticula) || empty($id_especialidad) || empty($id_materia) || empty($semestre)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit();
    }

    // Verificar que no exista ya otra retícula con la misma especialidad y materia
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM Reticulas WHERE id_especialidad = ? AND id_materia = ? AND id_reticula != ?");
    $stmt_check->bind_param("iii", $id_especialidad, $id_materia, $id_reticula);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        echo json_encode(["status" => "error", "message" => "Esta materia ya existe en la retícula de esta especialidad."]);
        exit();
    }

    // Preparar la consulta SQL
    $stmt = $conn->prepare("UPDATE Reticulas SET id_especialidad = ?, id_materia = ?, semestre = ? WHERE id_reticula = ?");
    $stmt->bind_param("iiii", $id_especialidad, $id_materia, $semestre, $id_reticula);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Retícula actualizada correctamente."]);

        // Registrar actividad
        $modulo = "Gestión de Retículas";
        $descripcion = "Actualización de la retícula con ID: $id_reticula, especialidad: $id_especialidad, materia: $id_materia, semestre: $semestre";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

    } else {
        // Mostrar el error detallado
        echo json_encode(["status" => "error", "message" => "Error al actualizar la retícula: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
