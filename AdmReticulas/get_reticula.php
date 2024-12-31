<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['id_reticula'])) {
    $id_reticula = $_GET['id_reticula'];
    $stmt = $conn->prepare("SELECT * FROM Reticulas WHERE id_reticula = ?");
    $stmt->bind_param("i", $id_reticula);
    $stmt->execute();
    $result = $stmt->get_result();
    $reticula = $result->fetch_assoc();

    echo json_encode($reticula);
    $stmt->close();
}

$conn->close();
?>
