<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

$sql = "SELECT id_carrera, nombre_carrera FROM Carreras";
$result = $conn->query($sql);

$carreras = [];
while ($row = $result->fetch_assoc()) {
    $carreras[] = $row;
}

echo json_encode($carreras);

$conn->close();
?>