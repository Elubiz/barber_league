<?php
session_start();

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include '../includes/conexion.php';

$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $telefono = mysqli_real_escape_string($conexion, str_replace(' ', '', trim($_POST['telefono'])));
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';
    $id_servicio = intval($_POST['id_servicio']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, trim($_POST['notas'])) : '';
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
    
    if (empty($nombre) || empty($telefono) || empty($id_servicio) || empty($fecha) || empty($hora)) {
        $mensaje_error = 'Por favor completa todos los campos obligatorios';
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $mensaje_error = 'El teléfono debe tener exactamente 10 dígitos';
    } else {
        $checkQuery = "SELECT id FROM reservas WHERE fecha = '$fecha' AND hora = '$hora' AND estado != 'Cancelada'";
        $checkResult = mysqli_query($conexion, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $mensaje_advertencia = 'Advertencia: Ya existe una reserva en este horario';
        }
        
        $clienteQuery = "SELECT id FROM clientes WHERE telefono = '$telefono' LIMIT 1";
        $clienteResult = mysqli_query($conexion, $clienteQuery);
        
        if (mysqli_num_rows($clienteResult) > 0) {
            $cliente = mysqli_fetch_assoc($clienteResult);
            $id_cliente = $cliente['id'];
            
            $updateCliente = "UPDATE clientes SET nombre = '$nombre', correo = '$correo' WHERE id = $id_cliente";
            mysqli_query($conexion, $updateCliente);
        } else {
            $insertCliente = "INSERT INTO clientes (nombre, telefono, correo) VALUES ('$nombre', '$telefono', '$correo')";
            if (mysqli_query($conexion, $insertCliente)) {
                $id_cliente = mysqli_insert_id($conexion);
            } else {
                $mensaje_error = 'Error al registrar el cliente: ' . mysqli_error($conexion);
                $id_cliente = 0;
            }
        }
        
        if ($id_cliente > 0) {
            $insertReserva = "INSERT INTO reservas (id_cliente, id_servicio, fecha, hora, notas, estado) 
                              VALUES ($id_cliente, $id_servicio, '$fecha', '$hora', '$notas', '$estado')";
            
            if (mysqli_query($conexion, $insertReserva)) {
                $id_reserva = mysqli_insert_id($conexion);
                $mensaje_exito = "Reserva #$id_reserva creada exitosamente para $nombre";
            } else {
                $mensaje_error = 'Error al crear reserva: ' . mysqli_error($conexion);
            }
        }
    }
}

$serviciosQuery = "SELECT id, nombre_servicio, precio, duracion FROM servicios WHERE activo = 1 ORDER BY nombre_servicio";
$serviciosResult = mysqli_query($conexion, $serviciosQuery);

$clientesQuery = "SELECT id, nombre, telefono FROM clientes ORDER BY fecha_registro DESC LIMIT 50";
$clientesResult = mysqli_query($conexion, $clientesQuery);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Reserva - Barber League Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --gold-primary: #d4af37;
            --primary-black: #1a1a1a;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-black) 0%, #2d2d2d 100%);
            padding: 1.5rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-logo {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }
        
        .sidebar-logo i {
            font-size: 3rem;
            color: var(--gold-primary);
            margin-bottom: 0.5rem;
        }
        
        .sidebar-logo h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gold-primary);
            margin: 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--gold-primary);
            color: var(--primary-black);
        }
        
        .sidebar-menu a i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-section {
            border-left: 4px solid var(--gold-primary);
            padding-left: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .quick-select {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .quick-btn {
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .quick-btn:hover {
            border-color: var(--gold-primary);
            background: rgba(212, 175, 55, 0.1);
        }
        
        .quick-btn.selected {
            border-color: var(--gold-primary);
            background: var(--gold-primary);
            color: white;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-cut"></i>
            <h3>BARBER LEAGUE</h3>
            <small style="color: #999;">Panel Admin</small>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li><a href="ver_reservas.php"><i class="fas fa-calendar-check"></i><span>Ver Reservas</span></a></li>
            <li><a href="registrar_reserva.php" class="active"><i class="fas fa-plus-circle"></i><span>Nueva Reserva</span></a></li>
            <li><a href="clientes.php"><i class="fas fa-users"></i><span>Clientes</span></a></li>
            <li><a href="servicio.php"><i class="fas fa-scissors"></i><span>Servicios</span></a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i><span>Ver Sitio Web</span></a></li>
            <li style="margin-top: 2rem;">
                <a href="logout.php" style="background: rgba(244, 67, 54, 0.2); color: #f44336;">
                    <i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1 style="margin: 0; font-size: 1.8rem; font-weight: 700;">
                <i class="fas fa-plus-circle"></i> Registrar Nueva Reserva
            </h1>
            <small style="color: #666;">Crea una reserva manualmente desde el panel administrativo</small>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_advertencia)): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_advertencia; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form id="reservaForm" method="POST" action="">
                <div class="form-section">
                    <h4 style="margin-bottom: 1.5rem; color: var(--gold-primary);">
                        <i class="fas fa-user"></i> 1. Información del Cliente
                    </h4>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-search"></i> Buscar Cliente Existente (Opcional)
                        </label>
                        <select class="form-control" id="cliente_existente" onchange="cargarDatosCliente()">
                            <option value="">-- Nuevo cliente --</option>
                            <?php
                            if ($clientesResult && mysqli_num_rows($clientesResult) > 0) {
                                while ($cliente = mysqli_fetch_assoc($clientesResult)) {
                                    echo "<option value='" . $cliente['id'] . "' data-nombre='" . htmlspecialchars($cliente['nombre']) . "' data-telefono='" . $cliente['telefono'] . "'>";
                                    echo htmlspecialchars($cliente['nombre']) . " - " . $cliente['telefono'];
                                    echo "</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="text-muted">Si seleccionas un cliente, sus datos se autocompletarán</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   placeholder="Juan Pérez" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="310 609 3237" maxlength="12" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Correo (Opcional)
                            </label>
                            <input type="email" class="form-control" id="correo" name="correo" 
                                   placeholder="ejemplo@email.com">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 style="margin-bottom: 1.5rem; color: var(--gold-primary);">
                        <i class="fas fa-scissors"></i> 2. Seleccionar Servicio
                    </h4>
                    
                    <div class="quick-select" id="servicios-grid">
                        <?php
                        mysqli_data_seek($serviciosResult, 0);
                        while ($servicio = mysqli_fetch_assoc($serviciosResult)) {
                            ?>
                            <div class="quick-btn" onclick="seleccionarServicio(<?php echo $servicio['id']; ?>, this)">
                                <strong><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></strong><br>
                                <small style="color: #666;">
                                    <i class="fas fa-clock"></i> <?php echo $servicio['duracion']; ?> min
                                </small><br>
                                <strong style="color: var(--gold-primary);">
                                    $<?php echo number_format($servicio['precio'], 0, ',', '.'); ?>
                                </strong>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <input type="hidden" id="id_servicio" name="id_servicio" required>
                    <small class="text-danger" id="servicio-error" style="display: none;">
                        Por favor selecciona un servicio
                    </small>
                </div>

                <div class="form-section">
                    <h4 style="margin-bottom: 1.5rem; color: var(--gold-primary);">
                        <i class="fas fa-calendar"></i> 3. Fecha y Hora
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i> Fecha <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="fecha" name="fecha" 
                                   placeholder="Selecciona una fecha" readonly required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Hora <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="hora" name="hora" required>
                                <option value="">-- Selecciona fecha primero --</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-info-circle"></i> Estado <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" name="estado" required>
                                <option value="Confirmada" selected>Confirmada</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Completada">Completada</option>
                            </select>
                            <small class="text-muted">Recomendado: Confirmada</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 style="margin-bottom: 1.5rem; color: var(--gold-primary);">
                        <i class="fas fa-comment"></i> 4. Notas Adicionales (Opcional)
                    </h4>
                    
                    <div class="mb-3">
                        <textarea class="form-control" name="notas" rows="3" 
                                  placeholder="Ej: Cliente prefiere barbero Juan, corte específico, etc."></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-between">
                    <a href="ver_reservas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Reservas
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const fechaInput = document.getElementById('fecha');
        const horaSelect = document.getElementById('hora');
        
        flatpickr(fechaInput, {
            locale: 'es',
            minDate: 'today',
            maxDate: new Date().fp_incr(90),
            dateFormat: 'Y-m-d',
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    cargarHorasDisponibles(dateStr);
                }
            }
        });
        
        function cargarHorasDisponibles(fecha) {
            horaSelect.innerHTML = '<option value="">Cargando...</option>';
            
            const horarios = [];
            for (let hora = 9; hora <= 20; hora++) {
                for (let minuto of ['00', '30']) {
                    if (hora === 20 && minuto === '30') break;
                    const horaStr = `${hora.toString().padStart(2, '0')}:${minuto}:00`;
                    horarios.push(horaStr);
                }
            }
            
            horaSelect.innerHTML = '<option value="">-- Selecciona una hora --</option>';
            horarios.forEach(hora => {
                const option = document.createElement('option');
                option.value = hora;
                option.textContent = convertirA12Horas(hora);
                horaSelect.appendChild(option);
            });
        }
        
        function convertirA12Horas(hora24) {
            const [hora] = hora24.split(':');
            let h = parseInt(hora);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return `${h}:00 ${ampm}`;
        }
        
        let servicioSeleccionado = null;
        function seleccionarServicio(id, element) {
            document.querySelectorAll('.quick-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            element.classList.add('selected');
            document.getElementById('id_servicio').value = id;
            document.getElementById('servicio-error').style.display = 'none';
            servicioSeleccionado = id;
        }
        
        function cargarDatosCliente() {
            const select = document.getElementById('cliente_existente');
            const option = select.options[select.selectedIndex];
            
            if (select.value) {
                document.getElementById('nombre').value = option.dataset.nombre;
                document.getElementById('telefono').value = formatearTelefono(option.dataset.telefono);
            } else {
                document.getElementById('nombre').value = '';
                document.getElementById('telefono').value = '';
                document.getElementById('correo').value = '';
            }
        }
        
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
        
        function formatearTelefono(tel) {
            tel = tel.replace(/\D/g, '');
            if (tel.length >= 7) {
                return tel.slice(0, 3) + ' ' + tel.slice(3, 6) + ' ' + tel.slice(6);
            }
            return tel;
        }
        
        document.getElementById('reservaForm').addEventListener('submit', function(e) {
            const telefono = document.getElementById('telefono').value.replace(/\s/g, '');
            
            if (!servicioSeleccionado) {
                e.preventDefault();
                document.getElementById('servicio-error').style.display = 'block';
                Swal.fire({
                    icon: 'error',
                    title: 'Servicio no seleccionado',
                    text: 'Por favor selecciona un servicio',
                    confirmButtonColor: '#d4af37'
                });
                return false;
            }
            
            if (telefono.length !== 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Teléfono inválido',
                    text: 'El teléfono debe tener exactamente 10 dígitos',
                    confirmButtonColor: '#d4af37'
                });
                return false;
            }
        });
        
        <?php if ($mensaje_exito): ?>
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva Creada!',
                    text: '<?php echo $mensaje_exito; ?>',
                    confirmButtonColor: '#d4af37'
                }).then(() => {
                    window.location.href = 'ver_reservas.php';
                });
            }, 500);
        <?php endif; ?>
    </script>
    
</body>
</html>