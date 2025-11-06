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
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- PÁGINA DE RESERVA -->
    <section class="form-section section">
        <div class="container">
            <div class="section-title">
                <h2>Reserva Tu Cita</h2>
                <p>Completa el formulario y asegura tu lugar</p>
            </div>
            
            <div class="form-container">
                <form id="reservaForm" method="POST">
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
                                placeholder="3112345678"
                                maxlength="10"
                                required
                            >
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
                            
                            $query = "SELECT id, nombre_servicio, precio FROM servicios ORDER BY nombre_servicio";
                            $result = mysqli_query($conexion, $query);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($servicio = mysqli_fetch_assoc($result)) {
                                    $selected = (isset($_GET['servicio']) && $_GET['servicio'] == $servicio['id']) ? 'selected' : '';
                                    echo "<option value='{$servicio['id']}' {$selected}>{$servicio['nombre_servicio']} - $" . number_format($servicio['precio'], 0, ',', '.') . "</option>";
                                }
                            } else {
                                // Opciones de ejemplo
                                echo "<option value='1'>Corte Clásico - $25,000</option>";
                                echo "<option value='2'>Barba Profesional - $15,000</option>";
                                echo "<option value='3'>Corte + Barba - $35,000</option>";
                                echo "<option value='4'>Trenzas - $30,000</option>";
                                echo "<option value='5'>Dreadlocks - $80,000</option>";
                                echo "<option value='6'>Masaje Capilar - $20,000</option>";
                            }
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
                                <option value="">-- Selecciona hora --</option>
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
                        ></textarea>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="assets/js/main.js"></script>
    
</body>
</html>

<?php
// ============================================
// PROCESAMIENTO DEL FORMULARIO (PHP)
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    include 'includes/conexion.php';
    
    // Obtener datos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, $_POST['correo']) : '';
    $id_servicio = intval($_POST['id_servicio']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conexion, $_POST['hora']);
    $notas = isset($_POST['notas']) ? mysqli_real_escape_string($conexion, $_POST['notas']) : '';
    
    // Validación básica
    if (empty($nombre) || empty($telefono) || empty($id_servicio) || empty($fecha) || empty($hora)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos obligatorios deben ser completados'
        ]);
        exit;
    }
    
    // Verificar disponibilidad
    $checkQuery = "SELECT id FROM reservas WHERE fecha = '$fecha' AND hora = '$hora'";
    $checkResult = mysqli_query($conexion, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Este horario ya está reservado. Por favor, seleccione otro.'
        ]);
        exit;
    }
    
    // Insertar o actualizar cliente
    $clienteQuery = "SELECT id FROM clientes WHERE telefono = '$telefono' LIMIT 1";
    $clienteResult = mysqli_query($conexion, $clienteQuery);
    
    if (mysqli_num_rows($clienteResult) > 0) {
        // Cliente existe, actualizar datos
        $cliente = mysqli_fetch_assoc($clienteResult);
        $id_cliente = $cliente['id'];
        
        $updateCliente = "UPDATE clientes SET nombre = '$nombre', correo = '$correo' WHERE id = $id_cliente";
        mysqli_query($conexion, $updateCliente);
    } else {
        // Cliente nuevo, insertar
        $insertCliente = "INSERT INTO clientes (nombre, telefono, correo) VALUES ('$nombre', '$telefono', '$correo')";
        if (mysqli_query($conexion, $insertCliente)) {
            $id_cliente = mysqli_insert_id($conexion);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el cliente'
            ]);
            exit;
        }
    }
    
    // Insertar reserva
    $insertReserva = "INSERT INTO reservas (id_cliente, id_servicio, fecha, hora, notas, estado) 
                      VALUES ($id_cliente, $id_servicio, '$fecha', '$hora', '$notas', 'Pendiente')";
    
    if (mysqli_query($conexion, $insertReserva)) {
        echo json_encode([
            'success' => true,
            'message' => 'Reserva realizada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la reserva: ' . mysqli_error($conexion)
        ]);
    }
    
    mysqli_close($conexion);
    exit;
}
?>