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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_carrera = $_POST['id_carrera'];

    $stmt = $conn->prepare("DELETE FROM Carreras WHERE id_carrera = ?");
    $stmt->bind_param("i", $id_carrera);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Carrera eliminada correctamente."]);

        // Registrar actividad de eliminación
        $modulo = "Gestión de Carreras";
        $descripcion = "Eliminación de la carrera con ID: $id_carrera";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar la carrera: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
