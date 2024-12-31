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
    $idP = $_POST['idP'];
    $nombre = $_POST['nombre'];
    $estatus = $_POST['estatus'];

    // Preparar y ejecutar la consulta de actualización
    $stmt = $conn->prepare("UPDATE Perfil SET nombre = ?, estatus = ? WHERE idP = ?");
    $stmt->bind_param("sii", $nombre, $estatus, $idP); 

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Perfil actualizado correctamente."]);

        // Registrar actividad de actualización
        $modulo = "Gestión de Perfiles";
        $descripcion = "Actualización del perfil: $nombre";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el perfil: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
