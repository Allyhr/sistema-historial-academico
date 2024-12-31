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

// Registrar actividad de entrada a "Administrar Estudiantes"
$modulo = "Gestión de Estudiantes";
$descripcion = "Entrada a la sección de administración de estudiantes";
$estado = "Acceso";
registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

// Consulta para obtener la lista de estudiantes con detalles de carrera
$sql = "SELECT 
            ue.idE, 
            ue.matricula, 
            ue.nombre, 
            ue.apellido_paterno, 
            ue.apellido_materno, 
            ue.promedio_acumulado, 
            ue.creditos_totales,
            c.nombre_carrera
        FROM 
            Usuario_Estudiante ue
        JOIN 
            Carreras c ON ue.id_carrera = c.id_carrera";
$result = $conn->query($sql);

$estudiantes = [];
while ($row = $result->fetch_assoc()) {
    $estudiantes[] = $row;
}

// Establecer el encabezado JSON y devolver los estudiantes
header('Content-Type: application/json');
echo json_encode($estudiantes);

$conn->close();
?>