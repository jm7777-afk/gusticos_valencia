<?php
require_once '../includes/funciones.php';


$id = $_GET['id'] ?? 0;
$titulo_pagina = 'Detalle de Producto - Gustico\'s';

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ? AND activo = 1");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$badge = getStockBadge($producto['stock']);
$tasa = obtenerTasaBVC($pdo);

include 'includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 20px;">
        <a href="index.php" style="color: var(--morado);">← Volver al catálogo</a>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <!-- Imagen -->
        <div class="producto-imagen" style="height: 400px;">
            <?php if (!empty($producto['imagen'])): ?>
                <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <i class="fas fa-ice-cream" style="font-size: 8rem;"></i>
            <?php endif; ?>
        </div>
        
        <!-- Información -->
        <div>
            <h1 style="color: var(--morado); margin-bottom: 15px;"><?php echo $producto['nombre']; ?></h1>
            
            <div style="margin-bottom: 15px;">
                <span class="badge <?php echo $badge['clase']; ?>"><?php echo $badge['texto']; ?></span>
                <?php if ($producto['stock'] > 0): ?>
                <small style="margin-left: 15px;"><?php echo $producto['stock']; ?> unidades disponibles</small>
                <?php endif; ?>
            </div>
            
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--verde); margin: 20px 0;">
                $<?php echo number_format($producto['precio'], 2); ?>
                <?php if ($tasa > 0): ?>
                <small style="font-size: 1rem; color: var(--texto-secundario;">Bs <?php echo number_format($producto['precio'] * $tasa, 2); ?></small>
                <?php endif; ?>
            </div>
            
            <div style="line-height: 1.8; margin: 30px 0;">
                <?php echo nl2br($producto['descripcion']); ?>
            </div>
            
            <?php if ($producto['stock'] > 0): ?>
            <div style="display: flex; gap: 20px; align-items: center; margin: 30px 0;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="btn-outline" style="width: 40px;" onclick="decrementar()">-</button>
                    <span id="cantidad" style="font-size: 1.3rem; font-weight: 700;">1</span>
                    <button class="btn-outline" style="width: 40px;" onclick="incrementar()">+</button>
                </div>
                <button class="btn btn-morado" style="flex: 1;" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                    Agregar al carrito
                </button>
            </div>
            <?php else: ?>
            <button class="btn btn-rojo" style="width: 100%;" disabled>Producto agotado</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    let cantidad = 1;
    
    function incrementar() {
        cantidad++;
        document.getElementById('cantidad').textContent = cantidad;
    }
    
    function decrementar() {
        if (cantidad > 1) {
            cantidad--;
            document.getElementById('cantidad').textContent = cantidad;
        }
    }
    
    function agregarAlCarrito(id) {
        let carrito = JSON.parse(localStorage.getItem('carrito') || '{}');
        carrito[id] = (carrito[id] || 0) + cantidad;
        localStorage.setItem('carrito', JSON.stringify(carrito));
        actualizarContadorCarrito();
        
        const btn = event.target;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Agregado';
        btn.style.background = 'var(--verde)';
        
        setTimeout(() => {
            btn.innerHTML = original;
            btn.style.background = '';
        }, 1500);
    }
</script>

<?php include 'includes/footer.php'; ?>