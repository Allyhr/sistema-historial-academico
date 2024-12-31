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

// Tu consulta SQL
$sql = "SELECT 
    c.id_calificacion, 
    c.numero_acta, 
    c.calificacion, 
    c.id_grupo, 
    c.id_periodo,
    e.nombre AS nombre_estudiante, 
    e.apellido_paterno, 
    e.apellido_materno, 
    m.nombre_materia, 
    te.tipo AS nombre_tipo_evaluacion, 
    g.nombre_grupo, 
    p.periodo AS nombre_periodo 
FROM 
    Calificaciones c
JOIN 
    Usuario_Estudiante e ON c.id_estudiante = e.idE 
JOIN 
    Materias m ON c.id_materia = m.id_materia
JOIN 
    TipoEvaluacion te ON c.id_tipo_evaluacion = te.id_tipo_evaluacion
LEFT JOIN 
    Grupo g ON c.id_grupo = g.id_grupo
LEFT JOIN 
    Periodo p ON c.id_periodo = p.id_periodo";

// Ejecutar la consulta
$result = $conn->query($sql);

// Verificar errores de consulta
if (!$result) {
    // Imprimir error de MySQL
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error", 
        "message" => "Error en la consulta: " . $conn->error,
        "sql" => $sql
    ]);
    exit();
}

$calificaciones = [];

// Recuperar los datos
while ($row = $result->fetch_assoc()) {
    $calificaciones[] = $row;
}

// Verificar si hay datos
if (empty($calificaciones)) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error", 
        "message" => "No se encontraron calificaciones",
        "sql" => $sql
    ]);
    exit();
}

// Establecer encabezados para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permitir solicitudes de cualquier origen

// Imprimir los datos
echo json_encode($calificaciones);

$conn->close();
?>