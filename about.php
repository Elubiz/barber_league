<?php $pageTitle = 'Sobre Nosotros'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - Barber League</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .about-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=1600') center/cover;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 70px;
        }
        
        .team-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 2rem;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
        }
        
        .team-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .team-info {
            padding: 1.5rem;
            text-align: center;
        }
        
        .team-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }
        
        .team-role {
            color: #d4af37;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .team-social {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .team-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f6fa;
            border-radius: 50%;
            color: #1a1a1a;
            transition: all 0.3s;
        }
        
        .team-social a:hover {
            background: #d4af37;
            color: #1a1a1a;
        }
        
        .values-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .values-card:hover {
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
            transform: translateY(-5px);
        }
        
        .values-icon {
            font-size: 3rem;
            color: #d4af37;
            margin-bottom: 1rem;
        }
        
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #d4af37;
            transform: translateX(-50%);
        }
        
        .timeline-item {
            margin-bottom: 3rem;
            position: relative;
        }
        
        .timeline-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 45%;
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: auto;
        }
        
        .timeline-year {
            font-size: 2rem;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .timeline::before {
                left: 0;
            }
            
            .timeline-content {
                max-width: 100%;
                margin-left: 2rem !important;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO SECTION -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Sobre <span style="color: #d4af37;">Barber League</span></h1>
                <p style="font-size: 1.3rem; max-width: 700px; margin: 1.5rem auto;">
                    Más que una barbería, somos un estilo de vida donde el cuidado personal se encuentra con la pasión deportiva
                </p>
            </div>
        </div>
    </section>

    <!-- NUESTRA HISTORIA -->
    <section class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4">
                    <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=800" 
                         alt="Barber League Interior" 
                         style="width: 100%; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                </div>
                
                <div class="col-md-6 mb-4">
                    <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1.5rem;">
                        Nuestra <span style="color: #d4af37;">Historia</span>
                    </h2>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 1rem;">
                        <strong>Barber League</strong> nació en 2020 con una visión única: crear un espacio donde los hombres pudieran cuidar su imagen y disfrutar de su pasión por el fútbol en un mismo lugar.
                    </p>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 1rem;">
                        En Ibagué, Tolima, combinamos la tradición de la barbería clásica con instalaciones deportivas de primera clase, creando una experiencia completa para nuestros clientes.
                    </p>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: #666;">
                        Hoy somos referentes en la ciudad, con más de <strong style="color: #d4af37;">5,000 clientes satisfechos</strong> y creciendo cada día.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- MISIÓN Y VISIÓN -->
    <section class="section" style="background: #f5f6fa;">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div style="background: white; padding: 3rem; border-radius: 15px; height: 100%; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #d4af37, #c49a2e); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-bullseye" style="font-size: 2.5rem; color: #1a1a1a;"></i>
                        </div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Nuestra Misión</h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #666;">
                            Proporcionar servicios de barbería premium y espacios recreativos de calidad, creando experiencias memorables que combinen estilo, bienestar y deporte en un ambiente profesional y acogedor.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div style="background: white; padding: 3rem; border-radius: 15px; height: 100%; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #d4af37, #c49a2e); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                            <i class="fas fa-eye" style="font-size: 2.5rem; color: #1a1a1a;"></i>
                        </div>
                        <h3 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Nuestra Visión</h3>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: #666;">
                            Ser la cadena líder en Colombia de espacios integrados de cuidado personal y recreación deportiva, expandiendo nuestro concepto innovador a las principales ciudades del país para el 2028.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- VALORES -->
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    Nuestros <span style="color: #d4af37;">Valores</span>
                </h2>
                <p style="color: #666; font-size: 1.1rem;">Los principios que guían todo lo que hacemos</p>
            </div>
            
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="values-card">
                        <i class="fas fa-star values-icon"></i>
                        <h4 style="font-weight: 700; margin-bottom: 1rem;">Excelencia</h4>
                        <p style="color: #666;">Búsqueda constante de la perfección en cada servicio</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="values-card">
                        <i class="fas fa-handshake values-icon"></i>
                        <h4 style="font-weight: 700; margin-bottom: 1rem;">Compromiso</h4>
                        <p style="color: #666;">Dedicación total con nuestros clientes y su satisfacción</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="values-card">
                        <i class="fas fa-lightbulb values-icon"></i>
                        <h4 style="font-weight: 700; margin-bottom: 1rem;">Innovación</h4>
                        <p style="color: #666;">Siempre a la vanguardia con técnicas y tendencias</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="values-card">
                        <i class="fas fa-users values-icon"></i>
                        <h4 style="font-weight: 700; margin-bottom: 1rem;">Comunidad</h4>
                        <p style="color: #666;">Creamos lazos y experiencias compartidas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NUESTRO EQUIPO -->
    <section class="section" style="background: #f5f6fa;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    Conoce a Nuestro <span style="color: #d4af37;">Equipo</span>
                </h2>
                <p style="color: #666; font-size: 1.1rem;">Profesionales apasionados por su trabajo</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop" 
                             alt="Barbero 1" 
                             class="team-img">
                        <div class="team-info">
                            <h3 class="team-name">Carlos Méndez</h3>
                            <p class="team-role">Barbero Maestro</p>
                            <p style="color: #666; margin-bottom: 1rem;">15 años de experiencia en cortes clásicos y modernos</p>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-tiktok"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop" 
                             alt="Barbero 2" 
                             class="team-img">
                        <div class="team-info">
                            <h3 class="team-name">Miguel Ángel Torres</h3>
                            <p class="team-role">Especialista en Barba</p>
                            <p style="color: #666; margin-bottom: 1rem;">Experto en técnicas tradicionales con navaja</p>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-tiktok"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="team-card">
                        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400&h=400&fit=crop" 
                             alt="Barbero 3" 
                             class="team-img">
                        <div class="team-info">
                            <h3 class="team-name">Andrés Valencia</h3>
                            <p class="team-role">Estilista Creativo</p>
                            <p style="color: #666; margin-bottom: 1rem;">Especialista en diseños y estilos vanguardistas</p>
                            <div class="team-social">
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-tiktok"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LOGROS Y CIFRAS -->
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    Nuestros <span style="color: #d4af37;">Logros</span>
                </h2>
            </div>
            
            <div class="row text-center">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div style="padding: 2rem;">
                        <i class="fas fa-users" style="font-size: 3rem; color: #d4af37; margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 3rem; font-weight: 700; color: #d4af37; margin-bottom: 0.5rem;">5,000+</h3>
                        <p style="color: #666; font-size: 1.1rem;">Clientes Satisfechos</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div style="padding: 2rem;">
                        <i class="fas fa-cut" style="font-size: 3rem; color: #d4af37; margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 3rem; font-weight: 700; color: #d4af37; margin-bottom: 0.5rem;">15,000+</h3>
                        <p style="color: #666; font-size: 1.1rem;">Servicios Realizados</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div style="padding: 2rem;">
                        <i class="fas fa-futbol" style="font-size: 3rem; color: #d4af37; margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 3rem; font-weight: 700; color: #d4af37; margin-bottom: 0.5rem;">2,000+</h3>
                        <p style="color: #666; font-size: 1.1rem;">Partidos Jugados</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-4">
                    <div style="padding: 2rem;">
                        <i class="fas fa-award" style="font-size: 3rem; color: #d4af37; margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 3rem; font-weight: 700; color: #d4af37; margin-bottom: 0.5rem;">4</h3>
                        <p style="color: #666; font-size: 1.1rem;">Años de Experiencia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="section" style="background: linear-gradient(135deg, #d4af37, #c49a2e); text-align: center; color: #1a1a1a;">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">¿Listo para Unirte a la Liga?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Agenda tu cita y vive la experiencia Barber League</p>
            <a href="reservar.php" class="btn" style="background: #1a1a1a; color: white; padding: 1rem 3rem; border-radius: 30px; font-size: 1.2rem; font-weight: 600; text-decoration: none; display: inline-block;">
                <i class="fas fa-calendar-check"></i> Reservar Ahora
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
</body>
</html>