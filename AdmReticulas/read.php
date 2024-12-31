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

// Registrar actividad de entrada a "Administrar Retículas"
$modulo = "Gestión de Retículas";
$descripcion = "Entrada a la sección de administración de retículas";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Consulta que une Reticulas con Especialidades y Materias
$sql = "SELECT 
            Reticulas.id_reticula, 
            Especialidades.id_especialidad, 
            Especialidades.nombre AS nombre_especialidad, 
            Materias.id_materia, 
            Materias.nombre_materia, 
            Reticulas.semestre 
        FROM Reticulas 
        INNER JOIN Especialidades ON Reticulas.id_especialidad = Especialidades.id_especialidad 
        INNER JOIN Materias ON Reticulas.id_materia = Materias.id_materia";

$result = $conn->query($sql);

$reticulas = [];
while ($row = $result->fetch_assoc()) {
    $reticulas[] = $row;
}

// Establecer el encabezado JSON y devolver las retículas en formato JSON
header('Content-Type: application/json');
echo json_encode($reticulas);

$conn->close();
?>