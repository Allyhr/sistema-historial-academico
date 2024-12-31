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
    // Datos del estudiante
    $matricula = $_POST['matricula'];
    $contraseña = $_POST['contraseña'];
    $contraseña_encriptada = password_hash($contraseña, PASSWORD_BCRYPT);
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $id_carrera = $_POST['id_carrera'];
    $id_reticula = $_POST['id_reticula'];

    // Preparar la consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Usuario_Estudiante 
        (matricula, contraseña, nombre, apellido_paterno, apellido_materno, 
        id_carrera, id_reticula) 
        VALUES (?, ?, ?,?, ?, ?, ?)");
    $stmt->bind_param("sssssii", 
        $matricula, $contraseña_encriptada, $nombre, $apellido_paterno, 
        $apellido_materno, $id_carrera, $id_reticula);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        $id_estudiante = $conn->insert_id;
        echo json_encode([
            "status" => "success", 
            "message" => "Estudiante creado correctamente.",
            "id_estudiante" => $id_estudiante
        ]);

        // Registrar actividad de creación
        $modulo = "Gestión de Estudiantes";
        $descripcion = "Creación de estudiante con matrícula: $matricula";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Error al crear estudiante: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>