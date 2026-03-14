<?php
$whatsapp_principal = obtenerWhatsApp($pdo);
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'Gustico\'s - Helados Artesanales'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- CSS Cliente -->
    <link rel="stylesheet" href="/gusticos/assets/css/cliente.css">
    
    <style>
        /* Estilo específico para el logo */
        .logo {
            display: flex;
            align-items: center;
        }
        .logo-img {
            height: 60px;
            width: auto;
            margin-right: 10px;
        }
        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
        }
        .logo-text span {
            color: #FFD700;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <!-- ============================================
                     LOGO - CAMBIA LA RUTA SEGÚN TU ARCHIVO
                     Opción 1: Solo imagen
                     Opción 2: Imagen + texto
                     Opción 3: Solo texto (si no tienes imagen)
                ============================================ -->
                
                <!-- OPCIÓN 1: SOLO IMAGEN (recomendada) -->
                <img src="../assets/img/11.png" alt="Gustico's" class="logo-img">
                
                <!-- OPCIÓN 2: IMAGEN + TEXTO (descomenta si quieres) -->
                <!--
                <img src="/gusticos/assets/img/logo.png" alt="Gustico's" class="logo-img">
                <span class="logo-text">Gustico<span>'s</span></span>
                -->
                
            
               
            </div>
        
                
                <div class="user-menu">
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <span class="user-name">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['cliente_nombre']; ?>
                        </span>
                        <a href="perfil.php"><i class="fas fa-user-circle" style="font-size: 1.3rem;"></i></a>
                        <a href="logout.php">Salir</a>
                    <?php else: ?>
                        <a href="login.php">Iniciar Sesión</a>
                        <a href="registro.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 40px;">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <main>