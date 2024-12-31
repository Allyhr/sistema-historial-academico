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
    // Validación de datos
    $id_estudiante = $_POST['id_estudiante'];
    $id_materia = $_POST['id_materia'];
    $calificacion = $_POST['calificacion'];
    $id_tipo_evaluacion = $_POST['id_tipo_evaluacion'];
    $numero_acta = $_POST['numero_acta'];
    $id_grupo = $_POST['id_grupo'];
    $id_periodo = $_POST['id_periodo'];

    if (empty($id_estudiante) || empty($id_materia) || empty($calificacion) || empty($id_tipo_evaluacion) ||
        empty($numero_acta) || empty($id_grupo) || empty($id_periodo)) {
        echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios."]);
        exit();
    }

    // Preparar la consulta de inserción
    $stmt = $conn->prepare("INSERT INTO Calificaciones 
        (id_estudiante, id_materia, calificacion, id_tipo_evaluacion, numero_acta, id_grupo, id_periodo) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsisi", 
        $id_estudiante, $id_materia, $calificacion, $id_tipo_evaluacion, $numero_acta, $id_grupo, $id_periodo);

    header('Content-Type: application/json');
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Calificación registrada correctamente."]);
        // Registrar actividad
        $modulo = "Gestión de Calificaciones";
        $descripcion = "Registro de calificación para estudiante ID: $id_estudiante";
        $estado = "Completado";
        registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar calificación: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
