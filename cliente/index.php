<?php
require_once '../includes/funciones.php';

$cliente_logueado = isset($_SESSION['cliente_id']);
$whatsapp = obtenerWhatsApp($pdo);
$tasa = obtenerTasaBVC($pdo);

// Obtener categorías
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY orden ASC")->fetchAll();

// Obtener productos
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$sql = "SELECT * FROM productos WHERE activo = 1";
$params = [];

if ($categoria_id > 0) {
    $sql .= " AND id_categoria = ?";
    $params[] = $categoria_id;
}

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql .= " ORDER BY destacado DESC, nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Obtener promociones activas
$promociones = $pdo->query("
    SELECT * FROM promociones 
    WHERE activo = 1 AND fecha_inicio <= NOW() AND fecha_fin >= NOW() 
    ORDER BY fecha_fin ASC LIMIT 3
")->fetchAll();

// Obtener carrusel
$carrusel = $pdo->query("SELECT * FROM carrusel WHERE activo = 1 ORDER BY orden ASC")->fetchAll();

// Obtener comentarios aprobados
$comentarios = $pdo->query("
    SELECT c.*, cl.nombre as cliente_nombre 
    FROM comentarios c
    JOIN clientes cl ON c.id_cliente = cl.id_cliente
    WHERE c.estado = 'aprobado' 
    ORDER BY c.fecha DESC 
    LIMIT 10
")->fetchAll();

// Obtener zonas de envío
$zonas = $pdo->query("SELECT * FROM zonas_envio WHERE activo = 1 ORDER BY orden ASC")->fetchAll();

// Obtener puntos del cliente si está logueado
$puntos_cliente = 0;
if ($cliente_logueado) {
    $stmt_puntos = $pdo->prepare("SELECT puntos_totales FROM clientes WHERE id_cliente = ?");
    $stmt_puntos->execute([$_SESSION['cliente_id']]);
    $puntos_cliente = $stmt_puntos->fetchColumn();
}

include 'includes/header.php';
?>

<!-- CARRUSEL -->
<?php if (!empty($carrusel)): ?>
<style>
    .carrusel {
        width: 100%;
        height: 500px;
        overflow: hidden;
        position: relative;
        margin-bottom: 30px;
    }
    
    .carrusel-container {
        width: 100%;
        height: 100%;
        position: relative;
    }
    
    .carrusel-slide {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        z-index: 1;
    }
    
    .carrusel-slide.active {
        opacity: 1;
        z-index: 2;
    }
    
    .carrusel-slide::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1;
    }
    
    .carrusel-content {
        position: relative;
        z-index: 2;
        color: white;
        text-align: center;
        max-width: 800px;
        padding: 20px;
    }
    
    .carrusel-content h2 {
        font-size: 3rem;
        margin-bottom: 15px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .carrusel-content p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }
    
    .carrusel-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.3);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 2rem;
        cursor: pointer;
        z-index: 3;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .carrusel-arrow:hover {
        background: var(--morado);
        transform: translateY(-50%) scale(1.1);
    }
    
    .carrusel-arrow.prev {
        left: 20px;
    }
    
    .carrusel-arrow.next {
        right: 20px;
    }
    
    .carrusel-indicators {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 3;
    }
    
    .carrusel-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .carrusel-dot.active {
        background: white;
        transform: scale(1.3);
    }
    
    @media (max-width: 768px) {
        .carrusel {
            height: 300px;
        }
        .carrusel-content h2 {
            font-size: 1.8rem;
        }
        .carrusel-arrow {
            width: 35px;
            height: 35px;
            font-size: 1.2rem;
        }
    }
</style>

<div class="carrusel">
    <div class="carrusel-container">
        <?php foreach ($carrusel as $index => $item): ?>
        <div class="carrusel-slide <?php echo $index == 0 ? 'active' : ''; ?>" 
             style="background-image: url('<?php echo $item['imagen']; ?>');"
             data-index="<?php echo $index; ?>">
            <div class="carrusel-content">
                <h2><?php echo $item['titulo']; ?></h2>
                <p><?php echo $item['subtitulo']; ?></p>
                <?php if (!empty($item['link'])): ?>
                <a href="<?php echo $item['link']; ?>" class="btn btn-morado" style="margin-top: 20px;">Ver más</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Flechas de navegación -->
        <button class="carrusel-arrow prev" onclick="cambiarSlide(-1)">❮</button>
        <button class="carrusel-arrow next" onclick="cambiarSlide(1)">❯</button>
        
        <!-- Indicadores (puntos) -->
        <div class="carrusel-indicators">
            <?php foreach ($carrusel as $index => $item): ?>
            <div class="carrusel-dot <?php echo $index == 0 ? 'active' : ''; ?>" 
                 onclick="irASlide(<?php echo $index; ?>)"></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <!-- SECCIÓN DE PUNTOS PARA CLIENTES LOGUEADOS -->
    <?php if ($cliente_logueado): ?>
    <div style="background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 15px; padding: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="color: var(--morado-oscuro);">🌟 Tus Puntos</h3>
            <p style="font-size: 2rem; font-weight: 800; color: var(--morado-oscuro);"><?php echo $puntos_cliente; ?> puntos</p>
        </div>
        <div style="font-size: 3rem;">🏆</div>
    </div>
    <?php endif; ?>
    
    <!-- PROMOCIONES -->
    <?php if (!empty($promociones)): ?>
    <div class="promos-grid">
        <?php foreach ($promociones as $promo): ?>
        <div class="promo-card">
            <div class="promo-titulo"><?php echo $promo['codigo']; ?></div>
            <div class="promo-subtitulo"><?php echo $promo['nombre']; ?></div>
            <p><?php echo $promo['descripcion']; ?></p>
            <?php if ($promo['puntos_bonus'] > 0): ?>
            <p><span class="badge" style="background: gold; color: var(--morado-oscuro);">+<?php echo $promo['puntos_bonus']; ?> puntos</span></p>
            <?php endif; ?>
            <small>Válido hasta: <?php echo date('d/m/Y', strtotime($promo['fecha_fin'])); ?></small>
            <a href="#" class="promo-boton">¡Vamos a ganar!</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- TASA BCV -->
    <div class="tasa-info">
        <span><i class="fas fa-dollar-sign"></i> Tasa BCV del día</span>
        <span class="tasa-valor">Bs <?php echo number_format($tasa, 2); ?> / USD</span>
    </div>
    
    <!-- CATEGORÍAS -->
    <div class="categorias">
        <div class="categoria-item <?php echo $categoria_id == 0 ? 'active' : ''; ?>" onclick="window.location.href='index.php'">
            <div class="categoria-icon">🍽️</div>
            <div>Todos</div>
        </div>
        <?php foreach ($categorias as $cat): ?>
        <div class="categoria-item <?php echo $categoria_id == $cat['id_categoria'] ? 'active' : ''; ?>" 
             onclick="window.location.href='index.php?categoria=<?php echo $cat['id_categoria']; ?>'">
            <div class="categoria-icon"><?php echo $cat['icono'] ?? '🍽️'; ?></div>
            <div><?php echo $cat['nombre']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- NUESTROS PRODUCTOS -->
    <h2>Nuestros Productos</h2>
    <div class="productos-grid">
        <?php if (empty($productos)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 40px;">No hay productos disponibles</p>
        <?php endif; ?>
        
        <?php foreach ($productos as $p): 
            $badge = getStockBadge($p['estado_stock']);
            $deshabilitado = $p['estado_stock'] == 'agotado' ? 'disabled' : '';
            $medida = isset($p['medida']) ? $p['medida'] : 'Unidad';
        ?>
        <div class="producto-card">
            <a href="producto.php?id=<?php echo $p['id_producto']; ?>" style="text-decoration: none; color: inherit;">
                <div class="producto-imagen">
                    <?php if (!empty($p['imagen'])): ?>
                        <img src="<?php echo $p['imagen']; ?>" alt="<?php echo $p['nombre']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-ice-cream"></i>
                    <?php endif; ?>
                </div>
                <div class="producto-info">
                    <h3 class="producto-nombre"><?php echo $p['nombre']; ?></h3>
                    <div class="producto-medida"><?php echo $medida; ?></div>
                    
                    <span class="estado-stock estado-<?php echo $p['estado_stock']; ?>" style="display: inline-block; margin: 10px 0;">
                        <?php 
                        $estados = [
                            'disponible' => 'Disponible',
                            'pocas' => 'Pocas unidades',
                            'agotado' => 'Agotado'
                        ];
                        echo $estados[$p['estado_stock']];
                        ?>
                    </span>
                    
                    <div class="producto-precio">$<?php echo number_format($p['precio'], 2); ?></div>
                    <?php if (!empty($p['precio_mayor'])): ?>
                    <small class="precio-mayor-destacado">Mayor: $<?php echo number_format($p['precio_mayor'], 2); ?></small>
                    <?php endif; ?>
                    
                    <?php if ($tasa > 0): ?>
                    <small style="display: block;">Bs <?php echo number_format($p['precio'] * $tasa, 2); ?></small>
                    <?php endif; ?>
                </div>
            </a>
            <div style="padding: 0 20px 20px;">
                <button class="btn btn-morado" style="width: 100%;" onclick="agregarAlCarrito(<?php echo $p['id_producto']; ?>, this)" <?php echo $deshabilitado; ?>>
    Agregar
</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- ZONA DE ENTREGA -->
    <div class="card">
        <h3>Zona de entrega</h3>
        <select id="zona" class="form-group">
            <option value="">Selecciona tu zona</option>
            <?php foreach ($zonas as $z): ?>
            <option value="<?php echo $z['id_zona']; ?>" data-precio="<?php echo $z['precio_envio']; ?>">
                <?php echo $z['nombre']; ?> - $<?php echo number_format($z['precio_envio'], 2); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- SECCIÓN DE CALIFICACIÓN Y COMENTARIOS -->
    <div style="margin: 50px 0;">
        <h2 style="text-align: center; margin-bottom: 30px;">¿Cómo fue tu experiencia?</h2>
        
        <!-- Formulario para dejar comentario (solo para clientes logueados) -->
        <?php if ($cliente_logueado): ?>
        <div class="card" style="max-width: 600px; margin: 0 auto 40px;">
            <h3 style="color: var(--morado); margin-bottom: 20px;">Califícanos y deja tu comentario</h3>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600;">Tu calificación:</label>
                <div style="display: flex; gap: 10px; font-size: 2rem;" id="rating-stars">
                    <i class="far fa-star" data-rating="1" style="cursor: pointer; color: gold;"></i>
                    <i class="far fa-star" data-rating="2" style="cursor: pointer; color: gold;"></i>
                    <i class="far fa-star" data-rating="3" style="cursor: pointer; color: gold;"></i>
                    <i class="far fa-star" data-rating="4" style="cursor: pointer; color: gold;"></i>
                    <i class="far fa-star" data-rating="5" style="cursor: pointer; color: gold;"></i>
                </div>
                <input type="hidden" id="rating-value" value="0">
            </div>
            
            <div class="form-group">
                <label>Tu comentario:</label>
                <textarea id="comentario-texto" rows="4" placeholder="Cuéntanos tu experiencia..."></textarea>
            </div>
            
            <button class="btn btn-verde" onclick="enviarComentario()" style="width: 100%;">
                Enviar mi opinión
            </button>
            
            <div id="comentario-mensaje" style="margin-top: 15px; display: none;"></div>
        </div>
        <?php else: ?>
        <div class="card" style="max-width: 600px; margin: 0 auto 40px; text-align: center; padding: 30px;">
            <p>Para dejar tu opinión, <a href="login.php" style="color: var(--morado); font-weight: 600;">inicia sesión</a> o <a href="registro.php" style="color: var(--morado); font-weight: 600;">regístrate</a>.</p>
        </div>
        <?php endif; ?>
        
        <!-- COMENTARIOS APROBADOS -->
        <?php if (!empty($comentarios)): ?>
        <h3 style="text-align: center; margin: 40px 0 20px;">Lo que dicen nuestros clientes</h3>
        <div class="comentarios-grid">
            <?php foreach ($comentarios as $c): ?>
            <div class="comentario-card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: var(--sombra);">
                <div style="color: gold; font-size: 1.2rem; margin-bottom: 10px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="color: <?php echo $i <= $c['rating'] ? 'gold' : '#ccc'; ?>;"></i>
                    <?php endfor; ?>
                </div>
                <p style="font-style: italic; margin: 15px 0;">"<?php echo htmlspecialchars($c['comentario']); ?>"</p>
                <div style="display: flex; justify-content: space-between; color: var(--texto-secundario);">
                    <span>- <?php echo htmlspecialchars($c['cliente_nombre']); ?></span>
                    <span><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // ===== CARRUSEL =====
    let slideActual = 0;
    const slides = document.querySelectorAll('.carrusel-slide');
    const dots = document.querySelectorAll('.carrusel-dot');
    let intervalo;
    
    function mostrarSlide(index) {
        if (!slides.length) return;
        if (index >= slides.length) index = 0;
        if (index < 0) index = slides.length - 1;
        
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        slideActual = index;
    }
    
    function cambiarSlide(direccion) {
        mostrarSlide(slideActual + direccion);
    }
    
    function irASlide(index) {
        mostrarSlide(index);
        clearInterval(intervalo);
        iniciarAutoPlay();
    }
    
    function iniciarAutoPlay() {
        if (slides.length > 1) {
            intervalo = setInterval(() => cambiarSlide(1), 5000);
        }
    }
    
    if (slides.length > 0) {
        iniciarAutoPlay();
    }
    
    // ===== SISTEMA DE ESTRELLAS PARA CALIFICACIÓN =====
    const stars = document.querySelectorAll('#rating-stars i');
    const ratingInput = document.getElementById('rating-value');
    
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });
        
        star.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value);
            highlightStars(currentRating);
        });
        
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            highlightStars(rating);
        });
    });
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('fas');
                star.classList.add('far');
            }
        });
    }
    
   function enviarComentario() {
    const rating = document.getElementById('rating-value').value;
    const comentario = document.getElementById('comentario-texto').value;
    const mensajeDiv = document.getElementById('comentario-mensaje');
    
    // Validaciones
    if (rating == 0) {
        mensajeDiv.innerHTML = '<div class="alert alert-error">Selecciona una calificación</div>';
        mensajeDiv.style.display = 'block';
        return;
    }
    
    if (!comentario.trim()) {
        mensajeDiv.innerHTML = '<div class="alert alert-error">Escribe un comentario</div>';
        mensajeDiv.style.display = 'block';
        return;
    }
    
    // Mostrar mensaje de carga
    mensajeDiv.innerHTML = '<div class="alert alert-info">Enviando comentario...</div>';
    mensajeDiv.style.display = 'block';
    
    // Usar ruta ABSOLUTA para el fetch
    fetch('/gusticos/api/comentarios.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'accion=enviar&rating=' + rating + '&comentario=' + encodeURIComponent(comentario)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Error HTTP: ' + res.status);
        }
        return res.text(); // Primero obtener como texto para debug
    })
    .then(text => {
        console.log('Respuesta del servidor:', text); // Para debug
        
        try {
            const data = JSON.parse(text);
            if (data.success) {
                mensajeDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                // Limpiar formulario
                document.getElementById('rating-value').value = 0;
                document.getElementById('comentario-texto').value = '';
                highlightStars(0);
            } else {
                mensajeDiv.innerHTML = '<div class="alert alert-error">' + (data.message || 'Error al enviar comentario') + '</div>';
            }
        } catch(e) {
            console.error('Error parseando JSON:', e);
            mensajeDiv.innerHTML = '<div class="alert alert-error">Error en la respuesta del servidor</div>';
        }
        
        // Ocultar mensaje después de 5 segundos
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 5000);
    })
    .catch(error => {
        console.error('Error completo:', error);
        mensajeDiv.innerHTML = '<div class="alert alert-error">Error de conexión: ' + error.message + '</div>';
        mensajeDiv.style.display = 'block';
        
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 5000);
    });
}
   // ============================================
// FUNCIÓN PARA AGREGAR AL CARRITO
// ============================================
function agregarAlCarrito(id, boton) {
    // Obtener carrito actual
    let carrito = JSON.parse(localStorage.getItem('carrito') || '{}');
    
    // Incrementar cantidad
    carrito[id] = (carrito[id] || 0) + 1;
    
    // Guardar en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Actualizar contador flotante
    actualizarContadorCarrito();
    
    // Feedback visual
    const textoOriginal = boton.innerHTML;
    boton.innerHTML = '<i class="fas fa-check"></i> Agregado';
    boton.style.background = 'var(--verde)';
    boton.disabled = true;
    
    setTimeout(() => {
        boton.innerHTML = textoOriginal;
        boton.style.background = '';
        boton.disabled = false;
    }, 1500);
}

// ============================================
// FUNCIÓN PARA ACTUALIZAR CONTADOR
// ============================================
function actualizarContadorCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito') || '{}');
    const total = Object.values(carrito).reduce((a, b) => a + b, 0);
    const contador = document.getElementById('cart-count');
    
    if (contador) {
        contador.textContent = total;
        contador.style.display = total > 0 ? 'flex' : 'none';
    }
}

// ============================================
// FUNCIÓN PARA LIMPIAR CARRITO
// ============================================
function limpiarCarrito() {
    if (confirm('¿Estás seguro de vaciar el carrito?')) {
        localStorage.removeItem('carrito');
        actualizarContadorCarrito();
        
        // Si estamos en la página del carrito, recargar
        if (window.location.href.includes('carrito.php')) {
            location.reload();
        }
    }
}

// Inicializar contador al cargar la página
document.addEventListener('DOMContentLoaded', actualizarContadorCarrito);
    
    // Guardar zona seleccionada
    const zonaSelect = document.getElementById('zona');
    if (zonaSelect) {
        zonaSelect.addEventListener('change', function() {
            const zonaId = this.value;
            const zonaNombre = this.options[this.selectedIndex]?.text || '';
            const zonaPrecio = this.options[this.selectedIndex]?.dataset.precio || 0;
            
            localStorage.setItem('zona_seleccionada', JSON.stringify({
                id: zonaId,
                nombre: zonaNombre,
                precio: zonaPrecio
            }));
        });
        
        // Cargar zona guardada
        const zonaGuardada = localStorage.getItem('zona_seleccionada');
        if (zonaGuardada) {
            const zona = JSON.parse(zonaGuardada);
            zonaSelect.value = zona.id;
        }
    }
</script>

<style>
    .comentarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .comentario-card {
        transition: transform 0.3s;
    }
    
    .comentario-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--sombra-fuerte);
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 10px 0;
    }
    
    .alert-success {
        background: #E8F5E9;
        color: var(--verde-oscuro);
        border-left: 4px solid var(--verde);
    }
    
    .alert-error {
        background: #FFEBEE;
        color: var(--rojo);
        border-left: 4px solid var(--rojo);
    }
</style>

<?php include 'includes/footer.php'; ?>