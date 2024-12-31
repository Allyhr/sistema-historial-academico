<?php
session_start();
require('fpdf/fpdf.php');
require_once '../config/connectdb.php';

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 3) {
    header("Location: ../index.html");
    exit();
}

// Conectar a la base de datos
$db = new DB_Connect();
$conn = $db->connect();

// Obtener el ID del estudiante
$id_estudiante = $_SESSION['user_id'];

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

// Clase personalizada de FPDF con marca de agua
class PDF extends FPDF {
    function Header() {
        // Configuración de la marca de agua con imagen
        $this->SetAlpha(0.2); // Establece la transparencia (0.2 = 20% opaco)
        
        // Calcula el centro de la página
        $pageWidth = $this->GetPageWidth();
        $pageHeight = $this->GetPageHeight();
        
        // Asumiendo que la imagen es watermark.png y está en el mismo directorio
        $watermarkPath = 'imagen.png';
        
        // Obtiene las dimensiones de la imagen
        list($width, $height) = getimagesize($watermarkPath);
        
        // Calcula las coordenadas para centrar la imagen
        $x = ($pageWidth - $width) / 2;
        $y = ($pageHeight - $height) / 2;
        
        // Coloca la imagen como marca de agua
        $this->Image($watermarkPath, $x, $y, $width, $height);
        
        // Restaura la opacidad normal
        $this->SetAlpha(1);
    }

    // Método necesario para la transparencia
    function SetAlpha($alpha) {
        $this->_out(sprintf('q %.2F gs', $alpha));
    }

    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear el PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Datos del estudiante
$pdf->Cell(0, 10, 'Historial Académico', 0, 1, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Nombre: ' . $estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno'], 0, 1);
$pdf->Cell(0, 10, 'Matrícula: ' . $estudiante['matricula'], 0, 1);
$pdf->Cell(0, 10, 'Carrera: ' . $estudiante['nombre_carrera'], 0, 1);
$pdf->Ln(10);

// Tabla de materias
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 10, 'Período', 1);
$pdf->Cell(30, 10, 'Clave', 1);
$pdf->Cell(80, 10, 'Materia', 1);
$pdf->Cell(20, 10, 'Créditos', 1);
$pdf->Cell(30, 10, 'Calificación', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
while ($materia = $materias->fetch_assoc()) {
    $pdf->Cell(30, 10, $materia['periodo'], 1);
    $pdf->Cell(30, 10, $materia['clave_materia'], 1);
    $pdf->Cell(80, 10, $materia['nombre_materia'], 1);
    $pdf->Cell(20, 10, $materia['creditos'], 1);
    $pdf->Cell(30, 10, $materia['calificacion'], 1);
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output();
?>