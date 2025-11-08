<?php
$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/conexion.php';
    
    // Obtener datos del formulario
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $telefono = mysqli_real_escape_string($conexion, str_replace(' ', '', trim($_POST['telefono'])));
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora_inicio = mysqli_real_escape_string($conexion, $_POST['hora_inicio']);
    $duracion = intval($_POST['duracion']);
    $num_personas = intval($_POST['num_personas']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, trim($_POST['notas'])) : '';
    
    // Validación básica
    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora_inicio) || $duracion < 1) {
        $mensaje_error = 'Por favor completa todos los campos obligatorios';
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $mensaje_error = 'El teléfono debe tener exactamente 10 dígitos';
    } else {
        // Calcular hora_fin
        $hora_inicio_timestamp = strtotime($fecha . ' ' . $hora_inicio);
        $hora_fin_timestamp = $hora_inicio_timestamp + ($duracion * 3600);
        $hora_fin = date('H:i:s', $hora_fin_timestamp);
        
        // Verificar disponibilidad
        $checkQuery = "SELECT id FROM reservas_cancha 
                      WHERE fecha = '$fecha' 
                      AND estado != 'Cancelada'
                      AND (
                          (hora_inicio < '$hora_fin' AND hora_inicio >= '$hora_inicio')
                          OR (hora_fin > '$hora_inicio' AND hora_fin <= '$hora_fin')
                          OR (hora_inicio <= '$hora_inicio' AND hora_fin >= '$hora_fin')
                      )";
        
        $checkResult = mysqli_query($conexion, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $mensaje_error = 'Este horario ya está reservado. Por favor, selecciona otro.';
        } else {
            // Calcular precio
            $precio_hora = 50000;
            $precio_total = $precio_hora * $duracion;
            
            // Descuento por grupo (5+ personas = 10%)
            if ($num_personas >= 5) {
                $precio_total = $precio_total * 0.90;
            }
            
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
                $insertReserva = "INSERT INTO reservas_cancha 
                                 (id_cliente, fecha, hora_inicio, hora_fin, duracion, precio, num_personas, notas, estado) 
                                 VALUES 
                                 ($id_cliente, '$fecha', '$hora_inicio', '$hora_fin', $duracion, $precio_total, $num_personas, '$notas', 'Pendiente')";
                
                if (mysqli_query($conexion, $insertReserva)) {
                    $mensaje_exito = '¡Reserva de cancha realizada exitosamente! Total a pagar: $' . number_format($precio_total, 0, ',', '.') . '. Te contactaremos al ' . $telefono;
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
    <title>Reservar Cancha Sintética - Barber League</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .cancha-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1600') center/cover;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 70px;
        }
        
        .price-card {
            background: linear-gradient(135deg, #d4af37, #c49a2e);
            padding: 2rem;
            border-radius: 15px;
            color: #1a1a1a;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }
        
        .price-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .price-card p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .discount-badge {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            font-weight: 600;
            margin-top: 1rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #d4af37;
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        
        .time-slot {
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            margin: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .time-slot:hover {
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
        }
        
        .time-slot.selected {
            border-color: #d4af37;
            background: #d4af37;
            color: #1a1a1a;
        }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO SECTION -->
    <section class="cancha-hero">
        <div class="container">
            <div class="hero-content">
                <i class="fas fa-futbol fa-4x mb-3" style="color: #d4af37;"></i>
                <h1>Cancha Sintética <span style="color: #d4af37;">Premium</span></h1>
                <p style="font-size: 1.3rem; max-width: 700px; margin: 1.5rem auto;">
                    Césped sintético de última generación con iluminación LED para disfrutar del mejor fútbol en Ibagué
                </p>
            </div>
        </div>
    </section>

    <!-- CARACTERÍSTICAS Y PRECIOS -->
    <section class="section" style="padding-top: 3rem;">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-md-6 mb-4">
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1.5rem;">
                        Instalaciones de <span style="color: #d4af37;">Primera Clase</span>
                    </h2>
                    
                    <ul class="feature-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <strong>Césped Sintético Premium</strong> - Última generación con sistema de drenaje
                        </li>
                        <li>
                            <i class="fas fa-lightbulb"></i>
                            <strong>Iluminación LED</strong> - Perfecta visibilidad para partidos nocturnos
                        </li>
                        <li>
                            <i class="fas fa-shield-alt"></i>
                            <strong>Redes Profesionales</strong> - Resistentes y de alta calidad
                        </li>
                        <li>
                            <i class="fas fa-door-open"></i>
                            <strong>Vestuarios Equipados</strong> - Limpios y cómodos con duchas
                        </li>
                        <li>
                            <i class="fas fa-parking"></i>
                            <strong>Parqueadero Gratis</strong> - Estacionamiento seguro incluido
                        </li>
                        <li>
                            <i class="fas fa-water"></i>
                            <strong>Zona de Hidratación</strong> - Punto de agua y bebidas disponibles
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="price-card">
                        <i class="fas fa-tag fa-2x mb-3"></i>
                        <h3>$50,000</h3>
                        <p style="font-size: 1.3rem; font-weight: 600;">Por Hora</p>
                        <hr style="border-color: rgba(26, 26, 26, 0.3); margin: 1.5rem 0;">
                        <p><strong>Tarifa 2 Horas:</strong> $95,000</p>
                        <span class="discount-badge">
                            <i class="fas fa-users"></i> desceunto de 20% para grupos de 5 o más personas que reserven cancha y barberia
                        </span>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-top: 1rem;">
                        <h5 style="margin-bottom: 1rem; color: #1a1a1a;">
                            <i class="fas fa-clock"></i> Horarios Disponibles
                        </h5>
                        <p style="margin: 0; color: #666;">
                            <strong>Lunes - Domingo:</strong> 7:00 AM - 11:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FORMULARIO DE RESERVA -->
    <section class="section" style="background: #f5f6fa; padding: 3rem 0;">
        <div class="container">
            <div class="text-center mb-4">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; background: #6a7fd5ff;">
                    <i class="fas fa-calendar-check"></i> Reserva tu Cancha
                </h2>
                <p style="color: #666; font-size: 1.1rem;">
                    Completa el formulario y asegura tu horario
                </p>
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
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container">
                        <form id="canchaForm" method="POST" action="">
                            <!-- Información Personal -->
                            <h4 style="margin-bottom: 1.5rem; color: #1a1a1a;">
                                <i class="fas fa-user"></i> Información de Contacto
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
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
                                
                                <div class="col-md-6 mb-3">
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
                            
                            <div class="mb-3">
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
                            
                            <!-- Detalles de la Reserva -->
                            <h4 style="margin-bottom: 1.5rem; color: #1a1a1a;">
                                <i class="fas fa-futbol"></i> Detalles de la Reserva
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
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
                                
                                <div class="col-md-6 mb-3">
                                    <label for="hora_inicio" class="form-label">
                                        <i class="fas fa-clock"></i> Hora de Inicio <span style="color: red;">*</span>
                                    </label>
                                    <select class="form-control" id="hora_inicio" name="hora_inicio" required>
                                        <option value="">-- Selecciona fecha primero --</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="duracion" class="form-label">
                                        <i class="fas fa-hourglass-half"></i> Duración <span style="color: red;">*</span>
                                    </label>
                                    <select class="form-control" id="duracion" name="duracion" required>
                                        <option value="1">1 Hora - $50,000</option>
                                        <option value="2">2 Horas - $95,000</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="num_personas" class="form-label">
                                        <i class="fas fa-users"></i> Número de Personas
                                    </label>
                                    <input 
                                        type="number" 
                                        class="form-control" 
                                        id="num_personas" 
                                        name="num_personas" 
                                        min="1" 
                                        max="20" 
                                        value="10"
                                        placeholder="¿Cuántos van a jugar?"
                                    >
                                    <small class="text-muted">
                                        <i class="fas fa-tag"></i> 5+ personas = 10% descuento
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">
                                    <i class="fas fa-comment"></i> Notas o Comentarios (Opcional)
                                </label>
                                <textarea 
                                    class="form-control" 
                                    id="notas" 
                                    name="notas" 
                                    rows="3" 
                                    placeholder="Ej: Necesitamos balón, chalecos, etc."
                                ><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Resumen de Precio -->
                            <div id="precioResumen" style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; display: none;">
                                <h5 style="margin-bottom: 1rem; color: #1a1a1a;">
                                    <i class="fas fa-calculator"></i> Resumen de Precio
                                </h5>
                                <div id="precioDetalle"></div>
                            </div>
                            
                            <!-- Información Importante -->
                            <div style="background: #e8f5e9; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem;">
                                <h5 style="margin-bottom: 1rem; color: #1a1a1a;">
                                    <i class="fas fa-info-circle"></i> Información Importante
                                </h5>
                                <ul style="margin: 0; padding-left: 1.5rem; color: #666;">
                                    <li>Llegar 10 minutos antes del horario reservado</li>
                                    <li>Firmar un formulario de responsabilidad menores de 18</li>
                                    <li>El pago se realiza en el lugar antes de iniciar</li>
                                    <li>Cancelaciones con 24h de anticipación sin cargo</li>
                                    <li>Prohibido el ingreso de bebidas alcohólicas</li>
                                </ul>
                            </div>
                            
                            <!-- Botón de Envío -->
                            <button type="submit" class="btn btn-primary w-100" style="padding: 1rem; font-size: 1.1rem; font-weight: 600; background: linear-gradient(135deg, #d4af37, #c49a2e); border: none; border-radius: 10px;">
                                <i class="fas fa-check-circle"></i> Confirmar Reserva de Cancha
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    
    <script>
        // Inicializar selector de fecha
        const fechaInput = document.getElementById('fecha');
        const horaSelect = document.getElementById('hora_inicio');
        const duracionSelect = document.getElementById('duracion');
        const numPersonasInput = document.getElementById('num_personas');
        
        flatpickr(fechaInput, {
            locale: 'es',
            minDate: 'today',
            maxDate: new Date().fp_incr(60),
            dateFormat: 'Y-m-d',
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    cargarHorasDisponibles(dateStr);
                }
            }
        });
        
        // Generar horarios de 7 AM a 11 PM cada hora
        function cargarHorasDisponibles(fecha) {
            horaSelect.innerHTML = '<option value="">Cargando...</option>';
            
            const horarios = [];
            for (let hora = 7; hora <= 22; hora++) {
                const horaStr = hora.toString().padStart(2, '0') + ':00:00';
                horarios.push(horaStr);
            }
            
            horaSelect.innerHTML = '<option value="">-- Selecciona hora de inicio --</option>';
            horarios.forEach(hora => {
                const option = document.createElement('option');
                option.value = hora;
                option.textContent = convertirA12Horas(hora);
                horaSelect.appendChild(option);
            });
        }
        
        // Convertir hora de 24h a 12h (AM/PM)
        function convertirA12Horas(hora24) {
            const [hora] = hora24.split(':');
            let h = parseInt(hora);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return `${h}:00 ${ampm}`;
        }
        
        // Calcular y mostrar precio
        function actualizarPrecio() {
            const duracion = parseInt(duracionSelect.value) || 1;
            const numPersonas = parseInt(numPersonasInput.value) || 1;
            
            let precioBase = duracion === 1 ? 50000 : 95000;
            let descuento = 0;
            
            if (numPersonas >= 5) {
                descuento = precioBase * 0.10;
            }
            
            const precioFinal = precioBase - descuento;
            
            document.getElementById('precioResumen').style.display = 'block';
            document.getElementById('precioDetalle').innerHTML = `
                <p style="margin: 0.5rem 0;">
                    <strong>Duración:</strong> ${duracion} hora(s) - $${precioBase.toLocaleString('es-CO')}
                </p>
                ${descuento > 0 ? `
                    <p style="margin: 0.5rem 0; color: #28a745;">
                        <strong>Descuento Grupal (${numPersonas} personas):</strong> -$${descuento.toLocaleString('es-CO')}
                    </p>
                ` : ''}
                <hr style="margin: 1rem 0;">
                <p style="margin: 0; font-size: 1.3rem; font-weight: 700; color: #d4af37;">
                    <strong>Total a Pagar:</strong> $${precioFinal.toLocaleString('es-CO')}
                </p>
            `;
        }
        
        // Event listeners para actualizar precio
        duracionSelect.addEventListener('change', actualizarPrecio);
        numPersonasInput.addEventListener('input', actualizarPrecio);
        
        // Validar teléfono
        document.getElementById('telefono').addEventListener('input', function(e) {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length > 10) valor = valor.slice(0, 10);
            
            if (valor.length >= 7) {
                this.value = valor.slice(0, 3) + ' ' + valor.slice(3, 6) + ' ' + valor.slice(6);
            } else if (valor.length >= 4) {
                this.value = valor.slice(0, 3) + ' ' + valor.slice(3);
            } else {
                this.value = valor;
            }
        });
        
        // Validación antes de enviar
        document.getElementById('canchaForm').addEventListener('submit', function(e) {
            const telefono = document.getElementById('telefono').value.replace(/\s/g, '');
            
            if (telefono.length !== 10) {
                e.preventDefault();
                alert('El teléfono debe tener exactamente 10 dígitos\nEjemplo: 315 639 3235');
                return false;
            }
        });
        
        // Mostrar alerta de éxito
        <?php if ($mensaje_exito): ?>
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva Exitosa!',
                    html: '<?php echo $mensaje_exito; ?>',
                    confirmButtonColor: '#d4af37'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            }, 500);
        <?php endif; ?>
    </script>
    
</body