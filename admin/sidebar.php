<div class="sidebar">
    <div class="sidebar-header">
        <h2>Gustico<span>'s</span></h2>
        <p style="font-size: 0.9rem; margin-top: 10px;">Admin Panel</p>
    </div>
    
    <div class="user-info">
        <div><strong><?php echo $_SESSION['usuario_nombre']; ?></strong></div>
        <div style="font-size: 0.8rem; opacity: 0.7;"><?php echo $_SESSION['usuario_rol']; ?></div>
    </div>
    
    <ul class="nav-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="productos.php"><i class="fas fa-utensils"></i> Productos</a></li>
        <li><a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
        <li><a href="clientes.php"><i class="fas fa-users"></i> Clientes</a></li>
        <li><a href="promociones.php"><i class="fas fa-tags"></i> Promociones</a></li>
        <li><a href="comentarios.php"><i class="fas fa-star"></i> Comentarios</a></li>
        <li><a href="zonas.php"><i class="fas fa-truck"></i> Zonas de Envío</a></li>
        <li><a href="carrusel.php"><i class="fas fa-images"></i> Carrusel</a></li>
        <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
        <li style="margin-top: 20px;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<style>
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2D2D2D 0%, #1a1a1a 100%);
    color: white;
    height: 100vh;
    position: fixed;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 30px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
    font-size: 1.8rem;
    font-weight: 800;
    color: white;
}

.sidebar-header span {
    color: var(--green);
}

.user-info {
    padding: 20px;
    background: rgba(255,255,255,0.1);
    margin: 20px;
    border-radius: 12px;
}

.nav-menu {
    list-style: none;
    padding: 20px;
}

.nav-menu li {
    margin-bottom: 5px;
}

.nav-menu a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
}

.nav-menu a:hover, .nav-menu a.active {
    background: rgba(123,31,162,0.5);
}

.nav-menu i {
    width: 24px;
    margin-right: 10px;
    color: var(--purple-light);
}
</style>