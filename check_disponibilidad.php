<?php
/**
 * BARBER LEAGUE - Verificación de Disponibilidad
 * Este archivo verifica si una fecha/hora está disponible para reserva
 */

header('Content-Type: application/json');

// Solo permitir peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Incluir conexión a base de datos
include 'includes/conexion.php';

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar datos recibidos
if (!isset($data['fecha']) || !isset($data['hora'])) {
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

$fecha = mysqli_real_escape_string($conexion, $data['fecha']);
$hora = mysqli_real_escape_string($conexion, $data['hora']);
$servicio = isset($data['servicio']) ? intval($data['servicio']) : 0;

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => 'Formato de fecha inválido'
    ]);
    exit;
}

// Validar formato de hora
if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => 'Formato de hora inválido'
    ]);
    exit;
}

// Verificar que la fecha no sea pasada
$hoy = date('Y-m-d');
if ($fecha < $hoy) {
    echo json_encode([
        'success' => true,
        'disponible' => false,
        'message' => 'No se pueden hacer reservas en fechas pasadas'
    ]);
    exit;
}

// Verificar horario de atención (9 AM - 9 PM)
$horaInt = intval(str_replace(':', '', $hora));
if ($horaInt < 900 || $horaInt >= 2100) {
    echo json_encode([
        'success' => true,
        'disponible' => false,
        'message' => 'Horario fuera del horario de atención (9:00 AM - 9:00 PM)'
    ]);
    exit;
}

// Consultar disponibilidad en la base de datos
$query = "SELECT id, estado FROM reservas 
          WHERE fecha = '$fecha' 
          AND hora = '$hora' 
          AND estado != 'Cancelada'
          LIMIT 1";

$result = mysqli_query($conexion, $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'disponible' => false,
        'message' => 'Error al consultar la base de datos'
    ]);
    exit;
}

$disponible = (mysqli_num_rows($result) === 0);

// Si hay servicio, verificar duración y conflictos
if ($disponible && $servicio > 0) {
    // Obtener duración del servicio
    $servicioQuery = "SELECT duracion FROM servicios WHERE id = $servicio LIMIT 1";
    $servicioResult = mysqli_query($conexion, $servicioQuery);
    
    if ($servicioResult && mysqli_num_rows($servicioResult) > 0) {
        $servicioData = mysqli_fetch_assoc($servicioResult);
        $duracion = intval($servicioData['duracion']); // en minutos
        
        // Calcular hora de fin
        $horaInicio = new DateTime($fecha . ' ' . $hora);
        $horaFin = clone $horaInicio;
        $horaFin->modify("+{$duracion} minutes");
        
        // Verificar conflictos con otras reservas
        $conflictQuery = "SELECT id FROM reservas 
                          WHERE fecha = '$fecha' 
                          AND estado != 'Cancelada'
                          AND (
                              (hora >= '{$horaInicio->format('H:i')}' AND hora < '{$horaFin->format('H:i')}')
                              OR 
                              (TIME(DATE_ADD(CONCAT(fecha, ' ', hora), INTERVAL 60 MINUTE)) > '{$horaInicio->format('H:i')}' 
                               AND hora < '{$horaFin->format('H:i')}')
                          )";
        
        $conflictResult = mysqli_query($conexion, $conflictQuery);
        
        if ($conflictResult && mysqli_num_rows($conflictResult) > 0) {
            $disponible = false;
        }
    }
}

// Preparar respuesta
$response = [
    'success' => true,
    'disponible' => $disponible,
    'fecha' => $fecha,
    'hora' => $hora,
    'message' => $disponible ? 'Horario disponible' : 'Este horario ya está reservado o tiene conflictos'
];

// Si está disponible, sugerir horarios cercanos alternativos
if (!$disponible) {
    $alternativosQuery = "SELECT DISTINCT hora FROM reservas 
                          WHERE fecha = '$fecha' 
                          AND estado != 'Cancelada'
                          ORDER BY hora";
    
    $alternativosResult = mysqli_query($conexion, $alternativosQuery);
    $horariosOcupados = [];
    
    if ($alternativosResult) {
        while ($row = mysqli_fetch_assoc($alternativosResult)) {
            $horariosOcupados[] = $row['hora'];
        }
    }
    
    // Generar horarios disponibles del día
    $horariosDisponibles = [];
    for ($h = 9; $h < 21; $h++) {
        for ($m = 0; $m < 60; $m += 30) {
            $horario = sprintf('%02d:%02d', $h, $m);
            if (!in_array($horario, $horariosOcupados)) {
                $horariosDisponibles[] = $horario;
            }
        }
    }
    
    $response['alternativas'] = array_slice($horariosDisponibles, 0, 5); // Primeras 5 alternativas
}

// Cerrar conexión y enviar respuesta
mysqli_close($conexion);
echo json_encode($response);
?>