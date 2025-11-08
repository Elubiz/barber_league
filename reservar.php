<?php
$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/conexion.php';
    
    // Obtener datos
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $telefono = mysqli_real_escape_string($conexion, str_replace(' ', '', trim($_POST['telefono'])));
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';
    $id_servicio = intval($_POST['id_servicio']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, trim($_POST['notas'])) : '';
    
    // Validación básica
    if (empty($nombre) || empty($telefono) || empty($id_servicio) || empty($fecha) || empty($hora)) {
        $mensaje_error = 'Por favor completa todos los campos obligatorios';
    } 
    elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $mensaje_error = 'El teléfono debe tener exactamente 10 dígitos';
    }
    else {
        // Verificar disponibilidad
        $checkQuery = "SELECT id FROM reservas 
                      WHERE fecha = '$fecha' 
                      AND hora = '$hora' 
                      AND estado != 'Cancelada'";
        $checkResult = mysqli_query($conexion, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $mensaje_error = 'Este horario ya está reservado. Por favor, selecciona otro.';
        } else {
            // Buscar o crear cliente
            $clienteQuery = "SELECT id FROM clientes WHERE telefono = '$telefono' LIMIT 1";
            $clienteResult = mysqli_query($conexion, $clienteQuery);
            
            if (mysqli_num_rows($clienteResult) > 0) {
                $cliente = mysqli_fetch_assoc($clienteResult);
                $id_cliente = $cliente['id'];
                
                $updateCliente = "UPDATE clientes SET nombre = '$nombre', correo = '$correo' WHERE id = $id_cliente";
                mysqli_query($conexion, $updateCliente);
            } else {
                $insertCliente = "INSERT INTO clientes (nombre, telefono, correo) 
                                 VALUES ('$nombre', '$telefono', '$correo')";
                if (mysqli_query($conexion, $insertCliente)) {
                    $id_cliente = mysqli_insert_id($conexion);
                } else {
                    $mensaje_error = 'Error al registrar el cliente: ' . mysqli_error($conexion);
                    $id_cliente = 0;
                }
            }
            
            // Crear reserva
            if ($id_cliente > 0) {
                $insertReserva = "INSERT INTO reservas (id_cliente, id_servicio, fecha, hora, notas, estado) 
                                  VALUES ($id_cliente, $id_servicio, '$fecha', '$hora', '$notas', 'Pendiente')";
                
                if (mysqli_query($conexion, $insertReserva)) {
                    $mensaje_exito = '¡Reserva realizada exitosamente! Te contactaremos pronto al ' . $telefono;
                } else {
                    $mensaje_error = 'Error al crear la reserva: ' . mysqli_error($conexion);
                }
            }
        }
    }
    
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cita - Barber League</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Flatpickr CSS para selector de fecha -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- 🎨 ESTILOS MEJORADOS PARA INPUTS MÁS VISIBLES -->
    <style>
        /* Inputs con fondo más claro y texto blanco brillante */
        .form-control {
            background-color: rgba(255, 255, 255, 0.15) !important;
            border: 2px solid rgba(212, 175, 55, 0.5) !important;
            color: #FFFFFF !important;
            font-weight: 500;
            font-size: 1rem;
        }
        
        /* Al hacer focus - aún más claro */
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.22) !important;
            border-color: #d4af37 !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.3) !important;
        }
        
        /* Placeholder más visible */
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 400;
        }
        
        /* Para los selects */
        select.form-control {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #FFFFFF !important;
            cursor: pointer;
        }
        
        select.form-control option {
            background-color: #1a1a1a;
            color: #FFFFFF;
            padding: 12px;
        }
        
        /* Para textarea */
        textarea.form-control {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #FFFFFF !important;
        }
        
        /* Labels dorados y más visibles */
        .form-label {
            color: #d4af37 !important;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        /* Texto de ayuda más visible */
        .text-muted {
            color: rgba(255, 255, 255, 0.75) !important;
            font-size: 0.875rem;
        }
        
        /* Validación visual mejorada */
        .form-control.is-valid {
            border-color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.1) !important;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
        
        /* Botón de submit */
        .btn-submit {
            background: linear-gradient(135deg, #d4af37, #c49a2e);
            color: #1a1a1a;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- PÁGINA DE RESERVA -->
    <section class="form-section section" style="padding-top: 120px;">
        <div class="container">
            <div class="section-title">
                <h2>Reserva Tu Cita</h2>
                <p>Completa el formulario y asegura tu lugar</p>
            </div>
            
            <!-- Mensajes de éxito/error -->
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
                    <!-- Información Personal -->
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
                                placeholder="315 639 3235"
                                maxlength="12"
                                required
                                value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                            >
                            <small class="text-muted">Formato: 315 639 3235 (10 dígitos)</small>
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
                    
                    <!-- Detalles de la Reserva -->
                    <hr style="margin: 2rem 0;">
                    
                    <div class="form-group">
                        <label for="id_servicio" class="form-label">
                            <i class="fas fa-cut"></i> Selecciona el Servicio <span style="color: red;">*</span>
                        </label>
                        <select class="form-control" id="id_servicio" name="id_servicio" required>
                            <option value="">-- Elige un servicio --</option>
                            <?php
                            include 'includes/conexion.php';
                            
                            $query = "SELECT id, nombre_servicio, precio FROM servicios WHERE activo = 1 ORDER BY nombre_servicio";
                            $result = mysqli_query($conexion, $query);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($servicio = mysqli_fetch_assoc($result)) {
                                    $selected = (isset($_POST['id_servicio']) && $_POST['id_servicio'] == $servicio['id']) ? 'selected' : '';
                                    echo "<option value='{$servicio['id']}' {$selected}>{$servicio['nombre_servicio']} - $" . number_format($servicio['precio'], 0, ',', '.') . "</option>";
                                }
                            }
                            mysqli_close($conexion);
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
                    
                    <!-- Información Adicional -->
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
                    
                    <!-- Información de Horarios -->
                    <div style="background: var(--gold-light); padding: 1.5rem; border-radius: 10px; margin: 1.5rem 0;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary-black);">
                            <i class="fas fa-info-circle"></i> Información Importante
                        </h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: var(--gray-medium);">
                            <li>Horario de atención: 9:00 AM - 9:00 PM</li>
                            <li>Duración aproximada: 60 minutos</li>
                            <li>Por favor llega 5 minutos antes</li>
                            <li>Cancelaciones con 24h de anticipación</li>
                        </ul>
                    </div>
                    
                    <!-- Botón de Envío -->
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle"></i> Confirmar Reserva
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <!-- Script personalizado para el formulario -->
    <script>
        // Inicializar selector de fecha con Flatpickr
        const fechaInput = document.getElementById('fecha');
        const horaSelect = document.getElementById('hora');
        
        flatpickr(fechaInput, {
            locale: 'es',
            minDate: 'today',
            maxDate: new Date().fp_incr(90), // 90 días adelante
            dateFormat: 'Y-m-d',
            disable: [
                // Opcional: deshabilitar domingos
                // function(date) {
                //     return (date.getDay() === 0);
                // }
            ],
            onChange: function(selectedDates, dateStr, instance) {
                // Cuando se selecciona una fecha, cargar las horas disponibles
                if (dateStr) {
                    cargarHorasDisponibles(dateStr);
                }
            }
        });
        
        // Función para cargar horas disponibles
        function cargarHorasDisponibles(fecha) {
            horaSelect.innerHTML = '<option value="">Cargando...</option>';
            
            // Generar horarios de 9:00 AM a 9:00 PM cada 30 minutos
            const horarios = [];
            for (let hora = 9; hora <= 20; hora++) {
                for (let minuto of ['00', '30']) {
                    if (hora === 20 && minuto === '30') break; // No agregar 8:30 PM
                    const horaStr = `${hora.toString().padStart(2, '0')}:${minuto}`;
                    horarios.push(horaStr);
                }
            }
            
            // Limpiar y llenar el select
            horaSelect.innerHTML = '<option value="">-- Selecciona una hora --</option>';
            horarios.forEach(hora => {
                const option = document.createElement('option');
                option.value = hora + ':00';
                option.textContent = convertirA12Horas(hora);
                horaSelect.appendChild(option);
            });
        }
        
        // Convertir hora de 24h a 12h (AM/PM)
        function convertirA12Horas(hora24) {
            const [hora, minuto] = hora24.split(':');
            let h = parseInt(hora);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return `${h}:${minuto} ${ampm}`;
        }
        
        // Validar teléfono en tiempo real - FORMATO COLOMBIANO
        document.getElementById('telefono').addEventListener('input', function(e) {
            let valor = this.value.replace(/\D/g, ''); // Solo números
            
            // Limitar a 10 dígitos
            if (valor.length > 10) {
                valor = valor.slice(0, 10);
            }
            
            // Formatear con espacios: 310 609 3237
            if (valor.length >= 7) {
                this.value = valor.slice(0, 3) + ' ' + valor.slice(3, 6) + ' ' + valor.slice(6);
            } else if (valor.length >= 4) {
                this.value = valor.slice(0, 3) + ' ' + valor.slice(3);
            } else {
                this.value = valor;
            }
            
            // Validación visual
            const soloNumeros = this.value.replace(/\s/g, '');
            if (soloNumeros.length === 10) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (soloNumeros.length > 0) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
        
        // Validación antes de enviar
        document.getElementById('reservaForm').addEventListener('submit', function(e) {
            const telefono = document.getElementById('telefono').value.replace(/\s/g, '');
            
            if (telefono.length !== 10) {
                e.preventDefault();
                alert('El teléfono debe tener exactamente 10 dígitos\nEjemplo: 310 609 3237');
                document.getElementById('telefono').focus();
                return false;
            }
        });
        
        // Mostrar alerta de éxito si existe
        <?php if ($mensaje_exito): ?>
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva Exitosa!',
                    text: '<?php echo $mensaje_exito; ?>',
                    confirmButtonColor: '#d4af37'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            }, 500);
        <?php endif; ?>
    </script>
    
</body>
</html>