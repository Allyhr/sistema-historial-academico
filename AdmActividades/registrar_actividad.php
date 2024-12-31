<?php
function registrar_actividad($idU, $modulo, $descripcion, $estado, $conn) {
    // Inserción del registro en la tabla de actividades
    $sql = "INSERT INTO actividades (idU, modulo, descripcion, estado) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $idU, $modulo, $descripcion, $estado);
    $stmt->execute();
    $stmt->close();
}
?>
