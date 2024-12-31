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
    $idE = $_POST['idE'];
    $matricula = $_POST['matricula'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $id_carrera = $_POST['id_carrera'];
    $id_reticula = $_POST['id_reticula'];

    // Verificar si se proporcionó una nueva contraseña
    $updatePassword = false;
    if (!empty($_POST['contraseña'])) {
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
        $updatePassword = true;
    }

    // Preparar la consulta de actualización
    if ($updatePassword) {
        $stmt = $conn->prepare("UPDATE Usuario_Estudiante 
            SET matricula = ?, 
                contraseña = ?, 
                nombre = ?,
                apellido_paterno = ?, 
                apellido_materno = ?, 
                id_carrera = ?, 
                id_reticula = ? 
            WHERE idE = ?");
        $stmt->bind_param("sssssiii", 
            $matricula, $contraseña, $nombre,  $apellido_paterno, 
            $apellido_materno, $id_carrera, $id_reticula, $idE);
    } else {
        $stmt = $conn->prepare("UPDATE Usuario_Estudiante 
            SET matricula = ?, 
                nombre = ?, 
                apellido_paterno = ?, 
                apellido_materno = ?, 
                id_carrera = ?, 
                id_reticula = ? 
            WHERE idE = ?");
        $stmt->bind_param("sssssii", 
            $matricula, $nombre, $apellido_paterno, 
            $apellido_materno, $id_carrera, $id_reticula, $idE);
    }

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success", 
            "message" => "Estudiante actualizado correctamente."
        ]);

        // Registrar actividad de actualización
        $modulo = "Gestión de Estudiantes";
        $descripcion = "Actualización de estudiante con matrícula: $matricula";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Error al actualizar estudiante: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>