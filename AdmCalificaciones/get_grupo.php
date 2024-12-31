<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

// Consulta para obtener todos los grupos
$stmt = $conn->prepare("SELECT id_grupo, nombre_grupo FROM Grupo");
$stmt->execute();
$result = $stmt->get_result();

$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode($grupos);

$stmt->close();
$conn->close();
?>