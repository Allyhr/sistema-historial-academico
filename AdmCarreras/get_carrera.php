<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['id_carrera'])) {
    $id_carrera = $_GET['id_carrera'];
    $stmt = $conn->prepare("SELECT id_carrera, nombre_carrera, total_semestres FROM Carreras WHERE id_carrera = ?");
    $stmt->bind_param("i", $id_carrera);
    $stmt->execute();
    $result = $stmt->get_result();
    $carrera = $result->fetch_assoc();
    
    echo json_encode($carrera);
    
    $stmt->close();
}

$conn->close();
?>