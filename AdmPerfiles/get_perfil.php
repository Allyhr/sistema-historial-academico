<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['idP'])) {
    $idP = $_GET['idP'];
    $stmt = $conn->prepare("SELECT idP, nombre, estatus FROM Perfil WHERE idP = ?");
    $stmt->bind_param("i", $idP);
    $stmt->execute();
    $result = $stmt->get_result();
    $perfil = $result->fetch_assoc();
    
    echo json_encode($perfil);
    
    $stmt->close();
}

$conn->close();
?>