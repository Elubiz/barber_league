<?php
// 1️⃣ Incluir la conexión a la base de datos
require_once("includes/conexion.php");

// 2️⃣ Consultar los servicios antes del HTML
$query = "SELECT * FROM servicios LIMIT 6";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barber League - Barbería profesional con cancha sintética en Ibagué">
    <title>Barber League - Barbería & Cancha Sintética</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- HEADER / NAVBAR -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-cut"></i> BARBER LEAGUE
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="servicios.php">Servicios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cancha.php">Cancha</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contacto.php">Contacto</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-reservar" href="reservar.php">Reservar Cita</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Estilo y Deporte en <span class="gold-text">un Solo Lugar</span></h1>
                <p>Barber League combina los mejores servicios de barbería con una cancha sintética de alta calidad. Tu experiencia completa te espera.</p>
                <div class="hero-buttons">
                    <a href="reservar.php" class="btn-primary-custom">Reservar Ahora</a>
                    <a href="#servicios" class="btn-secondary-custom">Ver Servicios</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ¿POR QUÉ ELEGIRNOS? -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>¿Por Qué Elegirnos?</h2>
                <p>Ofrecemos una experiencia única que combina profesionalismo y diversión</p>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center">
                        <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=800'); background-size: cover; background-position: center;">
                        </div>
                        <div class="service-card-body">
                            <i class="fas fa-user-tie fa-3x text-gold mb-3"></i>
                            <h3 class="service-card-title">Profesionales Expertos</h3>
                            <p class="service-card-description">Barberos certificados con años de experiencia en cortes modernos y clásicos</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center">
                        <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800'); background-size: cover; background-position: center;">
                        </div>
                        <div class="service-card-body">
                            <i class="fas fa-futbol fa-3x text-gold mb-3"></i>
                            <h3 class="service-card-title">Cancha Sintética</h3>
                            <p class="service-card-description">Césped sintético de última generación para tu mejor experiencia deportiva</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="service-card text-center">
                        <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1521791136064-7986c2920216?w=800'); background-size: cover; background-position: center;">
                        </div>
                        <div class="service-card-body">
                            <i class="fas fa-star fa-3x text-gold mb-3"></i>
                            <h3 class="service-card-title">Ambiente Premium</h3>
                            <p class="service-card-description">Instalaciones modernas y cómodas para tu máxima satisfacción</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICIOS DESTACADOS -->
    <section id="servicios" class="section" style="background: var(--gray-light);">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Servicios</h2>
                <p>Tratamientos de barbería profesional para lucir tu mejor versión</p>
            </div>
            
            <div class="row">
                <?php
            
                
                // Consultar servicios destacados (primeros 6)
                $query = "SELECT * FROM servicios LIMIT 6";
                $result = mysqli_query($conexion, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($servicio = mysqli_fetch_assoc($result)) {
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="service-card">
                                <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=800'); background-size: cover; background-position: center;">
                                </div>
                                <div class="service-card-body">
                                    <h3 class="service-card-title"><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></h3>
                                    <p class="service-card-description"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                                    <p class="service-card-price">$<?php echo number_format($servicio['precio'], 0, ',', '.'); ?></p>
                                    <a href="reservar.php?servicio=<?php echo $servicio['id']; ?>" class="btn-service">Reservar</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Servicios de ejemplo si no hay datos en la BD
                    $serviciosEjemplo = [
                        ['nombre' => 'Corte Clásico', 'desc' => 'Corte tradicional con tijera y máquina', 'precio' => 25000],
                        ['nombre' => 'Barba Profesional', 'desc' => 'Perfilado y arreglo de barba con navaja', 'precio' => 15000],
                        ['nombre' => 'Corte + Barba', 'desc' => 'Combo completo para lucir impecable', 'precio' => 35000],
                        ['nombre' => 'Trenzas', 'desc' => 'Diseños creativos y duraderos', 'precio' => 30000],
                        ['nombre' => 'Dreadlocks', 'desc' => 'Mantenimiento y nuevas dreadlocks', 'precio' => 80000],
                        ['nombre' => 'Masaje Capilar', 'desc' => 'Tratamiento relajante para el cuero cabelludo', 'precio' => 20000]
                    ];
                    
                    foreach ($serviciosEjemplo as $serv) {
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="service-card">
                                <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=800'); background-size: cover; background-position: center;">
                                </div>
                                <div class="service-card-body">
                                    <h3 class="service-card-title"><?php echo $serv['nombre']; ?></h3>
                                    <p class="service-card-description"><?php echo $serv['desc']; ?></p>
                                    <p class="service-card-price">$<?php echo number_format($serv['precio'], 0, ',', '.'); ?></p>
                                    <a href="reservar.php" class="btn-service">Reservar</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="servicios.php" class="btn-primary-custom">Ver Todos los Servicios</a>
            </div>
        </div>
    </section>

    <!-- CANCHA SINTÉTICA -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Cancha Sintética</h2>
                <p>Disfruta del mejor espacio deportivo con tus amigos</p>
            </div>
            
            <div class="row align-items-center">
                <div class="col-md-6 mb-4">
                    <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800" alt="Cancha Sintética" style="width: 100%; border-radius: 15px; box-shadow: var(--shadow-lg);">
                </div>
                
                <div class="col-md-6 mb-4">
                    <h3 class="mb-4" style="font-size: 2rem; font-weight: 600;">Instalaciones de Primera</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 1rem; padding-left: 2rem; position: relative;">
                            <i class="fas fa-check-circle" style="position: absolute; left: 0; color: var(--gold-primary); font-size: 1.3rem;"></i>
                            <strong>Césped Sintético Premium</strong> de última generación
                        </li>
                        <li style="margin-bottom: 1rem; padding-left: 2rem; position: relative;">
                            <i class="fas fa-check-circle" style="position: absolute; left: 0; color: var(--gold-primary); font-size: 1.3rem;"></i>
                            <strong>Iluminación LED</strong> para partidos nocturnos
                        </li>
                        <li style="margin-bottom: 1rem; padding-left: 2rem; position: relative;">
                            <i class="fas fa-check-circle" style="position: absolute; left: 0; color: var(--gold-primary); font-size: 1.3rem;"></i>
                            <strong>Vestuarios</strong> equipados y limpios
                        </li>
                        <li style="margin-bottom: 1rem; padding-left: 2rem; position: relative;">
                            <i class="fas fa-check-circle" style="position: absolute; left: 0; color: var(--gold-primary); font-size: 1.3rem;"></i>
                            <strong>Descuentos grupales</strong> para +5 personas
                        </li>
                    </ul>
                    
                    <div style="background: var(--gold-light); padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
                        <p style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--primary-black);">
                            <i class="fas fa-tag"></i> Desde $50,000/hora
                        </p>
                        <p style="margin: 0.5rem 0 0 0; color: var(--gray-medium);">*Descuentos especiales en reservas grupales</p>
                    </div>
                    
                    <a href="cancha.php" class="btn-primary-custom mt-4">Reservar Cancha</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="section" style="background: var(--primary-black); color: var(--white); text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">¿Listo para tu Mejor Look?</h2>
            <p style="font-size: 1.2rem; color: var(--gray-light); margin-bottom: 2rem;">Reserva tu cita ahora y disfruta de nuestros servicios premium</p>
            <a href="reservar.php" class="btn-primary-custom" style="font-size: 1.2rem; padding: 1.2rem 3rem;">Reservar Ahora</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Barber League</h3>
                    <p>Tu barbería de confianza con las mejores instalaciones deportivas en Ibagué.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-phone"></i> +57 311 234 5678</p>
                    <p><i class="fas fa-envelope"></i> info@barberleague.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Calle 42 #23-45, Ibagué</p>
                </div>
                
                <div class="footer-section">
                    <h3>Horarios</h3>
                    <p><strong>Lunes - Viernes:</strong> 9:00 AM - 9:00 PM</p>
                    <p><strong>Sábados:</strong> 9:00 AM - 9:00 PM</p>
                    <p><strong>Domingos:</strong> 10:00 AM - 6:00 PM</p>
                </div>
                s
                <div class="footer-section">
                    <h3>Enlaces</h3>
                    <a href="servicios.php">Servicios</a>
                    <a href="cancha.php">Cancha</a>
                    <a href="reservar.php">Reservar</a>
                    <a href="contacto.php">Contacto</a>
                    <a href="admin/login.php">Admin</a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Barber League. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="assets/js/main.js"></script>
    
</body>
</html>