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
        // ✅ CORRECCIÓN: Calcular hora_fin correctamente
        $hora_inicio_obj = new DateTime($fecha . ' ' . $hora_inicio);
        $hora_fin_obj = clone $hora_inicio_obj;
        $hora_fin_obj->modify("+{$duracion} hours");
        $hora_fin = $hora_fin_obj->format('H:i:s');
        
        // ✅ CORRECCIÓN: Verificar disponibilidad CON LÓGICA CORRECTA
        $checkQuery = "SELECT id FROM reservas_cancha 
                      WHERE fecha = '$fecha' 
                      AND estado != 'Cancelada'
                      AND (
                          -- Nueva reserva comienza durante una existente
                          (hora_inicio <= '$hora_inicio' AND hora_fin > '$hora_inicio')
                          OR
                          -- Nueva reserva termina durante una existente
                          (hora_inicio < '$hora_fin' AND hora_fin >= '$hora_fin')
                          OR
                          -- Nueva reserva envuelve completamente una existente
                          ('$hora_inicio' <= hora_inicio AND '$hora_fin' >= hora_fin)
                      )";
        
        $checkResult = mysqli_query($conexion, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $mensaje_error = 'Este horario ya está reservado o tiene conflictos. Por favor, selecciona otro.';
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
            
            // ✅ CORRECCIÓN: Crear reserva con todos los campos
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
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        /* ✅ Inputs más visibles */
        .form-control {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border: 2px solid #e0e0e0 !important;
            color: #1a1a1a !important;
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: #d4af37 !important;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25) !important;
        }
        
        .form-label {
            color: #1a1a1a;
            font-weight: 600;
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
                    Césped sintético de última generación con iluminación LED
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
                            <strong>Césped Sintético Premium</strong> - Última generación
                        </li>
                        <li>
                            <i class="fas fa-lightbulb"></i>
                            <strong>Iluminación LED</strong> - Partidos nocturnos
                        </li>
                        <li>
                            <i class="fas fa-shield-alt"></i>
                            <strong>Redes Profesionales</strong> - Alta calidad
                        </li>
                        <li>
                            <i class="fas fa-door-open"></i>
                            <strong>Vestuarios Equipados</strong> - Con duchas
                        </li>
                        <li>
                            <i class="fas fa-parking"></i>
                            <strong>Parqueadero Gratis</strong> - Seguro
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
                            <i class="fas fa-users"></i> 10% descuento grupos 5+
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FORMULARIO DE RESERVA -->
    <section class="section" style="background: #f5f6fa; padding: 3rem 0;">
        <div class="container">
            <div class="text-center mb-4">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-calendar-check"></i> Reserva tu Cancha
                </h2>
            </div>
            
            <!-- Mensajes -->
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
                            <h4 style="margin-bottom: 1.5rem;">
                                <i class="fas fa-user"></i> Información de Contacto
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        Nombre Completo <span style="color: red;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           placeholder="Juan Pérez" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">
                                        Teléfono <span style="color: red;">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           placeholder="310 609 3237" maxlength="12" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo (Opcional)</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       placeholder="ejemplo@correo.com">
                            </div>
                            
                            <hr style="margin: 2rem 0;">
                            
                            <!-- Detalles de Reserva -->
                            <h4 style="margin-bottom: 1.5rem;">
                                <i class="fas fa-futbol"></i> Detalles de la Reserva
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha" class="form-label">
                                        Fecha <span style="color: red;">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="fecha" name="fecha" 
                                           placeholder="Selecciona fecha" readonly required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="hora_inicio" class="form-label">
                                        Hora de Inicio <span style="color: red;">*</span>
                                    </label>
                                    <select class="form-control" id="hora_inicio" name="hora_inicio" required>
                                        <option value="">-- Selecciona fecha primero --</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="duracion" class="form-label">
                                        Duración <span style="color: red;">*</span>
                                    </label>
                                    <select class="form-control" id="duracion" name="duracion" required>
                                        <option value="1">1 Hora - $50,000</option>
                                        <option value="2">2 Horas - $95,000</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="num_personas" class="form-label">
                                        Número de Personas
                                    </label>
                                    <input type="number" class="form-control" id="num_personas" 
                                           name="num_personas" min="1" max="20" value="10">
                                    <small class="text-muted">5+ personas = 10% descuento</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas (Opcional)</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3" 
                                          placeholder="Comentarios adicionales"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" 
                                    style="padding: 1rem; background: linear-gradient(135deg, #d4af37, #c49a2e); 
                                           border: none; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Confirmar Reserva
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
        // Selector de fecha
        flatpickr(document.getElementById('fecha'), {
            locale: 'es',
            minDate: 'today',
            maxDate: new Date().fp_incr(60),
            dateFormat: 'Y-m-d',
            onChange: function(selectedDates, dateStr) {
                if (dateStr) cargarHoras();
            }
        });
        
        // Generar horarios
        function cargarHoras() {
            const select = document.getElementById('hora_inicio');
            select.innerHTML = '<option value="">-- Selecciona hora --</option>';
            
            for (let h = 7; h <= 22; h++) {
                const hora = `${h.toString().padStart(2, '0')}:00:00`;
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                select.innerHTML += `<option value="${hora}">${h12}:00 ${ampm}</option>`;
            }
        }
        
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
        
        // Validar antes de enviar
        document.getElementById('canchaForm').addEventListener('submit', function(e) {
            const telefono = document.getElementById('telefono').value.replace(/\s/g, '');
            if (telefono.length !== 10) {
                e.preventDefault();
                alert('El teléfono debe tener 10 dígitos');
                return false;
            }
        });
        
        <?php if ($mensaje_exito): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Reserva Exitosa!',
            text: '<?php echo $mensaje_exito; ?>',
            confirmButtonColor: '#d4af37'
        });
        <?php endif; ?>
    </script>
</body>
</html>