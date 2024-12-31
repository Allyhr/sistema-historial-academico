<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

// Consulta para obtener todos los tipos de evaluación
$stmt = $conn->prepare("SELECT id_tipo_evaluacion, tipo FROM TipoEvaluacion");
$stmt->execute();
$result = $stmt->get_result();

$tiposEvaluacion = [];
while ($row = $result->fetch_assoc()) {
    $tiposEvaluacion[] = $row;
}

echo json_encode($tiposEvaluacion);

$stmt->close();
$conn->close();
?>