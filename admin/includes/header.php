<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'Gustico\'s Admin'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <!-- CSS Admin -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        /* Estilo específico para el logo en admin */
        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .admin-logo {
            height: 50px;
            width: auto;
            margin-bottom: 10px;
        }
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .sidebar-header h2 span {
            color: var(--verde);
        }
    </style>
</head>
<body>
    <!-- MENÚ HAMBURGUESA -->
    <div class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </div>
    
    <!-- OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!-- ============================================
                 LOGO ADMIN - CAMBIA LA RUTA SEGÚN TU ARCHIVO
                 Opción 1: Solo imagen
                 Opción 2: Imagen + texto
                 Opción 3: Solo texto
            ============================================ -->
            
            <!-- OPCIÓN 1: SOLO IMAGEN (recomendada) -->
            <img src="../assets/img/11.png" alt="Gustico's" class="admin-logo">
            
            <!-- OPCIÓN 2: IMAGEN + TEXTO -->
            <!--
            <img src="/gusticos/assets/img/logo-admin.png" alt="Gustico's" class="admin-logo">
            <h2>Gustico<span>'s</span></h2>
            -->
            
            <!-- OPCIÓN 3: SOLO TEXTO -->
            <!-- <h2>Gustico<span>'s</span></h2> -->
            
            <p style="color: rgba(255,255,255,0.7); margin-top: 5px;">Admin Panel</p>
        </div>
        
        <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="user-info">
            <strong><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></strong>
            <small><?php echo $_SESSION['usuario_rol'] ?? 'admin'; ?></small>
        </div>
        <?php endif; ?>
        
        <ul class="nav-menu">
            <li><a href="/../admin/index.php" class="<?php echo $pagina_actual == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a></li>
            <li><a href="/../admin/productos.php" class="<?php echo $pagina_actual == 'productos.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Productos
            </a></li>
            <li><a href="/../admin/pedidos.php" class="<?php echo $pagina_actual == 'pedidos.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a></li>
            <li><a href="/../admin/clientes.php" class="<?php echo $pagina_actual == 'clientes.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Clientes
            </a></li>
            <li><a href="/../admin/promociones.php" class="<?php echo $pagina_actual == 'promociones.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Promociones
            </a></li>
            <li><a href="/../admin/comentarios.php" class="<?php echo $pagina_actual == 'comentarios.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Comentarios
            </a></li>
            <li><a href="/../admin/zonas.php" class="<?php echo $pagina_actual == 'zonas.php' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> Zonas
            </a></li>
            <li><a href="/../admin/carrusel.php" class="<?php echo $pagina_actual == 'carrusel.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Carrusel
            </a></li>
            <li><a href="/../admin/configuracion.php" class="<?php echo $pagina_actual == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Configuración
            </a></li>
            <li style="margin-top: 20px;"><a href="/../admin/logout.php">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a></li>
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">