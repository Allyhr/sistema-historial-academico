<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['idM'])) {
    $idM = $_GET['idM'];
    $stmt = $conn->prepare("SELECT idM, nombre, estatus FROM Modulo WHERE idM = ?");
    $stmt->bind_param("i", $idM);
    $stmt->execute();
    $result = $stmt->get_result();
    $modulo = $result->fetch_assoc();
    
    echo json_encode($modulo);
    
    $stmt->close();
}

$conn->close();
?>