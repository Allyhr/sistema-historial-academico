<?php
header('Content-Type: application/json');
require_once '../config/connectdb.php';

try {
    // Verificar si se recibió el ID de la carrera
    if (!isset($_GET['id_carrera'])) {
        throw new Exception("No se proporcionó el ID de la carrera");
    }

    $id_carrera = intval($_GET['id_carrera']);

    // Crear instancia de conexión
    $db = new DB_Connect();
    $conexion = $db->connect();

    // Consulta para obtener las retículas con la especialidad
    $query = "SELECT DISTINCT r.id_reticula, e.nombre AS nombre_especialidad 
              FROM Reticulas r
              JOIN Especialidades e ON r.id_especialidad = e.id_especialidad
              WHERE e.id_carrera = ?";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_carrera);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // Verificar si hay resultados
    if (mysqli_num_rows($resultado) > 0) {
        $reticulasDatos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $reticulasDatos[] = $fila;
        }

        // Devolver los datos como JSON
        echo json_encode($reticulasDatos);
    } else {
        // No se encontraron retículas para la carrera
        echo json_encode([]);
    }

    // Liberar recursos
    mysqli_stmt_close($stmt);
    $db->close();

} catch (Exception $e) {
    // Manejar cualquier error
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>