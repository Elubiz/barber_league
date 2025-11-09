<?php
session_start();
header('Content-Type: application/json');

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

include '../includes/conexion.php';

// Verificar que se recibieron datos
if (!isset($_POST['id']) || !isset($_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

$id_reserva = intval($_POST['id']);
$accion = $_POST['accion'];

$response = ['success' => false, 'message' => 'Acción no válida'];

// Procesar según la acción
switch ($accion) {
    case 'cambiar_estado':
        if (!isset($_POST['estado'])) {
            $response['message'] = 'Estado no especificado';
            break;
        }
        
        $nuevo_estado = mysqli_real_escape_string($conexion, $_POST['estado']);
        $estados_validos = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            $response['message'] = 'Estado no válido';
            break;
        }
        
        $query = "UPDATE reservas_cancha SET estado = '$nuevo_estado' WHERE id = $id_reserva";
        
        if (mysqli_query($conexion, $query)) {
            $response['success'] = true;
            $response['message'] = "Estado cambiado a: $nuevo_estado";
        } else {
            $response['message'] = 'Error al actualizar: ' . mysqli_error($conexion);
        }
        break;
        
    case 'eliminar':
        $check_query = "SELECT id FROM reservas_cancha WHERE id = $id_reserva LIMIT 1";
        $check_result = mysqli_query($conexion, $check_query);
        
        if (mysqli_num_rows($check_result) === 0) {
            $response['message'] = 'Reserva no encontrada';
            break;
        }
        
        $query = "DELETE FROM reservas_cancha WHERE id = $id_reserva";
        
        if (mysqli_query($conexion, $query)) {
            $response['success'] = true;
            $response['message'] = 'Reserva de cancha eliminada correctamente';
        } else {
            $response['message'] = 'Error al eliminar: ' . mysqli_error($conexion);
        }
        break;
        
    case 'obtener_detalles':
        $query = "SELECT rc.*, c.nombre, c.telefono, c.correo
                  FROM reservas_cancha rc
                  INNER JOIN clientes c ON rc.id_cliente = c.id
                  WHERE rc.id = $id_reserva
                  LIMIT 1";
        
        $result = mysqli_query($conexion, $query);
        
        if ($result && mysqli_num_rows($result) === 1) {
            $reserva = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = $reserva;
        } else {
            $response['message'] = 'Reserva no encontrada';
        }
        break;
        
    default:
        $response['message'] = 'Acción no reconocida';
}

mysqli_close($conexion);
echo json_encode($response);
?>