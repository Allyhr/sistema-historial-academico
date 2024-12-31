<?php
require_once '../config/connectdb.php';
include '../AdmActividades/registrar_actividad.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error", 
        "message" => "Usuario no autenticado."
    ]);
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
$stmt_user->bind_result($idU_editor);
$stmt_user->fetch();
$stmt_user->close();

if (isset($_GET['idU'])) {
    $idU = $_GET['idU'];
    
    // Preparar la consulta para obtener los datos del usuario
    $stmt = $conn->prepare("
        SELECT u.idU, u.nombre_usuario, u.idP, u.estatus
        FROM Usuario u 
        WHERE u.idU = ?
    ");
    
    $stmt->bind_param("i", $idU);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($usuario = $result->fetch_assoc()) {
        // Registrar la actividad de consulta
        $modulo = "Administración de Usuarios";
        $descripcion = "Consulta de datos del usuario ID: " . $idU;
        $estado = "Consulta";
        registrar_actividad($idU_editor, $modulo, $descripcion, $estado, $conn);
        
        // Devolver respuesta exitosa
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "data" => $usuario
        ]);
    } else {
        // Registrar el error
        $modulo = "Administración de Usuarios";
        $descripcion = "Error al consultar usuario ID: " . $idU;
        $estado = "Error";
        registrar_actividad($idU_editor, $modulo, $descripcion, $estado, $conn);
        
        // Devolver respuesta de error
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no encontrado"
        ]);
    }
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "ID de usuario no proporcionado"
    ]);
}

$conn->close();
?>