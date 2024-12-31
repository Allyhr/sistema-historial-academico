<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 1) {
    header("Location: ../index.html");
    exit();
}

// Incluir el archivo de conexión
include '../config/connectdb.php';

// Crear una instancia de la clase de conexión y obtener la conexión
$db = new DB_Connect();
$conn = $db->connect();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Actividades</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-4">
    <h2>Gestión de Actividades</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Módulo</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para obtener las actividades con el nombre del usuario
            $sql = "SELECT u.nombre_usuario, a.modulo, a.descripcion, a.estado, a.fecha_hora
                    FROM actividades a
                    JOIN Usuario u ON a.idU = u.idU
                    ORDER BY a.fecha_hora DESC";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['nombre_usuario']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['modulo']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['fecha_hora']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5" class="text-center">No hay actividades registradas</td></tr>';
            }

            // Cerrar la conexión
            $db->close();
            ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

