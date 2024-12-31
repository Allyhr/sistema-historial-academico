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

// Obtener el idU del usuario logueado para el registro de actividad
$usuario = $_SESSION['username'];
$sql_user = "SELECT idU FROM Usuario WHERE nombre_usuario = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $usuario);
$stmt_user->execute();
$stmt_user->bind_result($idU_creador);
$stmt_user->fetch();
$stmt_user->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $nombre_usuario = $_POST['nombre_usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Encriptamos la contraseña
    $idP = $_POST['idP'];
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : 1;

    // Verificar si el nombre de usuario ya existe
    $check_stmt = $conn->prepare("SELECT nombre_usuario FROM Usuario WHERE nombre_usuario = ?");
    $check_stmt->bind_param("s", $nombre_usuario);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "El nombre de usuario ya existe"
        ]);
        exit();
    }
    $check_stmt->close();

    // Preparar la consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Usuario (nombre_usuario, contraseña, idP, estatus) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $nombre_usuario, $contraseña, $idP, $estatus);
    
    if ($stmt->execute()) {
        // Obtener el ID del usuario recién creado
        $nuevo_usuario_id = $stmt->insert_id;
        
        // Registrar la actividad
        $modulo = "Administración de Usuarios";
        $descripcion = "Creación de nuevo usuario: " . $nombre_usuario;
        $estado = "Exitoso";
        registrar_actividad($idU_creador, $modulo, $descripcion, $estado, $conn);
        
        echo json_encode([
            "status" => "success",
            "message" => "Usuario creado exitosamente",
            "idU" => $nuevo_usuario_id
        ]);
    } else {
        // Registrar el error
        $modulo = "Administración de Usuarios";
        $descripcion = "Error al crear usuario: " . $nombre_usuario;
        $estado = "Error";
        registrar_actividad($idU_creador, $modulo, $descripcion, $estado, $conn);
        
        echo json_encode([
            "status" => "error",
            "message" => "Error al crear el usuario: " . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ]);
}

$conn->close();
?>