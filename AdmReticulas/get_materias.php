<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

$sql = "SELECT id_materia, nombre_materia FROM Materias";
$result = $conn->query($sql);

$materias = [];
while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}

echo json_encode($materias);

$conn->close();
?>
