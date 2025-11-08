<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include '../includes/conexion.php';

// Procesar acciones
$mensaje_exito = '';
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre_servicio']));
                $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
                $precio = floatval($_POST['precio']);
                $duracion = intval($_POST['duracion']);
                
                $query = "INSERT INTO servicios (nombre_servicio, descripcion, precio, duracion, activo) 
                         VALUES ('$nombre', '$descripcion', $precio, $duracion, 1)";
                
                if (mysqli_query($conexion, $query)) {
                    $mensaje_exito = 'Servicio agregado exitosamente';
                } else {
                    $mensaje_error = 'Error al agregar servicio: ' . mysqli_error($conexion);
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id']);
                $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre_servicio']));
                $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
                $precio = floatval($_POST['precio']);
                $duracion = intval($_POST['duracion']);
                
                $query = "UPDATE servicios 
                         SET nombre_servicio = '$nombre', descripcion = '$descripcion', 
                             precio = $precio, duracion = $duracion 
                         WHERE id = $id";
                
                if (mysqli_query($conexion, $query)) {
                    $mensaje_exito = 'Servicio actualizado exitosamente';
                } else {
                    $mensaje_error = 'Error al actualizar servicio: ' . mysqli_error($conexion);
                }
                break;
                
            case 'eliminar':
                $id = intval($_POST['id']);
                
                // Verificar si tiene reservas activas
                $checkQuery = "SELECT COUNT(*) as total FROM reservas WHERE id_servicio = $id AND estado != 'Completada' AND estado != 'Cancelada'";
                $checkResult = mysqli_query($conexion, $checkQuery);
                $check = mysqli_fetch_assoc($checkResult);
                
                if ($check['total'] > 0) {
                    $mensaje_error = 'No se puede eliminar el servicio porque tiene ' . $check['total'] . ' reserva(s) activa(s)';
                } else {
                    $deleteQuery = "DELETE FROM servicios WHERE id = $id";
                    if (mysqli_query($conexion, $deleteQuery)) {
                        $mensaje_exito = 'Servicio eliminado exitosamente';
                    } else {
                        $mensaje_error = 'Error al eliminar: ' . mysqli_error($conexion);
                    }
                }
                break;
                
            case 'toggle_activo':
                $id = intval($_POST['id']);
                $activo = intval($_POST['activo']) === 1 ? 0 : 1;
                
                $query = "UPDATE servicios SET activo = $activo WHERE id = $id";
                if (mysqli_query($conexion, $query)) {
                    $mensaje_exito = 'Estado del servicio actualizado a ' . ($activo ? 'Activo' : 'Inactivo');
                } else {
                    $mensaje_error = 'Error al actualizar: ' . mysqli_error($conexion);
                }
                break;
        }
    }
}

// Obtener servicios con estadísticas de reservas
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM reservas r WHERE r.id_servicio = s.id) as total_reservas,
          (SELECT COUNT(*) FROM reservas r WHERE r.id_servicio = s.id AND r.estado = 'Completada') as reservas_completadas,
          (SELECT COUNT(*) FROM reservas r WHERE r.id_servicio = s.id AND r.estado != 'Cancelada' AND r.fecha >= CURDATE()) as reservas_futuras
          FROM servicios s 
          ORDER BY s.nombre_servicio ASC";

$result = mysqli_query($conexion, $query);

// Estadísticas generales
$statsQuery = "SELECT 
               COUNT(*) as total,
               SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
               AVG(precio) as precio_promedio,
               MAX(precio) as precio_max,
               MIN(precio) as precio_min
               FROM servicios";
$statsResult = mysqli_query($conexion, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - Barber League Admin</title>
    
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
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
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
        .stat-icon.purple { background: #f3e5f5; color: #9C27B0; }
        .stat-icon.orange { background: #fff3e0; color: #FF9800; }
        
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .service-row {
            transition: all 0.3s;
        }
        
        .service-row:hover {
            background-color: rgba(212, 175, 55, 0.05);
        }
        
        .badge-popular {
            background: linear-gradient(135deg, #d4af37, #c49a2e);
            color: #1a1a1a;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .quick-stats {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .quick-stats .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
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
                <a href="clientes.php">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            <li>
                <a href="servicios.php" class="active">
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
                <i class="fas fa-scissors"></i> Gestión de Servicios
            </h1>
            <small style="color: #666;">Administra el catálogo de servicios de la barbería</small>
        </div>

        <!-- Mensajes -->
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

        <!-- Estadísticas Mejoradas -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['total']; ?></h3>
                            <p style="margin: 0; color: #666;">Total Servicios</p>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-scissors"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo $stats['activos']; ?></h3>
                            <p style="margin: 0; color: #666;">Servicios Activos</p>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 2rem; font-weight: 700; margin: 0;">$<?php echo number_format($stats['precio_promedio'], 0, ',', '.'); ?></h3>
                            <p style="margin: 0; color: #666;">Precio Promedio</p>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0;">
                                $<?php echo number_format($stats['precio_min'], 0, ',', '.'); ?> - $<?php echo number_format($stats['precio_max'], 0, ',', '.'); ?>
                            </h3>
                            <p style="margin: 0; color: #666;">Rango de Precios</p>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Servicios -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="margin: 0;">
                    <i class="fas fa-list"></i> Listado de Servicios (<?php echo mysqli_num_rows($result); ?>)
                </h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus"></i> Agregar Servicio
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Servicio</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Duración</th>
                            <th>Estadísticas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($servicio = mysqli_fetch_assoc($result)) {
                                $esPopular = $servicio['reservas_completadas'] >= 10;
                                ?>
                                <tr class="service-row">
                                    <td><strong>#<?php echo $servicio['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></strong>
                                        <?php if ($esPopular): ?>
                                            <br><span class="badge-popular">
                                                <i class="fas fa-fire"></i> Popular
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 40)) . '...'; ?></td>
                                    <td><strong style="color: #d4af37;">$<?php echo number_format($servicio['precio'], 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <i class="fas fa-clock"></i> <?php echo $servicio['duracion']; ?> min
                                    </td>
                                    <td>
                                        <div class="quick-stats">
                                            <span class="badge bg-primary" title="Total reservas">
                                                <i class="fas fa-calendar"></i> <?php echo $servicio['total_reservas']; ?>
                                            </span>
                                            <span class="badge bg-success" title="Completadas">
                                                <i class="fas fa-check"></i> <?php echo $servicio['reservas_completadas']; ?>
                                            </span>
                                            <span class="badge bg-info" title="Futuras">
                                                <i class="fas fa-clock"></i> <?php echo $servicio['reservas_futuras']; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($servicio['activo'] == 1): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-pause-circle"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="editarServicio(<?php echo htmlspecialchars(json_encode($servicio)); ?>)" 
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="toggleActivo(<?php echo $servicio['id']; ?>, <?php echo $servicio['activo']; ?>)" 
                                                    title="<?php echo $servicio['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="eliminarServicio(<?php echo $servicio['id']; ?>, '<?php echo htmlspecialchars($servicio['nombre_servicio']); ?>')" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay servicios registrados</p>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                                        <i class="fas fa-plus"></i> Agregar Primer Servicio
                                    </button>
                                  </td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Agregar -->
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #d4af37, #c49a2e); color: #1a1a1a;">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Agregar Nuevo Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="agregar">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-cut"></i> Nombre del Servicio <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre_servicio" 
                                   placeholder="Ej: Corte Clásico" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="descripcion" rows="3" 
                                      placeholder="Describe el servicio..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign"></i> Precio (COP) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="precio" 
                                       min="0" step="1000" placeholder="25000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock"></i> Duración (minutos) <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="duracion" required>
                                    <option value="15">15 minutos</option>
                                    <option value="30">30 minutos</option>
                                    <option value="45">45 minutos</option>
                                    <option value="60" selected>60 minutos (1 hora)</option>
                                    <option value="90">90 minutos (1.5 horas)</option>
                                    <option value="120">120 minutos (2 horas)</option>
                                    <option value="180">180 minutos (3 horas)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            El servicio se creará como <strong>Activo</strong> por defecto
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2196F3, #1976D2); color: white;">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="formEditar">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-cut"></i> Nombre del Servicio <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre_servicio" id="edit_nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign"></i> Precio (COP) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="precio" id="edit_precio" 
                                       min="0" step="1000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-clock"></i> Duración (minutos) <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="duracion" id="edit_duracion" required>
                                    <option value="15">15 minutos</option>
                                    <option value="30">30 minutos</option>
                                    <option value="45">45 minutos</option>
                                    <option value="60">60 minutos (1 hora)</option>
                                    <option value="90">90 minutos (1.5 horas)</option>
                                    <option value="120">120 minutos (2 horas)</option>
                                    <option value="180">180 minutos (3 horas)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function editarServicio(servicio) {
            document.getElementById('edit_id').value = servicio.id;
            document.getElementById('edit_nombre').value = servicio.nombre_servicio;
            document.getElementById('edit_descripcion').value = servicio.descripcion;
            document.getElementById('edit_precio').value = servicio.precio;
            document.getElementById('edit_duracion').value = servicio.duracion;
            
            new bootstrap.Modal(document.getElementById('modalEditar')).show();
        }
        
        function toggleActivo(id, activo) {
            const accion = activo === 1 ? 'desactivar' : 'activar';
            const nuevoEstado = activo === 1 ? 'Inactivo' : 'Activo';
            
            Swal.fire({
                title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} servicio?`,
                text: `El servicio quedará como ${nuevoEstado}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d4af37',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="accion" value="toggle_activo">
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="activo" value="${activo}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        function eliminarServicio(id, nombre) {
            Swal.fire({
                title: '¿Eliminar Servicio?',
                html: `
                    <div style="text-align: left; padding: 1rem;">
                        <p>Estás a punto de eliminar:</p>
                        <p style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                            <strong style="color: #d4af37;">${nombre}</strong>
                        </p>
                        <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                            <strong>Advertencia:</strong> Esta acción no se puede deshacer.<br>
                            Solo se puede eliminar si no tiene reservas activas.
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, Eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        
        // Auto-cerrar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
</body>
</html>