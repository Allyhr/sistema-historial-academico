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

if (!$idU) {
    echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID de usuario."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $estatus = $_POST['estatus'];

    if (empty($nombre) || !isset($estatus)) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
        exit();
    }

    // Consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Modulo (nombre, estatus) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $estatus);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Módulo creado correctamente."]);

        // Registrar actividad de creación
        $modulo = "Gestión de Módulos";
        $descripcion = "Creación del módulo: $nombre con estatus: $estatus";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear el módulo: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
