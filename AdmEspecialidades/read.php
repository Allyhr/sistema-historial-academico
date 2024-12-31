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

// Registrar actividad de entrada a "Administrar Especialidades"
$modulo = "Gestión de Especialidades";
$descripcion = "Entrada a la sección de administración de especialidades";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Consulta que une Especialidades con Carreras para obtener el nombre de la carrera
$sql = "SELECT Especialidades.id_especialidad, Especialidades.nombre, Carreras.nombre_carrera 
        FROM Especialidades 
        INNER JOIN Carreras ON Especialidades.id_carrera = Carreras.id_carrera";

$result = $conn->query($sql);

$especialidades = [];
while ($row = $result->fetch_assoc()) {
    $especialidades[] = $row;
}

// Establecer el encabezado JSON y devolver las especialidades en formato JSON
header('Content-Type: application/json');
echo json_encode($especialidades);

$conn->close();
?>
