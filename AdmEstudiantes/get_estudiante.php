<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['idE'])) {
    $idE = $_GET['idE'];
    
    // Consulta detallada para obtener toda la información del estudiante
    $stmt = $conn->prepare("
        SELECT 
            ue.idE, 
            ue.matricula,
            ue.nombre,
            ue.apellido_paterno, 
            ue.apellido_materno, 
            ue.id_carrera, 
            ue.id_reticula,
            ue.promedio_acumulado,
            ue.creditos_totales,
            ue.materias_aprobadas,
            ue.materias_reprobadas,
            c.nombre_carrera,
            r.id_especialidad
        FROM 
            Usuario_Estudiante ue
        JOIN 
            Carreras c ON ue.id_carrera = c.id_carrera
        JOIN 
            Reticulas r ON ue.id_reticula = r.id_reticula
        WHERE 
            ue.idE = ?
    ");
    $stmt->bind_param("i", $idE);
    $stmt->execute();
    $result = $stmt->get_result();
    $estudiante = $result->fetch_assoc();
    
    if ($estudiante) {
        echo json_encode([
            "status" => "success",
            "data" => $estudiante
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Estudiante no encontrado"
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "ID de estudiante no proporcionado"
    ]);
}

$conn->close();
?>