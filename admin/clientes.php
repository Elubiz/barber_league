<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include '../includes/conexion.php';

// Procesar acciones (eliminar, editar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];
    
    if ($accion === 'eliminar') {
        // Verificar si tiene reservas activas
        $checkQuery = "SELECT COUNT(*) as total FROM reservas WHERE id_cliente = $id AND estado != 'Completada' AND estado != 'Cancelada'";
        $checkResult = mysqli_query($conexion, $checkQuery);
        $check = mysqli_fetch_assoc($checkResult);
        
        if ($check['total'] > 0) {
            $mensaje_error = 'No se puede eliminar el cliente porque tiene reservas activas';
        } else {
            $deleteQuery = "DELETE FROM clientes WHERE id = $id";
            if (mysqli_query($conexion, $deleteQuery)) {
                $mensaje_exito = 'Cliente eliminado exitosamente';
            }
        }
    }
}

// Búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($conexion, $_GET['busqueda']) : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';

// Construir query
$where = $busqueda ? "WHERE nombre LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%' OR correo LIKE '%$busqueda%'" : "";

switch ($orden) {
    case 'nombre':
        $orderBy = "ORDER BY nombre ASC";
        break;
    case 'reservas':
        $orderBy = "ORDER BY total_reservas DESC";
        break;
    default:
        $orderBy = "ORDER BY fecha_registro DESC";
}

$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM reservas r WHERE r.id_cliente = c.id AND r.estado != 'Cancelada') as reservas_activas,
          (SELECT COUNT(*) FROM reservas r WHERE r.id_cliente = c.id AND r.estado = 'Completada') as reservas_completadas
          FROM clientes c 
          $where 
          $orderBy";

$result = mysqli_query($conexion, $query);

// Estadísticas
$statsQuery = "SELECT 
               COUNT(*) as total,
               (SELECT COUNT(*) FROM clientes WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as nuevos_mes,
               (SELECT COUNT(DISTINCT id_cliente) FROM reservas WHERE fecha >= CURDATE()) as con_reservas_hoy
               FROM clientes";
$statsResult = mysqli_query($conexion, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Barber League Admin</title>
    
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
        
        .top-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.blue { background: #e3f2fd; color: #2196F3; }
        .stat-icon.green { background: #e8f5e9; color: #4CAF50; }
        .stat-icon.orange { background: #fff3e0; color: #FF9800; }
        
        .filters-bar {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .badge-vip {
            background: linear-gradient(135deg, #d4af37, #c49a2e);
            color: #1a1a1a;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-nuevo {
            background: #4CAF50;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
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
                <a href="dashboard.php">
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
                <a href="clientes.php" class="active">
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
            <h1 style="margin: 0; font-size: 1.8rem; font-weight: 700;">
                <i class="fas fa-users"></i> Gestión de Clientes
            </h1>
            <small style="color: #666;">Administra tu base de datos de clientes</small>
        </div>

        <!-- Mensajes -->
        <?php if (isset($mensaje_exito)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['total']; ?></h3>
                            <p style="margin: 0; color: #666;">Total Clientes</p>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['nuevos_mes']; ?></h3>
                            <p style="margin: 0; color: #666;">Nuevos este Mes</p>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['con_reservas_hoy']; ?></h3>
                            <p style="margin: 0; color: #666;">Reservas Hoy</p>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-bar">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-search"></i> Buscar Cliente</label>
                    <input type="text" 
                           class="form-control" 
                           name="busqueda" 
                           placeholder="Nombre, teléfono o correo..."
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-sort"></i> Ordenar por</label>
                    <select class="form-control" name="orden">
                        <option value="reciente" <?php echo $orden === 'reciente' ? 'selected' : ''; ?>>Más Recientes</option>
                        <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Nombre A-Z</option>
                        <option value="reservas" <?php echo $orden === 'reservas' ? 'selected' : ''; ?>>Más Reservas</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de Clientes -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="margin: 0;">
                    <i class="fas fa-list"></i> Listado de Clientes (<?php echo mysqli_num_rows($result); ?>)
                </h5>
                <button class="btn btn-success" onclick="window.location.href='registrar_reserva.php'">
                    <i class="fas fa-user-plus"></i> Registrar Nuevo
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Total Reservas</th>
                            <th>Activas</th>
                            <th>Completadas</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($cliente = mysqli_fetch_assoc($result)) {
                                // Determinar si es VIP (más de 10 reservas completadas)
                                $esVIP = $cliente['reservas_completadas'] >= 10;
                                
                                // Es nuevo si se registró en los últimos 7 días
                                $esNuevo = (strtotime($cliente['fecha_registro']) > strtotime('-7 days'));
                                ?>
                                <tr>
                                    <td><strong>#<?php echo $cliente['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                        <?php if ($esVIP): ?>
                                            <span class="badge-vip ms-2">
                                                <i class="fas fa-crown"></i> VIP
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($esNuevo): ?>
                                            <span class="badge-nuevo ms-2">
                                                <i class="fas fa-star"></i> Nuevo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/57<?php echo preg_replace('/\D/', '', $cliente['telefono']); ?>" 
                                           target="_blank" 
                                           style="color: #25d366; text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i>
                                            <?php echo htmlspecialchars($cliente['telefono']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($cliente['correo'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $cliente['total_reservas']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?php echo $cliente['reservas_activas']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $cliente['reservas_completadas']; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="verDetalles(<?php echo $cliente['id']; ?>)" 
                                                    title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="eliminarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre']); ?>')" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="9" class="text-center">No hay clientes registrados</td></tr>';
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
        function verDetalles(idCliente) {
            // Redirigir a página de detalles o abrir modal
            window.location.href = `ver_reservas.php?cliente=${idCliente}`;
        }
        
        function eliminarCliente(id, nombre) {
            Swal.fire({
                title: '¿Eliminar Cliente?',
                html: `Se eliminará a <strong>${nombre}</strong><br><small>Esta acción no se puede deshacer</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `clientes.php?accion=eliminar&id=${id}`;
                }
            });
        }
    </script>
    
</body>
</html>