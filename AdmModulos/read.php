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

// Registrar actividad de entrada a "Administrar Módulos"
$modulo = "Gestión de Módulos";
$descripcion = "Entrada a la sección de administración de módulos";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Obtener lista de módulos
$sql = "SELECT idM, nombre, estatus FROM Modulo";
$result = $conn->query($sql);

$modulos = [];
while ($row = $result->fetch_assoc()) {
    $modulos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($modulos);

$conn->close();
?>
