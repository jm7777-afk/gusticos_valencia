<?php
require_once '../includes/funciones.php';  // ← Ruta corregida (sube un nivel)


$titulo_pagina = 'Pedido Confirmado - Gustico\'s';
include 'includes/header.php';
?>

<div class="container">
    <div class="card" style="text-align: center; padding: 60px 30px; max-width: 600px; margin: 50px auto;">
        <div style="font-size: 5rem; color: var(--verde); margin-bottom: 20px;">✅</div>
        <h1 style="color: var(--morado); margin-bottom: 20px;">¡Pedido Confirmado!</h1>
        
        <p style="font-size: 1.2rem; margin-bottom: 30px;">
            Hemos recibido tu pedido correctamente.
        </p>
        
        <div style="background: var(--gris-claro); padding: 20px; border-radius: var(--borde-redondo); margin: 30px 0;">
            <p style="font-size: 1.1rem;">
                En unos minutos recibirás un WhatsApp de confirmación con los detalles.
            </p>
            <p style="color: var(--texto-secundario); margin-top: 15px;">
                Tiempo estimado de entrega: 35-45 minutos
            </p>
        </div>
        
        <div style="display: flex; gap: 20px; justify-content: center;">
            <a href="index.php" class="btn btn-morado">Seguir comprando</a>
            <a href="perfil.php" class="btn btn-outline">Ver mis pedidos</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>