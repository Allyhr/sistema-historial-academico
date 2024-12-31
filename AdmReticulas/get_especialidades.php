<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

$sql = "SELECT id_especialidad, nombre FROM Especialidades";
$result = $conn->query($sql);

$especialidades = [];
while ($row = $result->fetch_assoc()) {
    $especialidades[] = $row;
}

echo json_encode($especialidades);

$conn->close();
?>
