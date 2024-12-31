<?php
require_once '../config/connectdb.php';
include '../AdmActividades/registrar_actividad.php';

// Configurar informe de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establecer encabezados para manejar JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Verificación de sesión
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error", 
        "message" => "Usuario no autenticado."
    ]);
    exit();
}

// Conexión a la base de datos
$db = new DB_Connect();
$conn = $db->connect();

// Obtener el ID de usuario
$usuario = $_SESSION['username'];
$stmt_user = $conn->prepare("SELECT idU FROM Usuario WHERE nombre_usuario = ?");
$stmt_user->bind_param("s", $usuario);
$stmt_user->execute();
$stmt_user->bind_result($idU);
$stmt_user->fetch();
$stmt_user->close();

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error", 
        "message" => "Método no permitido."
    ]);
    exit();
}

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Validación de campos requeridos
$required_fields = [
    'id_calificacion', 'id_estudiante', 'id_materia', 'calificacion', 
    'id_tipo_evaluacion', 'numero_acta', 'id_grupo', 'id_periodo'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        http_response_code(400);
        echo json_encode([
            "status" => "error", 
            "message" => "Campo requerido faltante: $field"
        ]);
        exit();
    }
}

// Validaciones adicionales
if (!is_numeric($data['calificacion']) || 
    $data['calificacion'] < 0 || 
    $data['calificacion'] > 10) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Calificación inválida. Debe estar entre 0 y 10."
    ]);
    exit();
}

// Preparar la consulta de actualización
$stmt = $conn->prepare("
    UPDATE Calificaciones 
    SET 
        id_estudiante = ?, 
        id_materia = ?, 
        calificacion = ?, 
        id_tipo_evaluacion = ?, 
        numero_acta = ?, 
        id_grupo = ?, 
        id_periodo = ? 
    WHERE id_calificacion = ?
");

// Bindear parámetros
$stmt->bind_param(
    "iidsisii", 
    $data['id_estudiante'], 
    $data['id_materia'], 
    $data['calificacion'], 
    $data['id_tipo_evaluacion'], 
    $data['numero_acta'], // ahora como cadena
    $data['id_grupo'], 
    $data['id_periodo'], 
    $data['id_calificacion']
);

// Ejecutar actualización
try {
    $result = $stmt->execute();

    if ($result) {
        // Registro de actividad
        $modulo = "Gestión de Calificaciones";
        $descripcion = "Actualización de calificación para estudiante ID: " . $data['id_estudiante'];
        $estado = "Completado";
        
        // Intentar registrar actividad
        $registro_actividad = registrar_actividad($idU, $modulo, $descripcion, $estado, $conn);

        echo json_encode([
            "status" => "success", 
            "message" => "Calificación actualizada correctamente.",
            "activity_log" => $registro_actividad ? "Actividad registrada" : "Error al registrar actividad"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error", 
            "message" => "Error al actualizar la calificación: " . $stmt->error
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Excepción al actualizar: " . $e->getMessage()
    ]);
} finally {
    $stmt->close();
    $conn->close();
}
?>