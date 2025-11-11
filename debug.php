<?php
include 'includes/conexion.php';

echo "<h2>🔍 DIAGNÓSTICO DE RESERVAS</h2>";

// 1. Contar reservas de barbería
$query_barberia = "SELECT COUNT(*) as total FROM reservas";
$result_barberia = mysqli_query($conexion, $query_barberia);
$count_barberia = mysqli_fetch_assoc($result_barberia)['total'];

echo "<h3>📊 Reservas de Barbería: $count_barberia</h3>";

// Mostrar últimas 5 reservas de barbería
$query_ultimas_barberia = "SELECT r.id, r.fecha, r.hora, r.estado, c.nombre, s.nombre_servicio, r.fecha_creacion
                           FROM reservas r
                           INNER JOIN clientes c ON r.id_cliente = c.id
                           INNER JOIN servicios s ON r.id_servicio = s.id
                           ORDER BY r.id DESC
                           LIMIT 5";
$result_ultimas_barberia = mysqli_query($conexion, $query_ultimas_barberia);

if ($result_ultimas_barberia && mysqli_num_rows($result_ultimas_barberia) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Cliente</th><th>Servicio</th><th>Fecha</th><th>Hora</th><th>Estado</th><th>Creada</th></tr>";
    while ($row = mysqli_fetch_assoc($result_ultimas_barberia)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['nombre_servicio']}</td>";
        echo "<td>{$row['fecha']}</td>";
        echo "<td>{$row['hora']}</td>";
        echo "<td>{$row['estado']}</td>";
        echo "<td>{$row['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No hay reservas de barbería</p>";
}

echo "<hr>";

// 2. Contar reservas de cancha
$query_cancha = "SELECT COUNT(*) as total FROM reservas_cancha";
$result_cancha = mysqli_query($conexion, $query_cancha);
$count_cancha = mysqli_fetch_assoc($result_cancha)['total'];

echo "<h3>⚽ Reservas de Cancha: $count_cancha</h3>";

// Mostrar últimas 5 reservas de cancha
$query_ultimas_cancha = "SELECT rc.id, rc.fecha, rc.hora_inicio, rc.hora_fin, rc.estado, c.nombre, rc.precio, rc.fecha_creacion
                         FROM reservas_cancha rc
                         INNER JOIN clientes c ON rc.id_cliente = c.id
                         ORDER BY rc.id DESC
                         LIMIT 5";
$result_ultimas_cancha = mysqli_query($conexion, $query_ultimas_cancha);

if ($result_ultimas_cancha && mysqli_num_rows($result_ultimas_cancha) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th><th>Precio</th><th>Estado</th><th>Creada</th></tr>";
    while ($row = mysqli_fetch_assoc($result_ultimas_cancha)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['fecha']}</td>";
        echo "<td>{$row['hora_inicio']}</td>";
        echo "<td>{$row['hora_fin']}</td>";
        echo "<td>\${$row['precio']}</td>";
        echo "<td>{$row['estado']}</td>";
        echo "<td>{$row['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No hay reservas de cancha</p>";
}

echo "<hr>";

// 3. Probar la consulta del dashboard
echo "<h3>🎯 Probando consulta del Dashboard:</h3>";

$query_dashboard = "
    (SELECT 
        r.id,
        'Barbería' as tipo,
        c.nombre,
        c.telefono,
        s.nombre_servicio as servicio,
        s.precio,
        r.fecha,
        COALESCE(r.hora, '00:00:00') as hora,
        r.estado,
        r.fecha_creacion
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
        COALESCE(rc.hora_inicio, '00:00:00') as hora,
        rc.estado,
        rc.fecha_creacion
    FROM reservas_cancha rc
    INNER JOIN clientes c ON rc.id_cliente = c.id)
    
    ORDER BY fecha DESC, hora DESC
    LIMIT 10
";

$result_dashboard = mysqli_query($conexion, $query_dashboard);

if (!$result_dashboard) {
    echo "<p style='color: red; font-weight: bold;'>❌ ERROR EN CONSULTA: " . mysqli_error($conexion) . "</p>";
} else {
    $num_resultados = mysqli_num_rows($result_dashboard);
    echo "<p style='color: green; font-weight: bold;'>✅ Consulta exitosa. Resultados: $num_resultados</p>";
    
    if ($num_resultados > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>Cliente</th><th>Servicio</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>";
        while ($row = mysqli_fetch_assoc($result_dashboard)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td><strong>{$row['tipo']}</strong></td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['servicio']}</td>";
            echo "<td>{$row['fecha']}</td>";
            echo "<td>{$row['hora']}</td>";
            echo "<td>{$row['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ La consulta no devolvió resultados</p>";
    }
}

echo "<hr>";
echo "<h3>✅ Diagnóstico completo</h3>";
echo "<p><a href='admin/dashboard.php'>🔗 Ir al Dashboard Admin</a></p>";
echo "<p><a href='reservar.php'>🔗 Hacer nueva reserva (Barbería)</a></p>";
echo "<p><a href='cancha.php'>🔗 Hacer nueva reserva (Cancha)</a></p>";

mysqli_close($conexion);
?>