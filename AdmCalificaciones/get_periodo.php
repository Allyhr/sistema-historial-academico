<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

// Consulta para obtener todos los periodos
$stmt = $conn->prepare("SELECT id_periodo, periodo FROM Periodo");
$stmt->execute();
$result = $stmt->get_result();

$periodos = [];
while ($row = $result->fetch_assoc()) {
    $periodos[] = $row;
}

echo json_encode($periodos);

$stmt->close();
$conn->close();
?>