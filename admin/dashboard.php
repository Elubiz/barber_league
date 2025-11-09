<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include '../includes/conexion.php';

// Obtener estadísticas
$stats = [];

// Total de reservas hoy (BARBERÍA + CANCHA)
$hoy = date('Y-m-d');

$query_hoy_barberia = "SELECT COUNT(*) as total FROM reservas WHERE fecha = '$hoy' AND estado != 'Cancelada'";
$result_hoy_barberia = mysqli_query($conexion, $query_hoy_barberia);
$barberia_hoy = mysqli_fetch_assoc($result_hoy_barberia)['total'];

$query_hoy_cancha = "SELECT COUNT(*) as total FROM reservas_cancha WHERE fecha = '$hoy' AND estado != 'Cancelada'";
$result_hoy_cancha = mysqli_query($conexion, $query_hoy_cancha);
$cancha_hoy = mysqli_fetch_assoc($result_hoy_cancha)['total'];

$stats['hoy'] = $barberia_hoy + $cancha_hoy;

// Reservas pendientes (BARBERÍA + CANCHA)
$query_pendientes_barberia = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'Pendiente'";
$result_pendientes_barberia = mysqli_query($conexion, $query_pendientes_barberia);
$pendientes_barberia = mysqli_fetch_assoc($result_pendientes_barberia)['total'];

$query_pendientes_cancha = "SELECT COUNT(*) as total FROM reservas_cancha WHERE estado = 'Pendiente'";
$result_pendientes_cancha = mysqli_query($conexion, $query_pendientes_cancha);
$pendientes_cancha = mysqli_fetch_assoc($result_pendientes_cancha)['total'];

$stats['pendientes'] = $pendientes_barberia + $pendientes_cancha;

// Total de clientes
$query_clientes = "SELECT COUNT(*) as total FROM clientes";
$result_clientes = mysqli_query($conexion, $query_clientes);
$stats['clientes'] = mysqli_fetch_assoc($result_clientes)['total'];

// Ingresos del mes (BARBERÍA + CANCHA)
$mes_actual = date('Y-m');
$query_ingresos_barberia = "SELECT SUM(s.precio) as total 
                   FROM reservas r 
                   INNER JOIN servicios s ON r.id_servicio = s.id 
                   WHERE DATE_FORMAT(r.fecha, '%Y-%m') = '$mes_actual' 
                   AND r.estado = 'Completada'";
$result_ingresos_barberia = mysqli_query($conexion, $query_ingresos_barberia);
$ingresos_barberia = mysqli_fetch_assoc($result_ingresos_barberia)['total'] ?? 0;

$query_ingresos_cancha = "SELECT SUM(precio) as total 
                          FROM reservas_cancha 
                          WHERE DATE_FORMAT(fecha, '%Y-%m') = '$mes_actual' 
                          AND estado = 'Completada'";
$result_ingresos_cancha = mysqli_query($conexion, $query_ingresos_cancha);
$ingresos_cancha = mysqli_fetch_assoc($result_ingresos_cancha)['total'] ?? 0;

$stats['ingresos'] = $ingresos_barberia + $ingresos_cancha;

// 🔥 CONSULTA CORREGIDA - Ordenar por ID descendente (más recientes primero)
$query_reservas_combinadas = "
    (SELECT 
        r.id,
        'Barbería' as tipo,
        c.nombre,
        c.telefono,
        s.nombre_servicio as servicio,
        s.precio,
        r.fecha,
        r.hora,
        r.estado,
        r.fecha_creacion,
        CONCAT(r.fecha, ' ', r.hora) as orden_datetime
    FROM reservas r
    INNER JOIN clientes c ON r.id_cliente = c.id
    INNER JOIN servicios s ON r.id_servicio = s.id)
    
    UNION ALL
    
    (SELECT 
        rc.id,
        'Cancha' as tipo,
        c.nombre,
        c.telefono,
        CONCAT('Cancha Sintética (', ROUND(rc.duracion/60, 1), 'h)') as servicio,
        rc.precio,
        rc.fecha,
        rc.hora_inicio as hora,
        rc.estado,
        rc.fecha_creacion,
        CONCAT(rc.fecha, ' ', rc.hora_inicio) as orden_datetime
    FROM reservas_cancha rc
    INNER JOIN clientes c ON rc.id_cliente = c.id)
    
    ORDER BY orden_datetime DESC
    LIMIT 20
";

$result_reservas = mysqli_query($conexion, $query_reservas_combinadas);

if (!$result_reservas) {
    die("❌ Error en consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Barber League</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card-icon.blue { background: #e3f2fd; color: #2196F3; }
        .stat-card-icon.green { background: #e8f5e9; color: #4CAF50; }
        .stat-card-icon.orange { background: #fff3e0; color: #FF9800; }
        .stat-card-icon.purple { background: #f3e5f5; color: #9C27B0; }
        
        .reservas-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-pendiente { background: #fff3e0; color: #ff9800; }
        .badge-confirmada { background: #e3f2fd; color: #2196f3; }
        .badge-completada { background: #e8f5e9; color: #4caf50; }
        .badge-cancelada { background: #ffebee; color: #f44336; }
        
        .badge-tipo-barberia {
            background: #e3f2fd;
            color: #2196f3;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-tipo-cancha {
            background: #e8f5e9;
            color: #4caf50;
            padding: 0.3rem 0.7rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-cut"></i>
            <h3>BARBER LEAGUE</h3>
            <small style="color: #999;">Panel Admin</small>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="ver_reservas.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Reservas Barbería</span>
                </a>
            </li>
            <li>
                <a href="ver_reservas_cancha.php">
                    <i class="fas fa-futbol"></i>
                    <span>Reservas Cancha</span>
                </a>
            </li>
            <li>
                <a href="registrar_reserva.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nueva Reserva</span>
                </a>
            </li>
            <li>
                <a href="clientes.php">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            <li>
                <a href="servicio.php">
                    <i class="fas fa-scissors"></i>
                    <span>Servicios</span>
                </a>
            </li>
            <li>
                <a href="../index.php" target="_blank">
                    <i class="fas fa-globe"></i>
                    <span>Ver Sitio Web</span>
                </a>
            </li>
            <li style="margin-top: 2rem;">
                <a href="logout.php" style="background: rgba(244, 67, 54, 0.2); color: #f44336;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h1 style="margin: 0; font-size: 1.8rem; font-weight: 700;">Dashboard</h1>
                <small style="color: #666;">Bienvenido de nuevo, <?php echo $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario']; ?></small>
            </div>
            
            <div class="user-info">
                <div>
                    <strong style="display: block;"><?php echo $_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario']; ?></strong>
                    <small style="color: #666;">Administrador</small>
                </div>
                <div style="width: 45px; height: 45px; border-radius: 50%; background: #d4af37; display: flex; align-items: center; justify-content: center; color: #1a1a1a; font-weight: 700; font-size: 1.2rem; margin-left: 1rem;">
                    <?php echo strtoupper(substr($_SESSION['admin_nombre'] ?? $_SESSION['admin_usuario'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['hoy']; ?></h3>
                        <p style="margin: 0; color: #666;">Reservas Hoy</p>
                    </div>
                    <div class="stat-card-icon blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['pendientes']; ?></h3>
                        <p style="margin: 0; color: #666;">Pendientes</p>
                    </div>
                    <div class="stat-card-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['clientes']; ?></h3>
                        <p style="margin: 0; color: #666;">Total Clientes</p>
                    </div>
                    <div class="stat-card-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin: 0;">$<?php echo number_format($stats['ingresos'], 0, ',', '.'); ?></h3>
                        <p style="margin: 0; color: #666;">Ingresos del Mes</p>
                    </div>
                    <div class="stat-card-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas Recientes COMBINADAS -->
        <div class="reservas-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 style="margin: 0;"><i class="fas fa-calendar-check"></i> Reservas Recientes (Barbería + Cancha)</h2>
                <div>
                    <a href="ver_reservas.php" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-cut"></i> Ver Barbería
                    </a>
                    <a href="ver_reservas_cancha.php" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-futbol"></i> Ver Cancha
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_reservas && mysqli_num_rows($result_reservas) > 0) {
                            while ($reserva = mysqli_fetch_assoc($result_reservas)) {
                                $badge_class = 'badge-' . strtolower($reserva['estado']);
                                $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
                                $hora_formateada = date('g:i A', strtotime($reserva['hora']));
                                
                                $tipo_badge = $reserva['tipo'] === 'Barbería' 
                                    ? '<span class="badge-tipo-barberia"><i class="fas fa-cut"></i> Barbería</span>'
                                    : '<span class="badge-tipo-cancha"><i class="fas fa-futbol"></i> Cancha</span>';
                                ?>
                                <tr>
                                    <td><strong>#<?php echo $reserva['id']; ?></strong></td>
                                    <td><?php echo $tipo_badge; ?></td>
                                    <td><?php echo htmlspecialchars($reserva['nombre']); ?></td>
                                    <td>
                                        <a href="https://wa.me/57<?php echo preg_replace('/\D/', '', $reserva['telefono']); ?>" 
                                           target="_blank"
                                           style="color: #25d366; text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i>
                                            <?php echo htmlspecialchars($reserva['telefono']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($reserva['servicio']); ?></td>
                                    <td><?php echo $fecha_formateada; ?></td>
                                    <td><?php echo $hora_formateada; ?></td>
                                    <td><strong>$<?php echo number_format($reserva['precio'], 0, ',', '.'); ?></strong></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $reserva['estado']; ?></span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($reserva['estado'] === 'Pendiente'): ?>
                                                <button class="btn btn-success btn-action btn-sm" 
                                                        onclick="cambiarEstado('<?php echo $reserva['tipo']; ?>', <?php echo $reserva['id']; ?>, 'Confirmada')"
                                                        title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] === 'Confirmada'): ?>
                                                <button class="btn btn-primary btn-action btn-sm" 
                                                        onclick="cambiarEstado('<?php echo $reserva['tipo']; ?>', <?php echo $reserva['id']; ?>, 'Completada')"
                                                        title="Completar">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] !== 'Cancelada' && $reserva['estado'] !== 'Completada'): ?>
                                                <button class="btn btn-danger btn-action btn-sm" 
                                                        onclick="cambiarEstado('<?php echo $reserva['tipo']; ?>', <?php echo $reserva['id']; ?>, 'Cancelada')"
                                                        title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i><br>
                                    <p class="text-muted">No hay reservas registradas</p>
                                  </td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function cambiarEstado(tipo, idReserva, nuevoEstado) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Cambiar estado a: ${nuevoEstado}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d4af37',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const archivo = tipo === 'Barbería' ? 'procesar_reserva.php' : 'procesar_reserva_cancha.php';
                    
                    fetch(archivo, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${idReserva}&accion=cambiar_estado&estado=${nuevoEstado}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: data.message,
                                confirmButtonColor: '#d4af37'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'No se pudo actualizar',
                                confirmButtonColor: '#d4af37'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al procesar la solicitud',
                            confirmButtonColor: '#d4af37'
                        });
                    });
                }
            });
        }
    </script>
    
</body>
</html>