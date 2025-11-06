<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Barber League - Barbería premium y cancha sintética en Ibagué. Reserva tu cita online.">
    <meta name="keywords" content="barbería, cancha sintética, cortes de cabello, barba, Ibagué, reservas online">
    <meta name="author" content="Barber League">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Barber League - Barbería & Cancha Sintética">
    <meta property="og:description" content="Experiencia premium en cuidado personal y recreación">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Barber League' : 'Barber League - Barbería & Cancha Sintética'; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 para notificaciones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

<!-- Preloader -->
<div class="preloader">
    <div class="spinner"></div>
</div>

<!-- Header -->
<header class="header">
    <nav class="navbar container">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <img src="assets/img/logo.png" alt="Barber League Logo" onerror="this.style.display='none'">
            <span>BARBER LEAGUE</span>
        </a>
        
        <!-- Navigation Menu -->
        <ul class="nav-menu">
            <li><a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Inicio</a></li>
            <li><a href="servicios.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'servicios.php') ? 'active' : ''; ?>">Servicios</a></li>
            <li><a href="cancha.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cancha.php') ? 'active' : ''; ?>">Cancha</a></li>
            <li><a href="about.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">Nosotros</a></li>
            <li><a href="contacto.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contacto.php') ? 'active' : ''; ?>">Contacto</a></li>
            <li><a href="reservar.php" class="btn-reserve-nav">Reservar Ahora</a></li>
        </ul>
        
        <!-- Hamburger Menu -->
        <button class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>
    
</header>