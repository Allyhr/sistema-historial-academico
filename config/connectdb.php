<?php
class DB_Connect {
    private $db;

    function __construct() {
        // Conectar automáticamente al crear la instancia, si así lo prefieres
        $this->connect();
    }

    public function connect() {
        require_once 'config.php';
        $this->db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

        // Verificar si la conexión fue exitosa
        if (mysqli_connect_errno()) {
            echo "Error de conexión a la base de datos: " . mysqli_connect_error();
            exit();
        }

        return $this->db;
    }

    public function close() {
        if ($this->db) {
            mysqli_close($this->db);
        }
    }
}
?>
