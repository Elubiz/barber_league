<?php
$host = "localhost";   // Servidor local
$user = "root";        // Usuario por defecto de XAMPP
$pass = "";            // Contraseña vacía por defecto
$db   = "barberia_db"; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}


$conexion->set_charset("utf8");
?>
