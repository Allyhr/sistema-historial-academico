<?php 
session_start();
require('fpdf/fpdf.php');
require_once '../config/connectdb.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 3) {
    header("Location: ../index.html");
    exit();
}

$db = new DB_Connect();
$conn = $db->connect();

$id_estudiante = $_SESSION['idU'];

// Consultar los datos del estudiante
$consulta_estudiante = "SELECT nombre, apellido_paterno, apellido_materno, matricula, c.nombre_carrera
FROM Usuario_Estudiante ue
JOIN Carreras c ON ue.id_carrera = c.id_carrera
WHERE ue.idE = ?";
$stmt = $conn->prepare($consulta_estudiante);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$estudiante = $stmt->get_result()->fetch_assoc();

// Consultar las materias
$consulta_materias = "SELECT m.nombre_materia, m.clave_materia, m.creditos, c.calificacion, 
COALESCE(p.periodo, 'Sin período') AS periodo
FROM Calificaciones c
JOIN Materias m ON c.id_materia = m.id_materia
LEFT JOIN Periodo p ON c.id_periodo = p.id_periodo
WHERE c.id_estudiante = ?
ORDER BY p.periodo, m.nombre_materia";
$stmt = $conn->prepare($consulta_materias);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$materias = $stmt->get_result();

function calcularMetricasAcademicas($conexion, $id_estudiante) {
    $consulta = "
        SELECT 
            COUNT(CASE WHEN c.calificacion >= 7.0 THEN 1 ELSE NULL END) AS materias_aprobadas,
            COUNT(CASE WHEN c.calificacion < 7.0 THEN 1 ELSE NULL END) AS materias_reprobadas,
            COALESCE(SUM(CASE WHEN c.calificacion >= 7.0 THEN m.creditos ELSE 0 END), 0) AS creditos_totales,
            COALESCE(ROUND(AVG(CASE WHEN c.calificacion >= 7.0 THEN c.calificacion ELSE NULL END), 2), 0.00) AS promedio_acumulado
        FROM Calificaciones c
        JOIN Materias m ON c.id_materia = m.id_materia
        WHERE c.id_estudiante = ?";
    
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    
    return [
        'materias_aprobadas' => $resultado['materias_aprobadas'] ?? 0,
        'materias_reprobadas' => $resultado['materias_reprobadas'] ?? 0,
        'creditos_totales' => $resultado['creditos_totales'] ?? 0,
        'promedio_acumulado' => $resultado['promedio_acumulado'] ?? 0.00,
    ];
}

$metricas = calcularMetricasAcademicas($conn, $id_estudiante);

class PDF extends FPDF {
    function Header() {
        // Agregar imagen como marca de agua
        $this->Image('imagen1.jpeg', 10, 10, 50); // Ajusta la ruta y dimensiones
        
        // Configura la transparencia para la imagen
        $this->SetAlpha(0.05);
        
        // Restaura la opacidad para el resto del contenido
        $this->SetAlpha(1);
    }

    function SetAlpha($alpha) {
        $this->_out(sprintf('q %.2F gs', $alpha));
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(0, 10, utf8_decode('Historial Académico'), 0, 1, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 10, utf8_decode('Nombre: ' . $estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Matrícula: ' . $estudiante['matricula']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Carrera: ' . $estudiante['nombre_carrera']), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 10, utf8_decode('Período'), 1);
$pdf->Cell(30, 10, utf8_decode('Clave'), 1);
$pdf->Cell(80, 10, utf8_decode('Materia'), 1);
$pdf->Cell(20, 10, utf8_decode('Créditos'), 1);
$pdf->Cell(30, 10, utf8_decode('Calificación'), 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while ($materia = $materias->fetch_assoc()) {
    $pdf->Cell(30, 10, utf8_decode($materia['periodo']), 1);
    $pdf->Cell(30, 10, utf8_decode($materia['clave_materia']), 1);
    $pdf->Cell(80, 10, utf8_decode($materia['nombre_materia']), 1);
    $pdf->Cell(20, 10, $materia['creditos'], 1);
    $pdf->Cell(30, 10, $materia['calificacion'], 1);
    $pdf->Ln();
}

// Agregar resumen académico
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Resumen Académico'), 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Promedio Acumulado: ' . number_format($metricas['promedio_acumulado'], 2)), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Créditos Totales: ' . $metricas['creditos_totales']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Materias Aprobadas: ' . $metricas['materias_aprobadas']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Materias Reprobadas: ' . $metricas['materias_reprobadas']), 0, 1);

$pdf->Output();
?>
