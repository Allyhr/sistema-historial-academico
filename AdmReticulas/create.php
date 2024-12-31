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
    $id_especialidad = $_POST['id_especialidad'];
    $id_materia = $_POST['id_materia'];
    $semestre = $_POST['semestre'];

    // Verificar que no exista ya la retícula
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM Reticulas WHERE id_especialidad = ? AND id_materia = ?");
    $stmt_check->bind_param("ii", $id_especialidad, $id_materia);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Esta materia ya existe en la retícula de esta especialidad."]);
        exit();
    }

    // Consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Reticulas (id_especialidad, id_materia, semestre) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $id_especialidad, $id_materia, $semestre);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Retícula creada correctamente."]);

        // Registrar actividad de creación
        $modulo = "Gestión de Retículas";
        $descripcion = "Creación de retícula para especialidad con ID: $id_especialidad, materia con ID: $id_materia, semestre: $semestre";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear la retícula: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>

