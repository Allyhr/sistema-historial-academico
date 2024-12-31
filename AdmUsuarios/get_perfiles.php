<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

// Consulta para obtener los perfiles activos
$stmt = $conn->prepare("SELECT idP, nombre FROM Perfil WHERE estatus = 1 ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();

// Array para almacenar todos los perfiles
$perfiles = array();

// Obtener todos los perfiles
while($row = $result->fetch_assoc()) {
    $perfiles[] = $row;
}

// Enviar la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($perfiles);

// Cerrar la sentencia y la conexión
$stmt->close();
$conn->close();
?>