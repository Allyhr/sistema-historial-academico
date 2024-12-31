<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['id_especialidad'])) {
    $id_especialidad = $_GET['id_especialidad'];
    $stmt = $conn->prepare("SELECT id_especialidad, nombre, id_carrera FROM Especialidades WHERE id_especialidad = ?");
    $stmt->bind_param("i", $id_especialidad);
    $stmt->execute();
    $result = $stmt->get_result();
    $especialidad = $result->fetch_assoc();
    
    echo json_encode($especialidad);
    
    $stmt->close();
}

$conn->close();
?>
