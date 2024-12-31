<?php
session_start();

// Encabezados para evitar el almacenamiento en caché de la página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el usuario ha iniciado sesión y tiene el rol adecuado (3 para Estudiantes)
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 3) {
    header("Location: ../index.html");
    exit();
}

// Verificar si la sesión ha expirado por inactividad (3 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 180)) {
    session_unset(); // Eliminar las variables de la sesión
    session_destroy(); // Destruir la sesión
    header("Location: ../index.html"); // Redirigir al inicio de sesión
    exit();
}

// Actualizar el tiempo de actividad de la sesión
$_SESSION['last_activity'] = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal del Estudiante</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
        }
        .sidebar a {
            color: white;
            padding: 10px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Menú Lateral -->
        <nav class="col-md-2 d-none d-md-block bg-dark sidebar">
            <div class="sidebar-sticky">
                <h4 class="text-center">Portal del Estudiante</h4>
                <p class="text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../AdmHistorial/historial.php">Ver Historial Academico</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <h1 class="h2 mt-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p>Selecciona una opción en el menú para ver tus materias, calificaciones o actualizar tus datos personales.</p>
            <a href="../login/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Script de navegación y sesión -->
<script>
// Detectar cambios en el historial de navegación
window.addEventListener('popstate', function () {
    // Cerrar sesión si se detecta navegación hacia atrás
    window.location.href = '../login/logout.php';
});

// Proteger la navegación hacia adelante sin sesión activa
document.addEventListener('DOMContentLoaded', function () {
    // Verificar si la sesión está activa al cargar la página
    if (!<?php echo json_encode(isset($_SESSION['logged_in']) && $_SESSION['logged_in']); ?>) {
        alert("Debe iniciar sesión para acceder.");
        window.location.href = '../index.html';
    }
});

// Manejo de inactividad de la sesión
let timeout;
document.onload = resetTimer;
document.onmousemove = resetTimer;
document.onkeypress = resetTimer;

function logout() {
    alert("Sesión expirada por inactividad.");
    window.location.href = '../login/logout.php'; // Redirigir al logout
}

function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(logout, 180000); // 3 minutos (180,000 ms)
}
</script>

</body>
</html>
