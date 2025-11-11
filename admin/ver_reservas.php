<?php
session_start();

// Verificar si está logueado
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit;
}

include '../includes/conexion.php';

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$filtro_busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($conexion, $_GET['busqueda']) : '';

// Construir query con filtros
$where_clauses = [];

if ($filtro_estado !== 'todos') {
    $where_clauses[] = "r.estado = '" . mysqli_real_escape_string($conexion, $filtro_estado) . "'";
}

if ($filtro_fecha !== '') {
    $where_clauses[] = "r.fecha = '" . mysqli_real_escape_string($conexion, $filtro_fecha) . "'";
}

if ($filtro_busqueda !== '') {
    $where_clauses[] = "(c.nombre LIKE '%$filtro_busqueda%' OR c.telefono LIKE '%$filtro_busqueda%' OR s.nombre_servicio LIKE '%$filtro_busqueda%')";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

/// Línea ~34 (Nueva consulta con UNION ALL)
// La consulta ahora incluye ambas tablas (reservas y reservas_cancha)
// y normaliza los nombres de las columnas para que el código PHP las reconozca.

// En el archivo admin/ver_reservas.php (Líneas 36-60 aproximadamente)

$query = "
(SELECT 
    r.id, r.id_cliente, r.id_servicio, r.fecha, r.hora, r.estado, r.notas,
    c.nombre, c.telefono, c.correo,
    s.nombre_servicio, s.precio,
    'Barbería' AS tipo, r.fecha_creacion, r.hora AS hora_inicio -- Aliases para compatibilidad
FROM reservas r 
INNER JOIN clientes c ON r.id_cliente = c.id
INNER JOIN servicios s ON r.id_servicio = s.id)

UNION ALL

(SELECT 
    rc.id, rc.id_cliente, NULL AS id_servicio, rc.fecha, rc.hora_inicio AS hora, rc.estado, rc.notas,
    c.nombre, c.telefono, c.correo,
    'Cancha' AS nombre_servicio, rc.precio AS precio,
    'Cancha' AS tipo, rc.fecha_creacion, rc.hora_inicio
FROM reservas_cancha rc
INNER JOIN clientes c ON rc.id_cliente = c.id)

UNION ALL

-- 📌 NUEVA SECCIÓN: RESERVAS PÚBLICAS (citas_web)
(SELECT 
    cw.id, cw.id_cliente, cw.id_servicio, cw.fecha, cw.hora, cw.estado, cw.notas,
    c.nombre, c.telefono, c.correo,
    s.nombre_servicio, s.precio,
    'Web' AS tipo, cw.fecha_creacion, cw.hora AS hora_inicio
FROM citas_web cw 
INNER JOIN clientes c ON cw.id_cliente = c.id
INNER JOIN servicios s ON cw.id_servicio = s.id)

{$where_sql}
ORDER BY fecha DESC, hora_inicio DESC
";
    
$result = mysqli_query($conexion, $query);

// Contar reservas por estado
$stats_query = "SELECT estado, COUNT(*) as total FROM reservas GROUP BY estado";
$stats_result = mysqli_query($conexion, $stats_query);
$stats = [];
while ($row = mysqli_fetch_assoc($stats_result)) {
    $stats[$row['estado']] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reservas - Barber League Admin</title>
    
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
        
        /* Sidebar (mismo estilo del dashboard) */
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
        }
        
        .top-bar h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-black);
        }
        
        /* Filtros */
        .filtros-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .estado-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0.25rem;
            border: 2px solid transparent;
        }
        
        .estado-badge:hover {
            transform: translateY(-2px);
        }
        
        .estado-badge.active {
            border-color: var(--primary-black);
        }
        
        .estado-todos { background: #e0e0e0; color: #333; }
        .estado-pendiente { background: #fff3e0; color: #ff9800; }
        .estado-confirmada { background: #e3f2fd; color: #2196f3; }
        .estado-completada { background: #e8f5e9; color: #4caf50; }
        .estado-cancelada { background: #ffebee; color: #f44336; }
        
        /* Tabla */
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
                <a href="ver_reservas.php" class="active">
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
            <h1><i class="fas fa-calendar-check"></i> Todas las Reservas</h1>
            <small style="color: #666;">Gestiona y visualiza todas las reservas del sistema</small>
        </div>

        <!-- Filtros -->
        <div class="filtros-section">
            <h5 style="margin-bottom: 1rem;"><i class="fas fa-filter"></i> Filtrar Reservas</h5>
            
            <form method="GET" action="" id="filtrosForm">
                <div class="row g-3">
                    <!-- Filtro por Estado -->
                    <div class="col-md-12">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Por Estado:</label>
                        <div>
                            <span class="estado-badge estado-todos <?php echo $filtro_estado === 'todos' ? 'active' : ''; ?>" onclick="filtrarEstado('todos')">
                                <i class="fas fa-th"></i> Todos (<?php echo array_sum($stats); ?>)
                            </span>
                            <span class="estado-badge estado-pendiente <?php echo $filtro_estado === 'Pendiente' ? 'active' : ''; ?>" onclick="filtrarEstado('Pendiente')">
                                <i class="fas fa-clock"></i> Pendientes (<?php echo $stats['Pendiente'] ?? 0; ?>)
                            </span>
                            <span class="estado-badge estado-confirmada <?php echo $filtro_estado === 'Confirmada' ? 'active' : ''; ?>" onclick="filtrarEstado('Confirmada')">
                                <i class="fas fa-check"></i> Confirmadas (<?php echo $stats['Confirmada'] ?? 0; ?>)
                            </span>
                            <span class="estado-badge estado-completada <?php echo $filtro_estado === 'Completada' ? 'active' : ''; ?>" onclick="filtrarEstado('Completada')">
                                <i class="fas fa-check-double"></i> Completadas (<?php echo $stats['Completada'] ?? 0; ?>)
                            </span>
                            <span class="estado-badge estado-cancelada <?php echo $filtro_estado === 'Cancelada' ? 'active' : ''; ?>" onclick="filtrarEstado('Cancelada')">
                                <i class="fas fa-times"></i> Canceladas (<?php echo $stats['Cancelada'] ?? 0; ?>)
                            </span>
                        </div>
                        <input type="hidden" name="estado" id="estadoInput" value="<?php echo $filtro_estado; ?>">
                    </div>
                    
                    <!-- Filtro por Fecha -->
                    <div class="col-md-4">
                        <label for="fecha" class="form-label"><i class="fas fa-calendar"></i> Fecha:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $filtro_fecha; ?>">
                    </div>
                    
                    <!-- Búsqueda -->
                    <div class="col-md-6">
                        <label for="busqueda" class="form-label"><i class="fas fa-search"></i> Buscar:</label>
                        <input type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Nombre, teléfono o servicio..." value="<?php echo htmlspecialchars($filtro_busqueda); ?>">
                    </div>
                    
                    <!-- Botones -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 1rem;">
                <a href="ver_reservas.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-redo"></i> Limpiar Filtros
                </a>
            </div>
        </div>

        <!-- Tabla de Reservas -->
        <div class="reservas-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="margin: 0;">
                    <i class="fas fa-list"></i> 
                    Resultados: <?php echo mysqli_num_rows($result); ?> reserva(s)
                </h5>
                <button class="btn btn-success" onclick="window.location.href='registrar_reserva.php'">
                    <i class="fas fa-plus"></i> Nueva Reserva
                </button>
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
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($reserva = mysqli_fetch_assoc($result)) {
                                $badge_class = 'badge-' . strtolower($reserva['estado']);
                                $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
                                $hora_formateada = date('g:i A', strtotime($reserva['hora']));
                                ?>
                                <tr>
                                    <td><strong>#<?php echo $reserva['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reserva['nombre']); ?></strong>
                                        <?php if ($reserva['correo']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($reserva['correo']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/57<?php echo preg_replace('/\D/', '', $reserva['telefono']); ?>" target="_blank">
                                            <i class="fab fa-whatsapp" style="color: #25d366;"></i>
                                            <?php echo htmlspecialchars($reserva['telefono']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($reserva['nombre_servicio']); ?></td>
                                    <td><?php echo $fecha_formateada; ?></td>
                                    <td><?php echo $hora_formateada; ?></td>
                                    <td><strong>$<?php echo number_format($reserva['precio'], 0, ',', '.'); ?></strong></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $reserva['estado']; ?></span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($reserva['estado'] === 'Pendiente'): ?>
                                                <button class="btn btn-success btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Confirmada')" title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] === 'Confirmada'): ?>
                                                <button class="btn btn-primary btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Completada')" title="Completar">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($reserva['estado'] !== 'Cancelada' && $reserva['estado'] !== 'Completada'): ?>
                                                <button class="btn btn-warning btn-action btn-sm" onclick="cambiarEstado(<?php echo $reserva['id']; ?>, 'Cancelada')" title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-danger btn-action btn-sm" onclick="eliminarReserva(<?php echo $reserva['id']; ?>)" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="9" class="text-center">No se encontraron reservas con los filtros seleccionados</td></tr>';
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
        // Filtrar por estado
        function filtrarEstado(estado) {
            document.getElementById('estadoInput').value = estado;
            document.getElementById('filtrosForm').submit();
        }
        
        // Cambiar estado de reserva
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
                                text: data.message,
                                confirmButtonColor: '#d4af37'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#d4af37'
                            });
                        }
                    });
                }
            });
        }
        
        // Eliminar reserva
        function eliminarReserva(idReserva) {
            Swal.fire({
                title: '¿Eliminar reserva?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('procesar_reserva.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${idReserva}&accion=eliminar`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: data.message,
                                confirmButtonColor: '#d4af37'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#d4af37'
                            });
                        }
                    });
                }
            });
        }
    </script>
    
</body>
</html>