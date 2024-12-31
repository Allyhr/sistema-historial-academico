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

// Registrar actividad de acceso a "Perfiles"
$modulo = "Administración de Perfiles";
$descripcion = "Acceso a la sección de visualización de perfiles";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Obtener lista de perfiles
$sql = "SELECT idP, nombre, estatus FROM Perfil";
$result = $conn->query($sql);

$perfiles = [];
while ($row = $result->fetch_assoc()) {
    $perfiles[] = $row;
}

// Establecer el encabezado JSON y devolver los perfiles en formato JSON
header('Content-Type: application/json');
echo json_encode($perfiles);

$conn->close();
?>
