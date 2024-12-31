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

    // Primero obtener la matrícula para el registro de actividad
    $stmt_matricula = $conn->prepare("SELECT matricula FROM Usuario_Estudiante WHERE idE = ?");
    $stmt_matricula->bind_param("i", $idE);
    $stmt_matricula->execute();
    $stmt_matricula->bind_result($matricula);
    $stmt_matricula->fetch();
    $stmt_matricula->close();

    // Preparar la consulta de eliminación
    $stmt = $conn->prepare("DELETE FROM Usuario_Estudiante WHERE idE = ?");
    $stmt->bind_param("i", $idE);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success", 
            "message" => "Estudiante eliminado correctamente."
        ]);

        // Registrar actividad de eliminación
        $modulo = "Gestión de Estudiantes";
        $descripcion = "Eliminación de estudiante con matrícula: $matricula";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Error al eliminar estudiante: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>