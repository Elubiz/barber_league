<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../includes/conexion.php';
    
    $usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $password = trim($_POST['password']);
    
    // ✅ CORREGIDO: Solo seleccionar campos que existen en la tabla
    $query = "SELECT id, usuario, password FROM administradores WHERE usuario = '$usuario' LIMIT 1";
    $result = mysqli_query($conexion, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        
        // Verificar la contraseña
        if (password_verify($password, $admin['password'])) {
            // Login exitoso - Crear sesión
            $_SESSION['admin_logueado'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            $_SESSION['admin_nombre'] = $admin['usuario']; 
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
    
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Barber League</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gold-primary: #d4af37;
            --primary-black: #1a1a1a;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--gold-primary) 0%, #c49a2e 100%);
            padding: 3rem;
            color: var(--primary-black);
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .login-left i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }
        
        .login-left h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .login-right {
            padding: 3rem;
            flex: 1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h3 {
            color: var(--primary-black);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--gold-primary), #c49a2e);
            color: var(--primary-black);
            border: none;
            border-radius: 10px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
            color: var(--primary-black);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-home a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-home a:hover {
            color: var(--gold-primary);
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                padding: 2rem;
            }
            
            .login-right {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Lado izquierdo - Branding -->
        <div class="login-left">
            <i class="fas fa-cut"></i>
            <h2>BARBER LEAGUE</h2>
            <p>Panel de Administración</p>
            <p style="font-size: 0.9rem; margin-top: 2rem; opacity: 0.9;">
                Gestiona reservas, clientes y servicios desde un solo lugar
            </p>
        </div>
        
        <!-- Lado derecho - Formulario -->
        <div class="login-right">
            <div class="login-header">
                <h3>Iniciar Sesión</h3>
                <p>Ingresa tus credenciales de administrador</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="usuario" 
                        name="usuario" 
                        placeholder="Ingresa tu usuario"
                        required
                        autofocus
                    >
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="back-home">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Volver al sitio web
                </a>
            </div>
            
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>