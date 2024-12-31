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
    $id_materia = $_POST['id_materia'];
    $nombre_materia = $_POST['nombre_materia'];
    $clave_materia = $_POST['clave_materia'];
    $creditos = $_POST['creditos'];

    if (empty($id_materia) || empty($nombre_materia) || empty($clave_materia) || empty($creditos)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE Materias SET nombre_materia = ?, clave_materia = ?, creditos = ? WHERE id_materia = ?");
    $stmt->bind_param("ssii", $nombre_materia, $clave_materia, $creditos, $id_materia);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Materia actualizada correctamente."]);

        // Registrar actividad
        $modulo = "Gestión de Materias";
        $descripcion = "Actualización de la materia: $nombre_materia, clave: $clave_materia, créditos: $creditos.";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar la materia: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
