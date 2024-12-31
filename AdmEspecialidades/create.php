<?php
require_once '../config/connectdb.php';
include '../AdmActividades/registrar_actividad.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado."]);
    exit();
}

$db = new DB_Connect();
$conn = $db->connect();

// Obtener el idU del usuario logueado
$usuario = $_SESSION['username'];
$sql_user = "SELECT idU FROM Usuario WHERE nombre_usuario = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $usuario);
$stmt_user->execute();
$stmt_user->bind_result($idU);
$stmt_user->fetch();
$stmt_user->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $id_carrera = $_POST['id_carrera'];

    // Consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Especialidades (nombre, id_carrera) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $id_carrera);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Especialidad creada correctamente."]);

        // Registrar actividad de creación
        $modulo = "Gestión de Especialidades";
        $descripcion = "Creación de la especialidad: $nombre, asignada a la carrera con ID: $id_carrera";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear la especialidad: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>


