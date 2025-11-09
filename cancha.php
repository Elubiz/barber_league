<?php
// ✅ INCLUIR CONEXIÓN AL INICIO
include 'includes/conexion.php';

$mensaje_exito = '';
$mensaje_error = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $debug_info = '<pre>POST: ' . print_r($_POST, true) . '</pre>';
    
    // Obtener datos
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $telefono = preg_replace('/\D/', '', trim($_POST['telefono']));
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora_inicio = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $duracion_horas = intval($_POST['duracion']);
    $duracion_minutos = $duracion_horas * 60;
    $num_personas = intval($_POST['num_personas']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, trim($_POST['notas'])) : '';
    
    // Validación
    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora_inicio) || $duracion_horas < 1) {
        $mensaje_error = 'Por favor completa todos los campos obligatorios';
    } 
    elseif (strlen($telefono) !== 10) {
        $mensaje_error = 'El teléfono debe tener exactamente 10 dígitos';
    } 
    else {
        // ✅ CALCULAR HORA_FIN
        try {
            $horaObj = new DateTime($hora_inicio);
            $horaFinObj = clone $horaObj;
            $horaFinObj->modify("+{$duracion_horas} hours");
            $hora_fin = $horaFinObj->format('H:i:s');
        } catch (Exception $e) {
            $mensaje_error = 'Error al calcular hora final';
            $hora_fin = null;
        }
        
        if ($hora_fin) {
            // ✅ VERIFICAR DISPONIBILIDAD
            $checkQuery = "SELECT id FROM reservas_cancha 
                          WHERE fecha = '$fecha' 
                          AND estado != 'Cancelada'
                          AND (
                              ('$hora_inicio' >= hora_inicio AND '$hora_inicio' < hora_fin)
                              OR ('$hora_fin' > hora_inicio AND '$hora_fin' <= hora_fin)
                              OR ('$hora_inicio' <= hora_inicio AND '$hora_fin' >= hora_fin)
                          )";
            
            $checkResult = mysqli_query($conexion, $checkQuery);
            
            if (!$checkResult) {
                $mensaje_error = 'Error al verificar: ' . mysqli_error($conexion);
            }
            elseif (mysqli_num_rows($checkResult) > 0) {
                $mensaje_error = 'Este horario ya está reservado. Selecciona otro.';
            } 
            else {
                // ✅ CALCULAR PRECIO
                $precio_hora = 50000;
                $precio_total = $precio_hora * $duracion_horas;
                if ($num_personas >= 5) {
                    $precio_total *= 0.90;
                }
                
                // ✅ BUSCAR/CREAR CLIENTE
                $clienteQuery = "SELECT id FROM clientes WHERE telefono = '$telefono' LIMIT 1";
                $clienteResult = mysqli_query($conexion, $clienteQuery);
                
                if (mysqli_num_rows($clienteResult) > 0) {
                    $cliente = mysqli_fetch_assoc($clienteResult);
                    $id_cliente = $cliente['id'];
                    
                    mysqli_query($conexion, "UPDATE clientes SET nombre = '$nombre', correo = '$correo' WHERE id = $id_cliente");
                } else {
                    $insertCliente = "INSERT INTO clientes (nombre, telefono, correo, fecha_registro) 
                                     VALUES ('$nombre', '$telefono', '$correo', NOW())";
                    
                    if (mysqli_query($conexion, $insertCliente)) {
                        $id_cliente = mysqli_insert_id($conexion);
                    } else {
                        $mensaje_error = 'Error al registrar cliente: ' . mysqli_error($conexion);
                        $id_cliente = 0;
                    }
                }
                
                // ✅ CREAR RESERVA CANCHA
                if (isset($id_cliente) && $id_cliente > 0) {
                    $insertReserva = "INSERT INTO reservas_cancha 
                                     (id_cliente, fecha, hora_inicio, hora_fin, duracion, precio, num_personas, notas, estado, fecha_creacion) 
                                     VALUES 
                                     ($id_cliente, '$fecha', '$hora_inicio', '$hora_fin', $duracion_minutos, $precio_total, $num_personas, '$notas', 'Pendiente', NOW())";
                    
                    $debug_info .= "Query: $insertReserva<br>";
                    
                    if (mysqli_query($conexion, $insertReserva)) {
                        $id_reserva = mysqli_insert_id($conexion);
                        $mensaje_exito = '¡Reserva #' . $id_reserva . ' confirmada! ' . $duracion_horas . 'h - Total: $' . number_format($precio_total, 0, ',', '.');
                        $_POST = array();
                    } else {
                        $mensaje_error = 'Error al crear reserva: ' . mysqli_error($conexion);
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cancha - Barber League</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .debug-box { background: #fff3cd; border: 2px solid #ffc107; padding: 1rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="section" style="padding-top: 120px;">
        <div class="container">
            <div class="section-title">
                <h2>Reserva Cancha Sintética</h2>
            </div>
            
            <?php if (!empty($debug_info) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="debug-box"><?php echo $debug_info; ?></div>
            <?php endif; ?>
            
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-user"></i> Nombre *</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-phone"></i> Teléfono *</label>
                            <input type="tel" class="form-control" name="telefono" maxlength="12" required>
                            <small class="text-muted">10 dígitos sin espacios</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label><i class="fas fa-envelope"></i> Correo (Opcional)</label>
                        <input type="email" class="form-control" name="correo">
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-calendar"></i> Fecha *</label>
                            <input type="text" id="fecha" name="fecha" class="form-control" readonly required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-clock"></i> Hora Inicio *</label>
                            <select id="hora_inicio" name="hora_inicio" class="form-control" required>
                                <option value="">Selecciona fecha</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-hourglass"></i> Duración *</label>
                            <select name="duracion" class="form-control" required>
                                <option value="1">1 Hora - $50,000</option>
                                <option value="2">2 Horas - $95,000</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-users"></i> Personas</label>
                            <input type="number" name="num_personas" class="form-control" min="1" max="20" value="10">
                            <small class="text-muted">5+ personas = 10% descuento</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label><i class="fas fa-comment"></i> Notas</label>
                        <textarea name="notas" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" style="padding: 1rem; font-size: 1.1rem;">
                        <i class="fas fa-check"></i> Confirmar Reserva
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <script>
        flatpickr('#fecha', {
            locale: 'es',
            minDate: 'today',
            maxDate: new Date().fp_incr(60),
            dateFormat: 'Y-m-d',
            onChange: function() { cargarHoras(); }
        });
        
        function cargarHoras() {
            const select = document.getElementById('hora_inicio');
            select.innerHTML = '<option value="">-- Selecciona --</option>';
            for (let h = 7; h <= 22; h++) {
                const hora = `${h.toString().padStart(2, '0')}:00:00`;
                const display = `${h % 12 || 12}:00 ${h >= 12 ? 'PM' : 'AM'}`;
                select.innerHTML += `<option value="${hora}">${display}</option>`;
            }
        }
        
        document.querySelector('input[name="telefono"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
        
        <?php if ($mensaje_exito): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Reserva Exitosa!',
            text: '<?php echo $mensaje_exito; ?>',
            confirmButtonColor: '#d4af37'
        }).then(() => window.location.href = 'index.php');
        <?php endif; ?>
    </script>
</body>
</html>
<?php mysqli_close($conexion); ?>