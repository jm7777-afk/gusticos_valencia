<?php
require_once '../includes/funciones.php';


$titulo_pagina = 'Mi Carrito - Gustico\'s';
$tasa = obtenerTasaBVC($pdo);
$cliente_logueado = isset($_SESSION['cliente_id']);

// Definir sucursales
$sucursales = [
    [
        'id' => 1,
        'nombre' => 'Sucursal Norte - Los Samanes',
        'direccion' => 'LOS SAMANES, VALENCIA EDO CARABOBO',
        'whatsapp' => '584244179135',
        'telefono' => '0424-4179135'
    ],
    [
        'id' => 2,
        'nombre' => 'Sucursal Sur - Valencia Sur',
        'direccion' => 'Av. Principal, Valencia Sur',
        'whatsapp' => '584124567890',
        'telefono' => '0412-4567890'
    ]
];

include 'includes/header.php';
?>

<div class="container">
    <h1 style="color: var(--morado); margin-bottom: 30px;">Mi Carrito</h1>
    
    <!-- CARRITO VACÍO -->
    <div id="carrito-vacio" style="display: none; text-align: center; padding: 60px;">
        <i class="fas fa-shopping-basket" style="font-size: 4rem; color: #CCC; margin-bottom: 20px;"></i>
        <h3>Tu carrito está vacío</h3>
        <a href="index.php" class="btn btn-morado" style="margin-top: 20px;">Ver productos</a>
    </div>
    
    <!-- CONTENIDO DEL CARRITO -->
    <div id="carrito-contenido">
         <!-- BOTÓN PARA LIMPIAR CARRITO -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <button class="btn btn-rojo" onclick="limpiarCarrito()">
                <i class="fas fa-trash"></i> Vaciar Carrito
            </button>
        </div>
        <!-- LISTA DE PRODUCTOS DEL CARRITO -->
        <div id="carrito-items" class="carrito-lista"></div>
        
        <!-- ADVERTENCIA DE DELIVERY Y DEVOLUCIONES -->
        <div class="card" style="background: #FFF3E0; border-left: 5px solid #FF9800; margin: 20px 0;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #FF9800;"></i>
                <div>
                    <h3 style="color: #E65100; margin-bottom: 10px;">⚠️ IMPORTANTE - LEE CON ATENCIÓN</h3>
                    <p style="color: #E65100; font-weight: 500;">
                        • Los pagos realizados son SOLO para delivery.<br>
                        • Verifica bien tus datos antes de confirmar.<br>
                        • Las devoluciones de dinero pueden tardar hasta 72 horas hábiles.<br>
                        • Para Pago Móvil, la captura de pago es OBLIGATORIA.<br>
                        • En Pago Múltiple, debes completar ambas partes.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- SELECCIÓN DE SUCURSAL -->
        <div class="card" style="margin: 30px 0;">
            <h2 style="margin-bottom: 20px;">Selecciona tu sucursal para el delivery</h2>
            <div class="sucursales-grid" id="sucursales-grid">
                <?php foreach ($sucursales as $index => $s): ?>
                <div class="sucursal-card" data-id="<?php echo $s['id']; ?>" 
                     data-nombre="<?php echo $s['nombre']; ?>"
                     data-whatsapp="<?php echo $s['whatsapp']; ?>"
                     onclick="seleccionarSucursal(<?php echo $index; ?>)">
                    <div class="sucursal-nombre"><?php echo $s['nombre']; ?></div>
                    <div class="sucursal-direccion"><i class="fas fa-map-marker-alt"></i> <?php echo $s['direccion']; ?></div>
                    <div><i class="fab fa-whatsapp" style="color: #25D366;"></i> <?php echo $s['telefono']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- TOTAL A PAGAR -->
        <div class="total-card">
            <h2 style="color: white;">TOTAL A PAGAR:</h2>
            <div class="total-cantidad" id="total-cantidad">$0.00</div>
            <div class="total-bs" id="total-bs">Bs 0,00</div>
        </div>
        
        <!-- DATOS DE ENTREGA CON GOOGLE MAPS -->
        <div class="card" style="margin: 30px 0;">
            <h2>Datos de entrega</h2>
            
            <div class="form-group">
                <label>Nombre de quien recibe *</label>
                <input type="text" id="nombre_recibe" required>
            </div>
            
            <div class="form-group">
                <label>Teléfono de contacto *</label>
                <input type="tel" id="telefono" required>
            </div>
            
            <div class="form-group">
                <label>Dirección completa *</label>
                <textarea id="direccion" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Referencias adicionales</label>
                <textarea id="referencias" rows="2" placeholder="Ej: Casa de rejas verdes, al lado de la panadería..."></textarea>
            </div>
            
            <button class="btn btn-outline" style="width: 100%;" onclick="obtenerUbicacion()">
                <i class="fas fa-map-marker-alt"></i> Obtener ubicación con Google Maps
            </button>
            <input type="hidden" id="ubicacion" value="">
            
            <!-- Mapa de Google -->
            <div id="map" style="height: 300px; display: none; margin-top: 20px; border-radius: 12px;"></div>
            <div id="map-link" style="display: none; margin-top: 10px;">
                <a href="#" id="map-url" target="_blank" class="btn btn-sm btn-outline">Abrir en Google Maps</a>
            </div>
        </div>
        
        <!-- MÉTODOS DE PAGO - COMPLETO -->
        <div class="card" style="margin: 30px 0;">
            <h2>Método de pago</h2>
            
            <!-- EFECTIVO BS -->
            <div class="metodo-pago" data-metodo="efectivo_bs" onclick="seleccionarMetodo('efectivo_bs')">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 1.5rem;">💵</span> 
                        <strong>Efectivo Bs</strong>
                        <span style="font-size: 0.8rem; color: var(--verde); margin-left: 10px;">(Paga en Bolívares al recibir)</span>
                    </div>
                    <input type="radio" name="metodo" value="efectivo_bs">
                </div>
            </div>
            
            <!-- EFECTIVO DIVISA -->
            <div class="metodo-pago" data-metodo="efectivo_divisa" onclick="seleccionarMetodo('efectivo_divisa')">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 1.5rem;">💶</span> 
                        <strong>Efectivo Divisa</strong>
                        <span style="font-size: 0.8rem; color: var(--verde); margin-left: 10px;">(USD/EUR al recibir)</span>
                    </div>
                    <input type="radio" name="metodo" value="efectivo_divisa">
                </div>
            </div>
            
            <!-- PAGO MÓVIL -->
            <div class="metodo-pago" data-metodo="pago_movil" onclick="seleccionarMetodo('pago_movil')">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 1.5rem;">📱</span> 
                        <strong>Pago Móvil</strong>
                        <span style="font-size: 0.8rem; color: var(--rojo); margin-left: 10px;">(Captura obligatoria)</span>
                    </div>
                    <input type="radio" name="metodo" value="pago_movil">
                </div>
            </div>
            
            <!-- PAGO MÚLTIPLE -->
            <div class="metodo-pago" data-metodo="multipago" onclick="seleccionarMetodo('multipago')">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 1.5rem;">🔄</span> 
                        <strong>Pago Múltiple</strong>
                        <span style="font-size: 0.8rem; color: var(--morado); margin-left: 10px;">(Parte Pago Móvil + Parte Efectivo)</span>
                    </div>
                    <input type="radio" name="metodo" value="multipago">
                </div>
            </div>
        </div>
        
        <!-- DATOS DE PAGO SEGÚN MÉTODO -->
        <div id="datos-pago" style="display: none;">
            
            <!-- EFECTIVO BS -->
            <div id="datos-efectivo_bs" class="card" style="display: none;">
                <h3>Pago en Efectivo - Bolívares</h3>
                <div style="background: var(--gris-claro); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p><i class="fas fa-check-circle" style="color: var(--verde);"></i> Pagarás al recibir el pedido.</p>
                    <p><strong>Total a pagar:</strong> Bs <?php echo number_format(0, 2, ',', '.'); ?></p>
                </div>
                <div class="form-group">
                    <label>¿Requieres cambio? (opcional)</label>
                    <input type="text" id="cambio_bs" placeholder="Ej: 100 (si pagas con 100 Bs)">
                </div>
            </div>
            
            <!-- EFECTIVO DIVISA -->
            <div id="datos-efectivo_divisa" class="card" style="display: none;">
                <h3>Pago en Efectivo - Divisas</h3>
                <div style="background: var(--gris-claro); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p><i class="fas fa-check-circle" style="color: var(--verde);"></i> Pagarás al recibir el pedido.</p>
                    <p><strong>Total a pagar:</strong> $<span id="total-efectivo-divisa">0.00</span></p>
                    <p><small>Tasa BCV: Bs <?php echo number_format($tasa, 2); ?> por USD</small></p>
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <select id="moneda_divisa">
                        <option value="USD">USD (Dólares)</option>
                        <option value="EUR">EUR (Euros)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>¿Requieres cambio? (opcional)</label>
                    <input type="text" id="cambio_divisa" placeholder="Ej: 50 (si pagas con $50)">
                </div>
            </div>
            
            <!-- PAGO MÓVIL -->
            <div id="datos-pago_movil" class="card" style="display: none;">
                <h3>Pago Móvil</h3>
                <div style="background: var(--gris-claro); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p><strong>Banco:</strong> Mercantil</p>
                    <p><strong>Teléfono:</strong> 0412-1234567</p>
                    <p><strong>CI/RIF:</strong> J-12345678-9</p>
                    <p><strong>Monto:</strong> $<span id="total-pago-movil">0.00</span></p>
                </div>
                
                <div class="form-group">
                    <label>Número de referencia *</label>
                    <input type="text" id="referencia" required>
                </div>
                
                <div class="form-group">
                    <label>Captura de pago (foto/comprobante) *</label>
                    <input type="file" id="captura_pago" accept="image/*,.pdf" required>
                    <small style="display: block; margin-top: 5px; color: var(--rojo);">
                        <i class="fas fa-exclamation-circle"></i> La captura de pago es OBLIGATORIA
                    </small>
                </div>
                
                <div id="preview-captura" style="display: none; margin-top: 15px;">
                    <p>Vista previa:</p>
                    <img id="preview-imagen" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                </div>
            </div>
            
            <!-- PAGO MÚLTIPLE -->
            <div id="datos-multipago" class="card" style="display: none;">
                <h3>Pago Múltiple</h3>
                <div style="background: var(--gris-claro); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p><i class="fas fa-info-circle" style="color: var(--morado);"></i> Puedes dividir tu pago en dos partes:</p>
                    <p><strong>Total:</strong> $<span id="total-multipago">0.00</span> / Bs <span id="total-multipago-bs">0.00</span></p>
                </div>
                
                <div class="form-group">
                    <label>Monto a pagar por Pago Móvil ($)</label>
                    <input type="number" id="monto_pago_movil" step="0.01" min="0" value="0" onchange="calcularRestanteMultipago()">
                </div>
                
                <!-- Datos de Pago Móvil (se muestra si monto > 0) -->
                <div id="datos-pago-movil-multipago" style="display: none; margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                    <h4>Datos para Pago Móvil</h4>
                    <p><strong>Banco:</strong> Mercantil</p>
                    <p><strong>Teléfono:</strong> 0412-1234567</p>
                    <p><strong>CI/RIF:</strong> J-12345678-9</p>
                    
                    <div class="form-group">
                        <label>Número de referencia *</label>
                        <input type="text" id="referencia_multipago">
                    </div>
                    
                    <div class="form-group">
                        <label>Captura de pago *</label>
                        <input type="file" id="captura_multipago" accept="image/*,.pdf">
                    </div>
                </div>
                
                <!-- Resto a pagar en efectivo -->
                <div id="resto-efectivo" style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
                    <h4>Resto a pagar en efectivo</h4>
                    <p><strong>Monto restante:</strong> $<span id="resto-multipago">0.00</span></p>
                    <p><strong>En Bs:</strong> Bs <span id="resto-multipago-bs">0.00</span> (tasa <?php echo $tasa; ?>)</p>
                    
                    <div class="form-group">
                        <label>Moneda para el efectivo</label>
                        <select id="moneda_resto">
                            <option value="USD">USD (Dólares)</option>
                            <option value="BS">Bs (Bolívares)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>¿Requieres cambio? (opcional)</label>
                        <input type="text" id="cambio_multipago" placeholder="Ej: 20 (si pagas con $20)">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- MENSAJE DE LOGIN SI NO ESTÁ LOGUEADO -->
        <?php if (!$cliente_logueado): ?>
        <div class="card" style="background: #FFF3E0; margin: 20px 0;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #FF9800;"></i>
                <div>
                    <h3 style="color: #E65100;">Necesitas iniciar sesión</h3>
                    <p>Para confirmar tu pedido, debes <a href="login.php" style="color: var(--morado); font-weight: 600;">iniciar sesión</a> o <a href="registro.php" style="color: var(--morado); font-weight: 600;">crear una cuenta</a>.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- BOTONES FINALES -->
        <div style="display: flex; gap: 20px; margin: 40px 0;">
            <a href="index.php" class="btn btn-outline" style="flex: 1;">Seguir comprando</a>
            <?php if ($cliente_logueado): ?>
            <button class="btn btn-verde" style="flex: 1;" onclick="confirmarPedido()">Confirmar Pedido</button>
            <?php else: ?>
            <a href="login.php" class="btn btn-verde" style="flex: 1; text-align: center;">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let carrito = {};
    let productos = [];
    let subtotal = 0;
    let metodoSeleccionado = '';
    let sucursalSeleccionada = null;
    let capturaBase64 = '';
    let capturaMultipagoBase64 = '';
    
    // ============================================
    // FUNCIÓN: CARGAR CARRITO DESDE LOCALSTORAGE
    // ============================================
    function cargarCarrito() {
        const carritoStr = localStorage.getItem('carrito');
        console.log('Carrito cargado:', carritoStr);
        
        try {
            carrito = JSON.parse(carritoStr) || {};
        } catch(e) {
            console.error('Error al parsear carrito:', e);
            carrito = {};
        }
        
        const totalItems = Object.values(carrito).reduce((a, b) => a + b, 0);
        console.log('Total de items:', totalItems);
        
        if (totalItems === 0) {
            document.getElementById('carrito-vacio').style.display = 'block';
            document.getElementById('carrito-contenido').style.display = 'none';
            return;
        }
        
        document.getElementById('carrito-vacio').style.display = 'none';
        document.getElementById('carrito-contenido').style.display = 'block';
        
        // Enviar a la API para obtener detalles de productos
        fetch('../api/carrito.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'accion=calcular&items=' + encodeURIComponent(JSON.stringify(carrito))
        })
        .then(res => res.json())
        .then(data => {
            console.log('Respuesta API:', data);
            if (data.success) {
                productos = data.data.productos || [];
                console.log('Productos recibidos:', productos);
                renderizarCarrito();
            } else {
                alert('Error al cargar carrito: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error de conexión:', error);
            alert('Error de conexión al cargar el carrito');
        });
    }
    
    // ============================================
    // FUNCIÓN: RENDERIZAR CARRITO
    // ============================================
    function renderizarCarrito() {
        if (!productos || productos.length === 0) {
            document.getElementById('carrito-items').innerHTML = '<p style="text-align: center; padding: 40px;">No hay productos en el carrito</p>';
            subtotal = 0;
            actualizarTotal();
            return;
        }
        
        let html = '';
        subtotal = 0;
        
        productos.forEach(p => {
            subtotal += p.subtotal;
            
            let precioTexto = `$${p.precio.toFixed(2)}`;
            let badgeMayor = '';
            
            if (p.cantidad >= 12 && p.precio_mayor) {
                precioTexto = `<span style="text-decoration: line-through; color: #999;">$${p.precio.toFixed(2)}</span> $${p.precio_usado.toFixed(2)}`;
                badgeMayor = '<span class="precio-mayor-destacado">Precio mayor</span>';
            }
            
            html += `
                <div class="carrito-item">
                    <div class="carrito-imagen"><i class="fas fa-ice-cream"></i></div>
                    <div class="carrito-info">
                        <div class="carrito-nombre">${p.nombre}</div>
                        <div class="carrito-medida">${p.medida || 'Unidad'}</div>
                        ${badgeMayor}
                    </div>
                    <div class="carrito-precio">${precioTexto}</div>
                    <div class="carrito-cantidad">
                        <button onclick="actualizarCantidad(${p.id}, -1)">-</button>
                        <span class="cantidad">${p.cantidad}</span>
                        <button onclick="actualizarCantidad(${p.id}, 1)">+</button>
                    </div>
                    <div class="carrito-subtotal"><strong>$${p.subtotal.toFixed(2)}</strong></div>
                    <button class="btn-eliminar" onclick="eliminarProducto(${p.id})">✕</button>
                </div>
            `;
        });
        
        document.getElementById('carrito-items').innerHTML = html;
        actualizarTotal();
    }
    
    // ============================================
    // FUNCIÓN: ACTUALIZAR TOTALES
    // ============================================
    function actualizarTotal() {
        document.getElementById('total-cantidad').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('total-bs').textContent = `Bs ${(subtotal * <?php echo $tasa; ?>).toFixed(2).replace('.', ',')}`;
        
        // Actualizar totales en los métodos de pago si existen
        if (document.getElementById('total-efectivo-divisa')) {
            document.getElementById('total-efectivo-divisa').textContent = subtotal.toFixed(2);
        }
        if (document.getElementById('total-pago-movil')) {
            document.getElementById('total-pago-movil').textContent = subtotal.toFixed(2);
        }
        if (document.getElementById('total-multipago')) {
            document.getElementById('total-multipago').textContent = subtotal.toFixed(2);
        }
        if (document.getElementById('total-multipago-bs')) {
            document.getElementById('total-multipago-bs').textContent = (subtotal * <?php echo $tasa; ?>).toFixed(2).replace('.', ',');
        }
    }
    
    // ============================================
    // FUNCIÓN: ACTUALIZAR CANTIDAD
    // ============================================
    function actualizarCantidad(id, delta) {
        carrito[id] = (carrito[id] || 0) + delta;
        
        if (carrito[id] <= 0) {
            delete carrito[id];
        }
        
        localStorage.setItem('carrito', JSON.stringify(carrito));
        cargarCarrito(); // Recargar todo
    }
    
    // ============================================
    // FUNCIÓN: ELIMINAR PRODUCTO
    // ============================================
    function eliminarProducto(id) {
        if (confirm('¿Eliminar este producto del carrito?')) {
            delete carrito[id];
            localStorage.setItem('carrito', JSON.stringify(carrito));
            cargarCarrito(); // Recargar todo
        }
    }
    
    // ============================================
    // FUNCIÓN: LIMPIAR CARRITO COMPLETO
    // ============================================
    function limpiarCarrito() {
        if (confirm('¿Estás seguro de vaciar el carrito completamente?')) {
            localStorage.removeItem('carrito');
            cargarCarrito(); // Recargar la página
        }
    }
    
    // ============================================
    // FUNCIÓN: SELECCIONAR SUCURSAL
    // ============================================
    function seleccionarSucursal(index) {
        const sucursales = document.querySelectorAll('.sucursal-card');
        sucursales.forEach(c => c.classList.remove('seleccionada'));
        sucursales[index].classList.add('seleccionada');
        
        sucursalSeleccionada = {
            id: sucursales[index].dataset.id,
            nombre: sucursales[index].dataset.nombre,
            whatsapp: sucursales[index].dataset.whatsapp
        };
        console.log('Sucursal seleccionada:', sucursalSeleccionada);
    }
    
    // ============================================
    // FUNCIÓN: SELECCIONAR MÉTODO DE PAGO
    // ============================================
    function seleccionarMetodo(metodo) {
        metodoSeleccionado = metodo;
        document.querySelectorAll('.metodo-pago').forEach(el => el.classList.remove('seleccionado'));
        document.querySelector(`[data-metodo="${metodo}"]`).classList.add('seleccionado');
        
        document.getElementById('datos-pago').style.display = 'block';
        document.querySelectorAll('#datos-pago > div').forEach(div => div.style.display = 'none');
        
        if (document.getElementById(`datos-${metodo}`)) {
            document.getElementById(`datos-${metodo}`).style.display = 'block';
        }
        
        // Resetear valores específicos para multipago
        if (metodo === 'multipago') {
            if (document.getElementById('monto_pago_movil')) {
                document.getElementById('monto_pago_movil').value = 0;
                document.getElementById('datos-pago-movil-multipago').style.display = 'none';
                calcularRestanteMultipago();
            }
        }
    }
    
    // ============================================
    // FUNCIÓN: CALCULAR RESTANTE PARA PAGO MÚLTIPLE
    // ============================================
    function calcularRestanteMultipago() {
        const montoPM = parseFloat(document.getElementById('monto_pago_movil').value) || 0;
        const restante = subtotal - montoPM;
        
        document.getElementById('resto-multipago').textContent = restante.toFixed(2);
        document.getElementById('resto-multipago-bs').textContent = (restante * <?php echo $tasa; ?>).toFixed(2).replace('.', ',');
        
        if (montoPM > 0) {
            document.getElementById('datos-pago-movil-multipago').style.display = 'block';
        } else {
            document.getElementById('datos-pago-movil-multipago').style.display = 'none';
        }
    }
    
    // ============================================
    // EVENTO: VISTA PREVIA DE CAPTURA DE PAGO
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Captura para Pago Móvil
        const capturaInput = document.getElementById('captura_pago');
        if (capturaInput) {
            capturaInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        capturaBase64 = e.target.result;
                        document.getElementById('preview-captura').style.display = 'block';
                        document.getElementById('preview-imagen').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Captura para Multipago
        const capturaMulti = document.getElementById('captura_multipago');
        if (capturaMulti) {
            capturaMulti.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        capturaMultipagoBase64 = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Evento para cálculo de multipago
        const montoPM = document.getElementById('monto_pago_movil');
        if (montoPM) {
            montoPM.addEventListener('input', calcularRestanteMultipago);
        }
    });
    
    // ============================================
    // FUNCIÓN: GEOLOCALIZACIÓN
    // ============================================
    function obtenerUbicacion() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
                const embedUrl = `https://maps.google.com/maps?q=${lat},${lng}&output=embed`;
                
                document.getElementById('ubicacion').value = mapsUrl;
                document.getElementById('map').style.display = 'block';
                document.getElementById('map').innerHTML = `<iframe width="100%" height="300" src="${embedUrl}" style="border:0;" allowfullscreen loading="lazy"></iframe>`;
                
                const mapLink = document.getElementById('map-link');
                if (mapLink) {
                    mapLink.style.display = 'block';
                    document.getElementById('map-url').href = mapsUrl;
                }
            }, error => {
                alert('No se pudo obtener tu ubicación. Por favor ingrésala manualmente.');
            });
        } else {
            alert('Tu navegador no soporta geolocalización');
        }
    }
    
    // ============================================
    // FUNCIÓN: CONFIRMAR PEDIDO
    // ============================================
function confirmarPedido() {
    // Validaciones básicas
    if (!sucursalSeleccionada) {
        alert('Selecciona una sucursal');
        return;
    }
    
    if (!document.getElementById('nombre_recibe').value || 
        !document.getElementById('telefono').value || 
        !document.getElementById('direccion').value) {
        alert('Completa todos los datos de entrega');
        return;
    }
    
    if (!metodoSeleccionado) {
        alert('Selecciona un método de pago');
        return;
    }
    
    // Validaciones según método de pago
    if (metodoSeleccionado === 'pago_movil') {
        const referencia = document.getElementById('referencia');
        const captura = document.getElementById('captura_pago');
        
        if (!referencia || !referencia.value) {
            alert('Ingresa el número de referencia');
            return;
        }
        
        if (!captura || !captura.files[0]) {
            alert('Debes subir la captura de pago');
            return;
        }
    }
    
    if (metodoSeleccionado === 'multipago') {
        const montoPM = parseFloat(document.getElementById('monto_pago_movil').value) || 0;
        const referencia = document.getElementById('referencia_multipago');
        const captura = document.getElementById('captura_multipago');
        
        if (montoPM <= 0 || montoPM >= subtotal) {
            alert('El monto de Pago Móvil debe ser mayor a 0 y menor al total');
            return;
        }
        
        if (!referencia || !referencia.value) {
            alert('Ingresa la referencia del Pago Móvil');
            return;
        }
        
        if (!captura || !captura.files[0]) {
            alert('Debes subir la captura del Pago Móvil');
            return;
        }
    }
    
    // Confirmación final
    if (!confirm('⚠️ ¿Estás seguro de confirmar este pedido?\n\nVerifica que todos tus datos sean correctos.')) {
        return;
    }
    
    // Mostrar indicador de carga
    const btnConfirmar = document.querySelector('.btn-verde');
    const textoOriginal = btnConfirmar.innerHTML;
    btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    btnConfirmar.disabled = true;
    
    // Función para convertir archivo a base64
    function convertirABase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    // Construir objeto de pedido base
    const datosPedido = {
        productos: productos,
        subtotal: subtotal,
        total: subtotal,
        sucursal: sucursalSeleccionada,
        metodo_pago: metodoSeleccionado,
        nombre: document.getElementById('nombre_recibe').value,
        telefono: document.getElementById('telefono').value,
        direccion: document.getElementById('direccion').value,
        referencias: document.getElementById('referencias') ? document.getElementById('referencias').value : '',
        ubicacion: document.getElementById('ubicacion') ? document.getElementById('ubicacion').value : '',
        tasa_bcv: <?php echo $tasa; ?>,
        datos_pago: {}
    };
    
    // Procesar capturas según método
    (async () => {
        try {
            if (metodoSeleccionado === 'pago_movil') {
                datosPedido.datos_pago.referencia = document.getElementById('referencia').value;
                const file = document.getElementById('captura_pago').files[0];
                datosPedido.datos_pago.captura = await convertirABase64(file);
            }
            
            if (metodoSeleccionado === 'multipago') {
                datosPedido.datos_pago.monto_pm = parseFloat(document.getElementById('monto_pago_movil').value);
                datosPedido.datos_pago.referencia = document.getElementById('referencia_multipago').value;
                const file = document.getElementById('captura_multipago').files[0];
                datosPedido.datos_pago.captura = await convertirABase64(file);
                datosPedido.datos_pago.moneda_resto = document.getElementById('moneda_resto')?.value;
                datosPedido.datos_pago.cambio = document.getElementById('cambio_multipago')?.value;
            }
            
            if (metodoSeleccionado === 'efectivo_bs' && document.getElementById('cambio_bs')) {
                datosPedido.datos_pago.cambio = document.getElementById('cambio_bs').value;
            }
            
            if (metodoSeleccionado === 'efectivo_divisa') {
                datosPedido.datos_pago.moneda = document.getElementById('moneda_divisa')?.value;
                datosPedido.datos_pago.cambio = document.getElementById('cambio_divisa')?.value;
            }
            
            console.log('Enviando pedido a la API:', datosPedido);
            
            // Enviar a la API
            const response = await fetch('/gusticos/api/crear_pedido.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(datosPedido)
            });
            
            const text = await response.text();
            console.log('Respuesta de la API (texto):', text);
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                throw new Error('La respuesta del servidor no es válida: ' + text.substring(0, 100));
            }
            
            if (data.success) {
                // ✅ ABRIR WHATSAPP CON EL MENSAJE PARA LA TIENDA
                console.log('Abriendo WhatsApp con URL:', data.whatsapp_url);
                window.open(data.whatsapp_url, '_blank');
                
                // Guardar en localStorage para la confirmación
                localStorage.setItem('ultimo_pedido', JSON.stringify({
                    numero: data.numero_pedido,
                    productos: productos,
                    total: subtotal,
                    sucursal: sucursalSeleccionada,
                    fecha: new Date().toISOString()
                }));
                
                // Limpiar carrito
                localStorage.removeItem('carrito');
                
                // Redirigir a confirmación después de 2 segundos
                setTimeout(() => {
                    window.location.href = 'confirmacion.php?pedido=' + data.numero_pedido;
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
                btnConfirmar.innerHTML = textoOriginal;
                btnConfirmar.disabled = false;
            }
        } catch (error) {
            console.error('Error completo:', error);
            alert('Error de conexión al procesar el pedido: ' + error.message);
            btnConfirmar.innerHTML = textoOriginal;
            btnConfirmar.disabled = false;
        }
    })();
}
    // ============================================
    // INICIALIZACIÓN
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== INICIANDO CARRITO ===');
        cargarCarrito();
    });
</script>
<style>
    .carrito-lista {
        background: white;
        border-radius: var(--borde-redondo);
        overflow: hidden;
        box-shadow: var(--sombra);
        margin-bottom: 20px;
    }
    
    .carrito-item {
        display: flex;
        gap: 15px;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid var(--gris-medio);
    }
    
    .carrito-item:last-child {
        border-bottom: none;
    }
    
    .carrito-imagen {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--morado-claro), var(--verde-claro));
        border-radius: var(--borde-redondo);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .carrito-info {
        flex: 2;
    }
    
    .carrito-nombre {
        font-weight: 700;
        color: var(--morado);
    }
    
    .carrito-medida {
        color: var(--texto-secundario);
        font-size: 0.9rem;
    }
    
    .carrito-precio {
        flex: 1;
        min-width: 100px;
    }
    
    .carrito-cantidad {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .carrito-cantidad button {
        width: 30px;
        height: 30px;
        border: none;
        background: var(--gris-claro);
        border-radius: 50%;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.2s;
    }
    
    .carrito-cantidad button:hover {
        background: var(--morado);
        color: white;
    }
    
    .carrito-subtotal {
        min-width: 80px;
        text-align: right;
        font-weight: 700;
        color: var(--verde);
    }
    
    .btn-eliminar {
        background: var(--rojo);
        color: white;
        border: none;
        border-radius: 5px;
        width: 36px;
        height: 36px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .btn-eliminar:hover {
        background: #B71C1C;
        transform: scale(1.1);
    }
    
    .metodo-pago {
        cursor: pointer;
        border: 2px solid var(--gris-medio);
        border-radius: var(--borde-redondo);
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    
    .metodo-pago:hover,
    .metodo-pago.seleccionado {
        border-color: var(--morado);
        background: rgba(106,13,173,0.05);
    }
    
    .precio-mayor-destacado {
        display: inline-block;
        background: linear-gradient(135deg, var(--morado), var(--verde));
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        margin-top: 5px;
    }
    
    .sucursales-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .sucursal-card {
        background: white;
        border-radius: var(--borde-redondo);
        padding: 25px;
        box-shadow: var(--sombra);
        cursor: pointer;
        transition: all 0.3s;
        border: 3px solid transparent;
    }
    
    .sucursal-card:hover {
        transform: translateY(-5px);
        border-color: var(--morado);
    }
    
    .sucursal-card.seleccionada {
        border-color: var(--morado);
        background: rgba(106,13,173,0.05);
    }
    
    .total-card {
        background: linear-gradient(135deg, var(--morado), var(--verde));
        color: white;
        padding: 30px;
        border-radius: var(--borde-redondo);
        text-align: center;
        margin: 30px 0;
    }
    
    .total-cantidad {
        font-size: 3rem;
        font-weight: 800;
        margin: 15px 0;
    }
    
    @media (max-width: 768px) {
        .carrito-item {
            flex-wrap: wrap;
        }
        
        .carrito-info {
            flex: 1 1 100%;
            order: -1;
        }
        
        .carrito-subtotal {
            min-width: auto;
        }
        
        .total-cantidad {
            font-size: 2.5rem;
        }
    }
   

    .carrito-lista {
        background: white;
        border-radius: var(--borde-redondo);
        overflow: hidden;
        box-shadow: var(--sombra);
        margin-bottom: 20px;
    }
    
    .carrito-item {
        display: flex;
        gap: 15px;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid var(--gris-medio);
    }
    
    .carrito-item:last-child {
        border-bottom: none;
    }
    
    .carrito-imagen {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--morado-claro), var(--verde-claro));
        border-radius: var(--borde-redondo);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .carrito-info {
        flex: 2;
    }
    
    .carrito-nombre {
        font-weight: 700;
        color: var(--morado);
    }
    
    .carrito-medida {
        color: var(--texto-secundario);
        font-size: 0.9rem;
    }
    
    .carrito-precio {
        flex: 1;
        min-width: 100px;
        font-weight: 600;
    }
    
    .carrito-cantidad {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .carrito-cantidad button {
        width: 30px;
        height: 30px;
        border: none;
        background: var(--gris-claro);
        border-radius: 50%;
        cursor: pointer;
        font-weight: 700;
        transition: all 0.2s;
    }
    
    .carrito-cantidad button:hover {
        background: var(--morado);
        color: white;
    }
    
    .carrito-cantidad .cantidad {
        min-width: 30px;
        text-align: center;
        font-weight: 600;
    }
    
    .carrito-subtotal {
        min-width: 80px;
        text-align: right;
        font-weight: 700;
        color: var(--verde);
    }
    
    .btn-eliminar {
        background: var(--rojo);
        color: white;
        border: none;
        border-radius: 5px;
        width: 36px;
        height: 36px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .btn-eliminar:hover {
        background: #B71C1C;
        transform: scale(1.1);
    }
    
    .total-card {
        background: linear-gradient(135deg, var(--morado), var(--verde));
        color: white;
        padding: 30px;
        border-radius: var(--borde-redondo);
        text-align: center;
        margin: 30px 0;
    }
    
    .total-cantidad {
        font-size: 3rem;
        font-weight: 800;
        margin: 15px 0;
    }
    
    .btn-rojo {
        background: var(--rojo);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: var(--borde-redondo-btn);
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-rojo:hover {
        background: #B71C1C;
    }
    
    @media (max-width: 768px) {
        .carrito-item {
            flex-wrap: wrap;
        }
        
        .carrito-info {
            flex: 1 1 100%;
            order: -1;
        }
        
        .carrito-subtotal {
            min-width: auto;
        }
        
        .total-cantidad {
            font-size: 2.5rem;
        }
    }


</style>

<?php include 'includes/footer.php'; ?>