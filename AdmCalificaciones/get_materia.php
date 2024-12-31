<?php
require_once '../config/connectdb.php';
$db = new DB_Connect();
$conn = $db->connect();

if (isset($_GET['id_materia'])) {
    $id_materia = $_GET['id_materia'];

    // Consulta para obtener información de la materia
    $stmt = $conn->prepare("SELECT id_materia, nombre_materia, clave_materia, creditos FROM Materias WHERE id_materia = ?");
    $stmt->bind_param("i", $id_materia);
    $stmt->execute();
    $result = $stmt->get_result();
    $materia = $result->fetch_assoc();

    if ($materia) {
        echo json_encode([
            "status" => "success",
            "data" => $materia
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Materia no encontrada"
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "ID de materia no proporcionado"
    ]);
}

$conn->close();
?>