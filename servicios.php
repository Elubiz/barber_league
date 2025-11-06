<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Servicios - Barber League</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .filter-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 3rem;
        }
        
        .filter-btn {
            padding: 0.6rem 1.5rem;
            margin: 0.3rem;
            border: 2px solid var(--gold-primary);
            background: transparent;
            color: var(--primary-black);
            border-radius: 25px;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: var(--gold-primary);
            color: var(--primary-black);
        }
        
        .service-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gold-primary);
            color: var(--primary-black);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .duration-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: var(--gray-light);
            border-radius: 15px;
            font-size: 0.85rem;
            color: var(--gray-medium);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO SECCIÓN SERVICIOS -->
    <section class="hero-section" style="height: 50vh; min-height: 400px; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=1600') center/cover;">
        <div class="container">
            <div class="hero-content text-center">
                <h1>Nuestros <span class="gold-text">Servicios</span></h1>
                <p>Tratamientos profesionales para tu mejor versión</p>
            </div>
        </div>
    </section>

    <!-- FILTROS -->
    <section class="section">
        <div class="container">
            <div class="filter-section text-center">
                <h3 style="margin-bottom: 1.5rem;">Filtrar por categoría</h3>
                <button class="filter-btn active" data-filter="todos">
                    <i class="fas fa-th"></i> Todos
                </button>
                <button class="filter-btn" data-filter="corte">
                    <i class="fas fa-cut"></i> Cortes
                </button>
                <button class="filter-btn" data-filter="barba">
                    <i class="fas fa-user-tie"></i> Barba
                </button>
                <button class="filter-btn" data-filter="tratamiento">
                    <i class="fas fa-spa"></i> Tratamientos
                </button>
                <button class="filter-btn" data-filter="especial">
                    <i class="fas fa-star"></i> Especiales
                </button>
            </div>

            <!-- LISTADO DE SERVICIOS -->
            <div class="row" id="serviciosContainer">
                <?php
                include 'includes/conexion.php';
                
                $query = "SELECT * FROM servicios ORDER BY nombre_servicio";
                $result = mysqli_query($conexion, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($servicio = mysqli_fetch_assoc($result)) {
                        // Determinar categoría (puedes agregar un campo 'categoria' en la BD)
                        $categoria = 'especial';
                        if (stripos($servicio['nombre_servicio'], 'corte') !== false) {
                            $categoria = 'corte';
                        } elseif (stripos($servicio['nombre_servicio'], 'barba') !== false) {
                            $categoria = 'barba';
                        } elseif (stripos($servicio['nombre_servicio'], 'masaje') !== false || 
                                  stripos($servicio['nombre_servicio'], 'tratamiento') !== false) {
                            $categoria = 'tratamiento';
                        }
                        
                        // Imagen según categoría
                        $imagenes = [
                            'corte' => 'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=800',
                            'barba' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?w=800',
                            'tratamiento' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800',
                            'especial' => 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=800'
                        ];
                        
                        $imagen = $imagenes[$categoria] ?? $imagenes['especial'];
                        $duracion = isset($servicio['duracion']) ? $servicio['duracion'] : 60;
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4 service-item" data-categoria="<?php echo $categoria; ?>">
                            <div class="service-card">
                                <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('<?php echo $imagen; ?>'); background-size: cover; background-position: center; position: relative;">
                                    <?php if ($servicio['precio'] < 30000): ?>
                                        <span class="service-badge">
                                            <i class="fas fa-tag"></i> Oferta
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="service-card-body">
                                    <span class="duration-badge">
                                        <i class="fas fa-clock"></i> <?php echo $duracion; ?> min
                                    </span>
                                    <h3 class="service-card-title"><?php echo htmlspecialchars($servicio['nombre_servicio']); ?></h3>
                                    <p class="service-card-description"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                                    <p class="service-card-price">$<?php echo number_format($servicio['precio'], 0, ',', '.'); ?></p>
                                    <a href="reservar.php?servicio=<?php echo $servicio['id']; ?>" class="btn-service">
                                        <i class="fas fa-calendar-check"></i> Reservar Ahora
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Servicios de ejemplo si no hay en la BD
                    $serviciosEjemplo = [
                        [
                            'nombre' => 'Corte Clásico',
                            'desc' => 'Corte tradicional con tijera y máquina, incluye lavado',
                            'precio' => 25000,
                            'duracion' => 45,
                            'categoria' => 'corte',
                            'img' => 'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=800'
                        ],
                        [
                            'nombre' => 'Corte Moderno',
                            'desc' => 'Estilos actuales con técnicas innovadoras',
                            'precio' => 30000,
                            'duracion' => 60,
                            'categoria' => 'corte',
                            'img' => 'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=800'
                        ],
                        [
                            'nombre' => 'Barba Profesional',
                            'desc' => 'Perfilado y arreglo de barba con navaja tradicional',
                            'precio' => 15000,
                            'duracion' => 30,
                            'categoria' => 'barba',
                            'img' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?w=800'
                        ],
                        [
                            'nombre' => 'Corte + Barba Combo',
                            'desc' => 'Paquete completo para lucir impecable',
                            'precio' => 35000,
                            'duracion' => 60,
                            'categoria' => 'especial',
                            'img' => 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=800'
                        ],
                        [
                            'nombre' => 'Trenzas Africanas',
                            'desc' => 'Diseños creativos y duraderos con técnica profesional',
                            'precio' => 30000,
                            'duracion' => 90,
                            'categoria' => 'especial',
                            'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'
                        ],
                        [
                            'nombre' => 'Dreadlocks',
                            'desc' => 'Mantenimiento y creación de dreadlocks profesionales',
                            'precio' => 80000,
                            'duracion' => 180,
                            'categoria' => 'especial',
                            'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'
                        ],
                        [
                            'nombre' => 'Masaje Capilar',
                            'desc' => 'Tratamiento relajante con productos premium',
                            'precio' => 20000,
                            'duracion' => 30,
                            'categoria' => 'tratamiento',
                            'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'
                        ],
                        [
                            'nombre' => 'Tratamiento Capilar',
                            'desc' => 'Hidratación profunda y revitalización del cabello',
                            'precio' => 35000,
                            'duracion' => 45,
                            'categoria' => 'tratamiento',
                            'img' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'
                        ],
                        [
                            'nombre' => 'Afeitado Tradicional',
                            'desc' => 'Experiencia clásica con toallas calientes y navaja',
                            'precio' => 20000,
                            'duracion' => 40,
                            'categoria' => 'barba',
                            'img' => 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?w=800'
                        ]
                    ];
                    
                    foreach ($serviciosEjemplo as $serv) {
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4 service-item" data-categoria="<?php echo $serv['categoria']; ?>">
                            <div class="service-card">
                                <div class="service-card-img" style="background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('<?php echo $serv['img']; ?>'); background-size: cover; background-position: center; position: relative;">
                                    <?php if ($serv['precio'] < 30000): ?>
                                        <span class="service-badge">
                                            <i class="fas fa-tag"></i> Oferta
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="service-card-body">
                                    <span class="duration-badge">
                                        <i class="fas fa-clock"></i> <?php echo $serv['duracion']; ?> min
                                    </span>
                                    <h3 class="service-card-title"><?php echo $serv['nombre']; ?></h3>
                                    <p class="service-card-description"><?php echo $serv['desc']; ?></p>
                                    <p class="service-card-price">$<?php echo number_format($serv['precio'], 0, ',', '.'); ?></p>
                                    <a href="reservar.php" class="btn-service">
                                        <i class="fas fa-calendar-check"></i> Reservar Ahora
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="section" style="background: var(--gold-light); text-align: center;">
        <div class="container">
            <h2 style="font-size: 2rem; margin-bottom: 1rem;">¿Tienes dudas sobre qué servicio elegir?</h2>
            <p style="color: var(--gray-medium); margin-bottom: 2rem;">Contáctanos y te asesoramos personalmente</p>
            <a href="contacto.php" class="btn-primary-custom">
                <i class="fas fa-phone"></i> Contactar Ahora
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Filtro de servicios
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover clase active de todos
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Agregar clase active al botón clickeado
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const items = document.querySelectorAll('.service-item');
                
                items.forEach(item => {
                    if (filter === 'todos' || item.dataset.categoria === filter) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
        
        // Inicializar con todos visibles
        document.querySelectorAll('.service-item').forEach(item => {
            item.style.transition = 'all 0.3s ease';
        });
    </script>
    
</body>
</html>