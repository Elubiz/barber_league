<?php
include 'includes/conexion.php';

$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $telefono = preg_replace('/\D/', '', trim($_POST['telefono']));
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';
    $id_servicio = intval($_POST['id_servicio']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, trim($_POST['notas'])) : '';
    
    if (empty($nombre) || empty($telefono) || empty($id_servicio) || empty($fecha) || empty($hora)) {
        $mensaje_error = 'Por favor completa todos los campos obligatorios';
    } 
    elseif (strlen($telefono) !== 10) {
        $mensaje_error = 'El teléfono debe tener exactamente 10 dígitos (sin espacios)';
    }
    else {
        $checkQuery = "SELECT id FROM citas_web 
                      WHERE fecha = '$fecha' 
                      AND hora = '$hora' 
                      AND estado != 'Cancelada'";
        $checkResult = mysqli_query($conexion, $checkQuery);
        
        if (!$checkResult) {
            $mensaje_error = 'Error al verificar disponibilidad: ' . mysqli_error($conexion);
        }
        elseif (mysqli_num_rows($checkResult) > 0) {
            $mensaje_error = 'Este horario ya está reservado. Por favor, selecciona otro.';
        } 
        else {
            $clienteQuery = "SELECT id FROM clientes WHERE telefono = '$telefono' LIMIT 1";
            $clienteResult = mysqli_query($conexion, $clienteQuery);
            
            if (mysqli_num_rows($clienteResult) > 0) {
                $cliente = mysqli_fetch_assoc($clienteResult);
                $id_cliente = $cliente['id'];
                
                $updateCliente = "UPDATE clientes 
                                 SET nombre = '$nombre', correo = '$correo' 
                                 WHERE id = $id_cliente";
                mysqli_query($conexion, $updateCliente);
            } 
            else {
                $insertCliente = "INSERT INTO clientes (nombre, telefono, correo, fecha_registro) 
                                 VALUES ('$nombre', '$telefono', '$correo', NOW())";
                
                if (mysqli_query($conexion, $insertCliente)) {
                    $id_cliente = mysqli_insert_id($conexion);
                } else {
                    $mensaje_error = 'Error al registrar cliente: ' . mysqli_error($conexion);
                    $id_cliente = 0;
                }
            }
            
            if (isset($id_cliente) && $id_cliente > 0) {
                $insertReserva = "INSERT INTO citas_web 
                                  (id_cliente, id_servicio, fecha, hora, notas, estado, fecha_creacion) 
                                  VALUES 
                                  ($id_cliente, $id_servicio, '$fecha', '$hora', '$notas', 'Pendiente', NOW())";
                
                if (mysqli_query($conexion, $insertReserva)) {
                    $id_reserva = mysqli_insert_id($conexion);
                    $mensaje_exito = 'Reserva #' . $id_reserva . ' realizada exitosamente! Te contactaremos al ' . $telefono;
                    
                    $_POST = array();
                } else {
                    $mensaje_error = 'Error al crear reserva: ' . mysqli_error($conexion);
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
    <title>Reservar Cita - Barber League</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .form-control {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border: 2px solid rgba(212, 175, 55, 0.5) !important;
            color: #FFFFFF !important;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.22) !important;
            border-color: #d4af37 !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.3) !important;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        select.form-control option {
            background-color: #1a1a1a;
            color: #FFFFFF;
        }
        
        .form-label {
            color: #d4af37 !important;
            font-weight: 600;
        }
        
        .text-muted {
            color: rgba(255, 255, 255, 0.75) !important;
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="form-section section" style="padding-top: 120px;">
        <div class="container">
            <div class="section-title">
                <h2>Reserva Tu Cita</h2>
                <p>Completa el formulario y asegura tu lugar</p>
            </div>
            
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="reservaForm" method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user"></i> Nombre Completo <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nombre" 
                                name="nombre" 
                                placeholder="Ej: Juan Pérez"
                                required
                                value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                            >
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone"></i> Teléfono <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="telefono" 
                                name="telefono" 
                                placeholder="3106093237"
                                maxlength="12"
                                required
                                value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                            >
                            <small class="text-muted">Solo 10 dígitos sin espacios</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo" class="form-label">
                            <i class="fas fa-envelope"></i> Correo Electrónico (Opcional)
                        </label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="correo" 
                            name="correo" 
                            placeholder="ejemplo@correo.com"
                            value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
                        >
                    </div>
                    
                    <hr style="margin: 2rem 0;">
                    
                    <div class="form-group">
                        <label for="id_servicio" class="form-label">
                            <i class="fas fa-cut"></i> Selecciona el Servicio <span style="color: red;">*</span>
                        </label>
                        <select class="form-control" id="id_servicio" name="id_servicio" required>
                            <option value="">-- Elige un servicio --</option>
                            <?php
                            $query = "SELECT id, nombre_servicio, precio FROM servicios WHERE activo = 1 ORDER BY nombre_servicio";
                            $result = mysqli_query($conexion, $query);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($servicio = mysqli_fetch_assoc($result)) {
                                    $selected = (isset($_POST['id_servicio']) && $_POST['id_servicio'] == $servicio['id']) ? 'selected' : '';
                                    echo "<option value='{$servicio['id']}' {$selected}>{$servicio['nombre_servicio']} - $" . number_format($servicio['precio'], 0, ',', '.') . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="fecha" class="form-label">
                                <i class="fas fa-calendar"></i> Fecha <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="fecha" 
                                name="fecha" 
                                placeholder="Selecciona una fecha"
                                readonly
                                required
                            >
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label for="hora" class="form-label">
                                <i class="fas fa-clock"></i> Hora <span style="color: red;">*</span>
                            </label>
                            <select class="form-control" id="hora" name="hora" required>
                                <option value="">-- Selecciona fecha primero --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notas" class="form-label">
                            <i class="fas fa-comment"></i> Notas o Comentarios (Opcional)
                        </label>
                        <textarea 
                            class="form-control" 
                            id="notas" 
                            name="notas" 
                            rows="3" 
                            placeholder="¿Alguna solicitud especial?"
                        ><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #d4af37, #c49a2e); border: none; padding: 1rem; font-size: 1.1rem; font-weight: 600; margin-top: 1rem;">
                        <i class="fas fa-check-circle"></i> Confirmar Reserva
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
            maxDate: new Date().fp_incr(90),
            dateFormat: 'Y-m-d',
            onChange: function(selectedDates, dateStr) {
                if (dateStr) cargarHoras();
            }
        });
        
        function cargarHoras() {
            const horaSelect = document.getElementById('hora');
            horaSelect.innerHTML = '<option value="">-- Selecciona una hora --</option>';
            
            for (let h = 9; h <= 20; h++) {
                for (let m of ['00', '30']) {
                    if (h === 20 && m === '30') break;
                    const hora = `${h.toString().padStart(2, '0')}:${m}:00`;
                    const display = `${h % 12 || 12}:${m} ${h >= 12 ? 'PM' : 'AM'}`;
                    horaSelect.innerHTML += `<option value="${hora}">${display}</option>`;
                }
            }
        }
        
        document.getElementById('telefono').addEventListener('input', function(e) {
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