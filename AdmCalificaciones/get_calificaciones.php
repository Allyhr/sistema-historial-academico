<?php
// Establecer encabezados para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Configuración de manejo de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connectdb.php';

$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['id_calificacion'])) {
    $id_calificacion = intval($_GET['id_calificacion']);

    // Consulta detallada con joins para obtener información completa
    $stmt = $conn->prepare("
        SELECT 
            c.id_calificacion, 
            c.id_estudiante, 
            c.id_materia, 
            c.calificacion, 
            c.id_tipo_evaluacion, 
            c.numero_acta, 
            c.id_grupo, 
            c.id_periodo,
            e.nombre AS nombre_estudiante,
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
            Periodo p ON c.id_periodo = p.id_periodo
        WHERE 
            c.id_calificacion = ?
    ");

    // Verificar errores en la preparación de la consulta
    if ($stmt === false) {
        echo json_encode([
            "status" => "error", 
            "message" => "Error en la preparación de la consulta: " . $conn->error
        ]);
        exit();
    }

    $stmt->bind_param("i", $id_calificacion);
    
    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verificar errores en la ejecución
    if ($result === false) {
        echo json_encode([
            "status" => "error", 
            "message" => "Error en la ejecución de la consulta: " . $stmt->error
        ]);
        exit();
    }

    $calificacion = $result->fetch_assoc();
    
    if ($calificacion) {
        echo json_encode([
            "status" => "success",
            "data" => $calificacion
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Calificación no encontrada"
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "ID de calificación no proporcionado"
    ]);
}

$conn->close();
?>