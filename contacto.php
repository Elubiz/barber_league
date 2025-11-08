<?php
// Procesar formulario de contacto
$mensaje_enviado = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $correo = htmlspecialchars(trim($_POST['correo']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    $asunto = htmlspecialchars(trim($_POST['asunto']));
    $mensaje = htmlspecialchars(trim($_POST['mensaje']));
    
    // Validación básica
    if (empty($nombre) || empty($correo) || empty($mensaje)) {
        $error = 'Por favor completa todos los campos obligatorios';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } else {
        // Aquí puedes:
        // 1. Enviar correo electrónico
        // 2. Guardar en base de datos
        // 3. Enviar notificación por WhatsApp
        
        // Por ahora, solo simulamos el éxito
        $mensaje_enviado = true;
        
        // OPCIONAL: Guardar en base de datos
        /*
        include 'includes/conexion.php';
        $query = "INSERT INTO mensajes_contacto (nombre, correo, telefono, asunto, mensaje, fecha) 
                  VALUES ('$nombre', '$correo', '$telefono', '$asunto', '$mensaje', NOW())";
        mysqli_query($conexion, $query);
        mysqli_close($conexion);
        */
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Barber League</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .contact-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1600') center/cover;
            min-height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 70px;
        }
        
        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: 100%;
            transition: all 0.3s;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);
        }
        
        .contact-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #d4af37, #c49a2e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #1a1a1a;
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: 400px;
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO SECTION -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Contáctanos</h1>
                <p style="font-size: 1.3rem; max-width: 700px; margin: 1.5rem auto;">
                    Estamos aquí para responder todas tus preguntas
                </p>
            </div>
        </div>
    </section>

    <!-- INFORMACIÓN DE CONTACTO -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Ubicación</h3>
                        <p style="color: #666; font-size: 1.1rem; line-height: 1.8;">
                            Calle 42 #23-45<br>
                            Barrio Centro<br>
                            Ibagué, Tolima<br>
                            Colombia
                        </p>
                        <a href="https://maps.google.com/?q=Ibague+Tolima+Colombia" 
                           target="_blank" 
                           class="btn btn-sm mt-3" 
                           style="background: #d4af37; color: #1a1a1a; border: none; padding: 0.5rem 1.5rem; border-radius: 20px;">
                            <i class="fas fa-directions"></i> Ver en Google Maps
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Teléfono</h3>
                        <p style="color: #666; font-size: 1.1rem; line-height: 1.8;">
                            <strong>Celular/WhatsApp:</strong><br>
                            <a href="tel:+573112345678" style="color: #d4af37; text-decoration: none;">
                                +57 311 234 5678
                            </a><br><br>
                            <strong>Fijo:</strong><br>
                            <a href="tel:+576082345678" style="color: #d4af37; text-decoration: none;">
                                (608) 234 5678
                            </a>
                        </p>
                        <a href="https://wa.me/573112345678?text=Hola%2C%20quiero%20información%20sobre%20Barber%20League" 
                           target="_blank" 
                           class="btn btn-sm mt-3" 
                           style="background: #25d366; color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 20px;">
                            <i class="fab fa-whatsapp"></i> Enviar WhatsApp
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="contact-card text-center">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Horarios</h3>
                        <p style="color: #666; font-size: 1.1rem; line-height: 1.8;">
                            <strong>Lunes - Viernes:</strong><br>
                            9:00 AM - 9:00 PM<br><br>
                            <strong>Sábados:</strong><br>
                            9:00 AM - 9:00 PM<br><br>
                            <strong>Domingos:</strong><br>
                            10:00 AM - 7:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FORMULARIO DE CONTACTO -->
    <section class="section" style="background: #f5f6fa;">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1.5rem;">
                        Envíanos un <span style="color: #d4af37;">Mensaje</span>
                    </h2>
                    <p style="font-size: 1.1rem; color: #666; line-height: 1.8; margin-bottom: 2rem;">
                        ¿Tienes alguna pregunta o comentario? Completa el formulario y te responderemos lo antes posible.
                    </p>
                    
                    <!-- Mensajes -->
                    <?php if ($mensaje_enviado): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> 
                            <strong>¡Mensaje enviado!</strong> Te contactaremos pronto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-container">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user"></i> Nombre Completo <span style="color: red;">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nombre" 
                                           name="nombre" 
                                           required
                                           placeholder="Juan Pérez">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="correo" class="form-label">
                                        <i class="fas fa-envelope"></i> Correo Electrónico <span style="color: red;">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="correo" 
                                           name="correo" 
                                           required
                                           placeholder="ejemplo@email.com">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">
                                        <i class="fas fa-phone"></i> Teléfono
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefono" 
                                           name="telefono"
                                           placeholder="311 234 5678">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="asunto" class="form-label">
                                        <i class="fas fa-tag"></i> Asunto
                                    </label>
                                    <select class="form-control" id="asunto" name="asunto">
                                        <option value="informacion">Información General</option>
                                        <option value="reserva">Consulta sobre Reservas</option>
                                        <option value="servicios">Servicios y Precios</option>
                                        <option value="cancha">Cancha Sintética</option>
                                        <option value="sugerencia">Sugerencia</option>
                                        <option value="queja">Queja o Reclamo</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje" class="form-label">
                                    <i class="fas fa-comment"></i> Mensaje <span style="color: red;">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="mensaje" 
                                          name="mensaje" 
                                          rows="5" 
                                          required
                                          placeholder="Escribe tu mensaje aquí..."></textarea>
                            </div>
                            
                            <button type="submit" 
                                    class="btn w-100" 
                                    style="background: linear-gradient(135deg, #d4af37, #c49a2e); color: #1a1a1a; border: none; padding: 1rem; font-weight: 600; font-size: 1.1rem; border-radius: 10px;">
                                <i class="fas fa-paper-plane"></i> Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <h3 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">Nuestra Ubicación</h3>
                    
                    <!-- Mapa de Google Maps (iframe) -->
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63704.42916427432!2d-75.28284555!3d4.438888899999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e38c576f1f0c9fb%3A0x65e892dd86dd0f87!2sIbagu%C3%A9%2C%20Tolima!5e0!3m2!1ses!2sco!4v1642442829123!5m2!1ses!2sco" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                    
                    <!-- Redes Sociales -->
                    <div style="background: white; padding: 2rem; border-radius: 15px; margin-top: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                        <h4 style="font-weight: 700; margin-bottom: 1.5rem; text-align: center;">Síguenos en Redes Sociales</h4>
                        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                            <a href="https://facebook.com/barberleague" 
                               target="_blank" 
                               style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #1877f2; color: white; border-radius: 50%; font-size: 1.8rem; text-decoration: none; transition: all 0.3s;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://instagram.com/barberleague" 
                               target="_blank" 
                               style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: white; border-radius: 50%; font-size: 1.8rem; text-decoration: none; transition: all 0.3s;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://tiktok.com/@barberleague" 
                               target="_blank" 
                               style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #000; color: white; border-radius: 50%; font-size: 1.8rem; text-decoration: none; transition: all 0.3s;">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="https://wa.me/573112345678" 
                               target="_blank" 
                               style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #25d366; color: white; border-radius: 50%; font-size: 1.8rem; text-decoration: none; transition: all 0.3s;">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PREGUNTAS FRECUENTES -->
    <section class="section">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 3rem;">
                Preguntas <span style="color: #d4af37;">Frecuentes</span>
            </h2>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item" style="border: none; margin-bottom: 1rem; border-radius: 10px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="background: #f5f6fa; font-weight: 600;">
                            ¿Necesito reserva previa?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body" style="background: white;">
                            Sí, recomendamos hacer reserva previa para garantizar tu horario preferido. Puedes reservar online o llamándonos directamente.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item" style="border: none; margin-bottom: 1rem; border-radius: 10px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="background: #f5f6fa; font-weight: 600;">
                            ¿Qué métodos de pago aceptan?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body" style="background: white;">
                            Aceptamos efectivo, tarjetas de crédito/débito, transferencias bancarias y Nequi/Daviplata.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item" style="border: none; margin-bottom: 1rem; border-radius: 10px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="background: #f5f6fa; font-weight: 600;">
                            ¿Puedo cancelar o reprogramar mi cita?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body" style="background: white;">
                            Sí, puedes cancelar o reprogramar con al menos 24 horas de anticipación sin costo alguno.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item" style="border: none; margin-bottom: 1rem; border-radius: 10px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="background: #f5f6fa; font-weight: 600;">
                            ¿La cancha incluye balón y chalecos?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body" style="background: white;">
                            Sí, incluimos balón, chalecos y acceso a vestuarios con duchas sin costo adicional.
                        </div>
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
    <script src="assets/js/main.js"></script>
    
    <?php if ($mensaje_enviado): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Mensaje Enviado!',
            text: 'Gracias por contactarnos. Te responderemos pronto.',
            confirmButtonColor: '#d4af37'
        });
    </script>
    <?php endif; ?>
    
</body>
</html>