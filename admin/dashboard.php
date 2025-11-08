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

// Total de reservas hoy
$hoy = date('Y-m-d');
$query_hoy = "SELECT COUNT(*) as total FROM reservas WHERE fecha = '$hoy' AND estado != 'Cancelada'";
$result_hoy = mysqli_query($conexion, $query_hoy);
$stats['hoy'] = mysqli_fetch_assoc($result_hoy)['total'];

// Reservas pendientes
$query_pendientes = "SELECT COUNT(*) as total FROM reservas WHERE estado = 'Pendiente'";
$result_pendientes = mysqli_query($conexion, $query_pendientes);
$stats['pendientes'] = mysqli_fetch_assoc($result_pendientes)['total'];

// Total de clientes
$query_clientes = "SELECT COUNT(*) as total FROM clientes";
$result_clientes = mysqli_query($conexion, $query_clientes);
$stats['clientes'] = mysqli_fetch_assoc($result_clientes)['total'];

// Ingresos del mes
$mes_actual = date('Y-m');
$query_ingresos = "SELECT SUM(s.precio) as total 
                   FROM reservas r 
                   INNER JOIN servicios s ON r.id_servicio = s.id 
                   WHERE DATE_FORMAT(r.fecha, '%Y-%m') = '$mes_actual' 
                   AND r.estado = 'Completada'";
$result_ingresos = mysqli_query($conexion, $query_ingresos);
$stats['ingresos'] = mysqli_fetch_assoc($result_ingresos)['total'] ?? 0;

// Obtener reservas recientes (últimas 10)
$query_reservas = "SELECT r.*, c.nombre, c.telefono, s.nombre_servicio, s.precio
                   FROM reservas r
                   INNER JOIN clientes c ON r.id_cliente = c.id
                   INNER JOIN servicios s ON r.id_servicio = s.id
                   ORDER BY r.fecha DESC, r.hora DESC
                   LIMIT 20";
$result_reservas = mysqli_query($conexion, $query_reservas);
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
        
        /* Sidebar */
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
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        
        /* Top Bar */
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
        
        .top-bar h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-black);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--gold-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-black);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        /* Stats Cards */
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
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
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
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--primary-black);
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Reservas Table */
        .reservas-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-pendiente {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .badge-confirmada {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .badge-completada {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .badge-cancelada {
            background: #ffebee;
            color: #f44336;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 1rem;
            }
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
                    <span>Ver Reservas</span>
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
                <h1>Dashboard</h1>
                <small style="color: #666;">Bienvenido de nuevo, <?php echo $_SESSION['admin_nombre']; ?></small>
            </div>
            
            <div class="user-info">
                <div>
                    <strong style="display: block;"><?php echo $_SESSION['admin_nombre']; ?></strong>
                    <small style="color: #666;">Administrador</small>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_nombre'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <h3><?php echo $stats['hoy']; ?></h3>
                        <p>Reservas Hoy</p>
                    </div>
                    <div class="stat-card-icon blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <h3><?php echo $stats['pendientes']; ?></h3>
                        <p>Pendientes</p>
                    </div>
                    <div class="stat-card-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <h3><?php echo $stats['clientes']; ?></h3>
                        <p>Total Clientes</p>
                    </div>
                    <div class="stat-card-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div>
                        <h3>$<?php echo number_format($stats['ingresos'], 0, ',', '.'); ?></h3>
                        <p>Ingresos del Mes</p>
                    </div>
                    <div class="stat-card-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas Recientes -->
        <div class="reservas-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-check"></i> Reservas Recientes</h2>
                <a href="ver_reservas.php" class="btn btn-outline-dark">Ver Todas</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_reservas && mysqli_num_rows($result_reservas) > 0) {
                            while ($reserva = mysqli_fetch_assoc($result_reservas)) {
                                // Clase del badge según el estado
                                $badge_class = 'badge-' . strtolower($reserva['estado']);
                                
                                // Formatear fecha
                                $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
                                $hora_formateada = date('g:i A', strtotime($reserva['hora']));
                                ?>
                                <tr>
                                    <td><strong>#<?php echo $reserva['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($reserva['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['nombre_servicio']); ?></td>
                                    <td><?php echo $fecha_formateada; ?></td>
                                    <td><?php echo $hora_formateada; ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $reserva['estado']; ?></span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($reserva['estado'] === 'Pendiente'): ?>
                                                <button class="btn btn-success btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Confirmada')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] === 'Confirmada'): ?>
                                                <button class="btn btn-primary btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Completada')">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] !== 'Cancelada' && $reserva['estado'] !== 'Completada'): ?>
                                                <button class="btn btn-danger btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Cancelada')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center">No hay reservas registradas</td></tr>';
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
        // Función para cambiar el estado de una reserva
        function cambiarEstado(idReserva, nuevoEstado) {
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
                    // Enviar petición para cambiar estado
                    fetch('procesar_reserva.php', {
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
                                text: 'El estado se cambió correctamente',
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