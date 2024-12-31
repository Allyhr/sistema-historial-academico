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
    $nombre_carrera = $_POST['nombre_carrera'];
    $total_semestres = $_POST['total_semestres'];

    // Actualizar la carrera
    $stmt = $conn->prepare("UPDATE Carreras SET nombre_carrera = ?, total_semestres = ? WHERE id_carrera = ?");
    $stmt->bind_param("sii", $nombre_carrera, $total_semestres, $id_carrera);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Carrera actualizada correctamente."]);

        // Registrar actividad de actualización
        $modulo = "Gestión de Carreras";
        $descripcion = "Actualización de la carrera con ID: $id_carrera, nombre: $nombre_carrera";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar la carrera: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
