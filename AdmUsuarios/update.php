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
    $idU_actualizar = $_POST['idU'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $contraseña = $_POST['contraseña'];
    $estatus = $_POST['estatus'];

    if (empty($idU_actualizar) || empty($nombre_usuario) || !isset($estatus)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit();
    }

    // Si se proporcionó una nueva contraseña, actualizarla
    if (!empty($contraseña)) {
        $hash_contraseña = password_hash($contraseña, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Usuario SET nombre_usuario = ?, contraseña = ?, estatus = ? WHERE idU = ?");
        $stmt->bind_param("ssii", $nombre_usuario, $hash_contraseña, $estatus, $idU_actualizar);
    } else {
        // Si no se proporcionó contraseña, actualizar solo el nombre y estatus
        $stmt = $conn->prepare("UPDATE Usuario SET nombre_usuario = ?, estatus = ? WHERE idU = ?");
        $stmt->bind_param("sii", $nombre_usuario, $estatus, $idU_actualizar);
    }
    
    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Usuario actualizado correctamente."]);

        $modulo = "Gestión de Usuarios";
        $descripcion = "Actualización del usuario: $nombre_usuario";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el usuario: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>