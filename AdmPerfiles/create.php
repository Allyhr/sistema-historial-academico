<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $estatus = $_POST['estatus'];

    // Consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Perfil (nombre, estatus) VALUES (?, ?)");
    $stmt->bind_param("ssi", $nombre, $estatus);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Perfil creado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear el perfil."]);
    }

    $stmt->close();
    $conn->close();
}
?>