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

// Registrar actividad de entrada a "Administrar Carreras"
$modulo = "Gestión de Carreras";
$descripcion = "Entrada a la sección de administración de carreras";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Obtener lista de carreras
$sql = "SELECT id_carrera, nombre_carrera, total_semestres FROM Carreras";
$result = $conn->query($sql);

$carreras = [];
while ($row = $result->fetch_assoc()) {
    $carreras[] = $row;
}

// Establecer el encabezado JSON y devolver las carreras en formato JSON
header('Content-Type: application/json');
echo json_encode($carreras);

$conn->close();
?>
