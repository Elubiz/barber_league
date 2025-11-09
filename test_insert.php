<?php
include 'includes/conexion.php';

// Test directo de INSERT
$nombre = 'Test Usuario';
$telefono = '3001234567';
$correo = 'test@test.com';
$id_servicio = 1;
$fecha = date('Y-m-d', strtotime('+1 day'));
$hora = '10:00:00';

// Crear cliente
$insertCliente = "INSERT INTO clientes (nombre, telefono, correo, fecha_registro) 
                 VALUES ('$nombre', '$telefono', '$correo', NOW())";

if (mysqli_query($conexion, $insertCliente)) {
    $id_cliente = mysqli_insert_id($conexion);
    echo "✅ Cliente creado: ID $id_cliente<br>";
    
    // Crear reserva
    $insertReserva = "INSERT INTO reservas (id_cliente, id_servicio, fecha, hora, estado, fecha_creacion) 
                     VALUES ($id_cliente, $id_servicio, '$fecha', '$hora', 'Pendiente', NOW())";
    
    if (mysqli_query($conexion, $insertReserva)) {
        echo "✅ Reserva creada: ID " . mysqli_insert_id($conexion);
    } else {
        echo "❌ Error reserva: " . mysqli_error($conexion);
    }
} else {
    echo "❌ Error cliente: " . mysqli_error($conexion);
}
?>