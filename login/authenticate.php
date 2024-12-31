<?php
session_start();
require_once '../config/connectdb.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Conectar a la base de datos
    $db = new DB_Connect();
    $conn = $db->connect();

    // Comprobamos si el usuario es un administrador/personal (tabla Usuario) o un estudiante (tabla Usuario_Estudiante)
    $stmt = $conn->prepare("
        SELECT idU, nombre_usuario, contraseña, idP 
        FROM Usuario 
        WHERE nombre_usuario = ? AND estatus = 1
        UNION
        SELECT idE AS idU, matricula AS nombre_usuario, contraseña, idP 
        FROM Usuario_Estudiante 
        WHERE matricula = ? AND estatus = 1
    ");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $stmt->store_result();

    // Verificar si el usuario existe y la contraseña es correcta
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($idU, $nombre_usuario, $hash_password, $idP);
        $stmt->fetch();

        // Verificar contraseña
        if (password_verify($password, $hash_password)) { // Considera usar password_verify si almacenas contraseñas con hash
            // Almacenar datos en la sesión
            $_SESSION['logged_in'] = true;
            $_SESSION['idU'] = $idU;
            $_SESSION['username'] = $nombre_usuario;
            $_SESSION['role'] = $idP;

            // Establecer el tiempo de expiración de la sesión (en segundos)
            $_SESSION['expire_time'] = 180; // 3 minutos para fines de prueba
            $_SESSION['last_activity'] = time(); // Registrar el tiempo actual

            // Redirigir según el perfil
            if ($idP == 1) {
                header("Location: ../dashboard/admin.php");
            } elseif ($idP == 2) {
                header("Location: ../dashboard/control_escolar.php");
            } elseif ($idP == 3) {
                header("Location: ../dashboard/estudiante.php");
            }
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado o inactivo.";
    }

    // Cerrar conexiones
    $stmt->close();
    $conn->close();
}
?>