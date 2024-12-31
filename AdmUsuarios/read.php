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

// Registrar actividad de acceso a "Usuarios"
$modulo = "Administración de Usuarios";
$descripcion = "Acceso a la sección de visualización de usuarios";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Obtener lista de usuarios con sus perfiles
$sql = "SELECT u.idU, u.nombre_usuario, u.estatus, p.nombre as nombre_perfil 
        FROM Usuario u 
        LEFT JOIN Perfil p ON u.idP = p.idP 
        ORDER BY u.idU";
$result = $conn->query($sql);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

// Establecer el encabezado JSON y devolver los usuarios en formato JSON
header('Content-Type: application/json');
echo json_encode($usuarios);

$conn->close();
?>